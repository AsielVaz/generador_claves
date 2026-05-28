<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EspiralPaymentController extends Controller
{
    public function create(Request $request): View
    {
        return view('payments.card', [
            'walletBalance' => $request->user()->walletBalance(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'amount' => $this->cleanMoney($request->input('amount')),
        ]);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:999999.99'],
            'phone' => ['required', 'string', 'max:30'],
            'street' => ['required', 'string', 'max:120'],
            'number_ext' => ['required', 'string', 'max:30'],
            'number_int' => ['nullable', 'string', 'max:30'],
            'zip_code' => ['required', 'string', 'max:12'],
            'city' => ['required', 'string', 'max:80'],
            'state' => ['required', 'string', 'max:80'],
        ], [], [
            'amount' => 'monto',
            'phone' => 'telefono',
            'street' => 'calle',
            'number_ext' => 'numero exterior',
            'number_int' => 'numero interior',
            'zip_code' => 'codigo postal',
            'city' => 'ciudad',
            'state' => 'estado',
        ]);

        $key = config('services.espiral.key');
        $baseUrl = rtrim((string) config('services.espiral.base_url'), '/');

        if (! $key) {
            return back()
                ->withErrors(['espiral' => 'No esta configurada la llave ESPIRAL_KEY.'])
                ->withInput();
        }

        $user = $request->user();
        $amount = round((float) $validated['amount'], 2);
        $localReference = 'espiral-'.now()->format('YmdHis').'-'.Str::lower(Str::random(8));

        $payment = $user->payments()->create([
            'course_id' => null,
            'type' => Payment::TYPE_WALLET_CREDIT,
            'amount' => $amount,
            'method' => 'Tarjeta Espiral',
            'status' => 'pendiente',
            'reference' => 'Pago con tarjeta pendiente',
            'unica' => $localReference,
        ]);

        $payload = $this->buildEspiralPayload($request, $validated, $payment, $baseUrl);

        try {
            $pendingRequest = Http::acceptJson()
                ->asJson()
                ->withHeaders([
                    'Accept-Language' => 'es-MX,es;q=0.9,en;q=0.8',
                    'User-Agent' => 'CryptoEfectivo/1.0 (+https://www.cryptoefectivo.com)',
                ])
                ->timeout(20);

            if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
                $pendingRequest = $pendingRequest->withOptions([
                    'curl' => [
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    ],
                ]);
            }

            $response = $pendingRequest->post($baseUrl.'/payOrder?'.http_build_query(['key' => $key]), $payload);
        } catch (\Throwable $exception) {
            $payment->update([
                'status' => 'error',
                'reference' => 'No se pudo conectar con Espiral',
            ]);

            report($exception);

            return back()
                ->withErrors(['espiral' => 'No se pudo conectar con Espiral. Intentalo nuevamente.'])
                ->with('espiral_api_error', $exception->getMessage())
                ->withInput();
        }

        $body = $response->json();
        $checkoutUrl = is_array($body)
            ? $this->checkoutUrlFromResponse($body, $baseUrl)
            : $this->checkoutUrlFromPlainText($response->body(), $baseUrl);

        if (! $response->successful() || ! $checkoutUrl) {
            $payment->update([
                'status' => 'error',
                'reference' => 'Espiral no genero la linea de pago',
            ]);

            Log::warning('Espiral payment link could not be generated.', [
                'payment_id' => $payment->id,
                'status' => $response->status(),
                'response' => $body ?? $response->body(),
            ]);

            $errorMessage = 'Espiral no genero la linea de pago. Revisa los datos e intentalo nuevamente.';

            if ($response->status() === 403 && Str::contains($response->body(), 'Cloudflare')) {
                $errorMessage = 'Espiral bloqueo la solicitud con Cloudflare (403). Revisa con Espiral que la IP del servidor este permitida para crear lineas de pago.';
            }

            return back()
                ->withErrors(['espiral' => $errorMessage])
                ->with('espiral_api_error', $this->formatEspiralErrorDetails($response, $body))
                ->withInput();
        }

        return redirect()->away($checkoutUrl);
    }

    public function success(string $reference): RedirectResponse
    {
        return redirect()
            ->route('payments.index')
            ->with('status', 'Pago recibido por Espiral. Cuando se confirme, se acreditara en tu cartera.');
    }

    public function error(string $reference): RedirectResponse
    {
        return redirect()
            ->route('payments.card.create')
            ->withErrors(['espiral' => 'Espiral no pudo completar el pago. Puedes intentarlo nuevamente.']);
    }

    public function webhook(Request $request, ?string $reference = null): JsonResponse
    {
        $payload = $request->all();
        $localReference = $reference
            ?: data_get($payload, 'metadata.reference')
            ?: data_get($payload, 'request.metadata.reference')
            ?: data_get($payload, 'reference');

        if (! $localReference) {
            Log::warning('Espiral webhook received without local reference.', ['payload' => $payload]);

            return response()->json(['ok' => false, 'message' => 'Referencia no encontrada.'], 422);
        }

        $payment = Payment::where('unica', $localReference)->first();

        if (! $payment) {
            Log::warning('Espiral webhook payment not found.', [
                'reference' => $localReference,
                'payload' => $payload,
            ]);

            return response()->json(['ok' => false, 'message' => 'Pago no encontrado.'], 404);
        }

        $message = Str::lower((string) data_get($payload, 'response.message'));
        $autStatus = (string) data_get($payload, 'response.data.autStatusResult');
        $autResult = (string) data_get($payload, 'response.data.autResult');
        $approved = $message === 'approved' || ($autStatus === 'A' && $autResult === '00');

        $transactionReference = data_get($payload, 'response.data.transactionId')
            ?: data_get($payload, 'response.data.reference')
            ?: $localReference;

        if ($payment->status === 'paid' && ! $approved) {
            return response()->json(['ok' => true, 'status' => 'paid']);
        }

        if ($approved) {
            if ($payment->status !== 'paid') {
                $payment->update([
                    'status' => 'paid',
                    'method' => 'Tarjeta Espiral',
                    'reference' => 'Espiral #'.$transactionReference,
                    'paid_at' => now(),
                ]);
            }

            return response()->json(['ok' => true, 'status' => 'paid']);
        }

        $payment->update([
            'status' => 'rechazado',
            'reference' => 'Espiral '.($message ?: 'rechazado').' #'.$transactionReference,
        ]);

        return response()->json(['ok' => true, 'status' => 'rechazado']);
    }

    private function buildEspiralPayload(Request $request, array $validated, Payment $payment, string $baseUrl): array
    {
        $user = $request->user();
        $amount = round((float) $validated['amount'], 2);

        return [
            'cardHolder' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $validated['phone'],
            ],
            'address' => [
                'country' => 'MX',
                'state' => $validated['state'],
                'city' => $validated['city'],
                'numberExt' => $validated['number_ext'],
                'numberInt' => $validated['number_int'] ?? '',
                'zipCode' => $validated['zip_code'],
                'street' => $validated['street'],
            ],
            'transaction' => [
                'items' => [
                    [
                        'name' => 'Saldo CryptoEfectivo',
                        'price' => $amount,
                        'description' => 'Abono a cartera CryptoEfectivo',
                        'quantity' => 1,
                    ],
                ],
                'total' => $amount,
                'currency' => 'MXN',
            ],
            'linkDetails' => [
                'name' => 'link - '.now()->valueOf(),
                'email' => '',
                'reusable' => true,
                'enableReference' => true,
                'securityType3D' => true,
            ],
            'webhook' => [
                'redirectUrl' => route('payments.card.success', $payment->unica),
                'redirectErrorUrl' => route('payments.card.error', $payment->unica),
                'backPage' => route('payments.card.create'),
                'redirectData' => [
                    'url' => route('webhooks.espiral', $payment->unica),
                    'redirectMethod' => 'POST',
                ],
                'redirectErrorData' => [
                    'url' => route('webhooks.espiral', $payment->unica),
                    'redirectMethod' => 'POST',
                ],
            ],
            'metadata' => [
                'payment_id' => $payment->id,
                'reference' => $payment->unica,
                'user_id' => $user->id,
            ],
        ];
    }

    private function checkoutUrlFromResponse(array $response, string $baseUrl): ?string
    {
        $candidates = [
            data_get($response, 'url'),
            data_get($response, 'link'),
            data_get($response, 'paymentUrl'),
            data_get($response, 'checkoutUrl'),
            data_get($response, 'token'),
            data_get($response, 'generatedToken'),
            data_get($response, 'id'),
            data_get($response, 'data.url'),
            data_get($response, 'data.link'),
            data_get($response, 'data.paymentUrl'),
            data_get($response, 'data.checkoutUrl'),
            data_get($response, 'data.token'),
            data_get($response, 'data.generatedToken'),
            data_get($response, 'data.id'),
        ];

        foreach (Arr::where($candidates, fn ($candidate) => is_string($candidate) && $candidate !== '') as $candidate) {
            if (Str::startsWith($candidate, ['http://', 'https://'])) {
                return $candidate;
            }

            return $baseUrl.'/'.ltrim($candidate, '/');
        }

        return null;
    }

    private function checkoutUrlFromPlainText(string $response, string $baseUrl): ?string
    {
        $value = trim($response);

        if ($value === '' || Str::contains($value, ['<html', '<!DOCTYPE html'])) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        if (preg_match('/^[A-Za-z0-9._-]+$/', $value) !== 1) {
            return null;
        }

        return $baseUrl.'/'.ltrim($value, '/');
    }

    private function formatEspiralErrorDetails($response, mixed $body): string
    {
        $contentType = $response->header('Content-Type') ?: 'Sin Content-Type';
        $rawBody = $response->body();

        if ($response->status() === 403 && Str::contains($rawBody, 'Cloudflare')) {
            return $this->formatCloudflareBlockDetails($response);
        }

        $responseBody = is_array($body)
            ? json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : $rawBody;

        if (! is_string($responseBody) || trim($responseBody) === '') {
            $responseBody = 'Sin cuerpo de respuesta.';
        }

        return trim(implode("\n\n", [
            'HTTP status: '.$response->status(),
            'Content-Type: '.$contentType,
            'Respuesta de Espiral:',
            Str::limit($responseBody, 5000, "\n... respuesta truncada ..."),
        ]));
    }

    private function formatCloudflareBlockDetails($response): string
    {
        $body = $response->body();
        preg_match('/Cloudflare Ray ID:\s*<strong[^>]*>([^<]+)<\/strong>/i', $body, $rayIdMatch);
        preg_match('/<span class="hidden" id="cf-footer-ip">([^<]+)<\/span>/i', $body, $ipMatch);

        return trim(implode("\n", array_filter([
            'HTTP status: '.$response->status(),
            'Content-Type: '.($response->header('Content-Type') ?: 'Sin Content-Type'),
            'Cloudflare bloqueo la solicitud antes de que llegara al API de Espiral.',
            isset($rayIdMatch[1]) ? 'Cloudflare Ray ID: '.$rayIdMatch[1] : null,
            isset($ipMatch[1]) ? 'IP del servidor bloqueada: '.$ipMatch[1] : null,
            'Accion requerida: solicita a Espiral que permita esta IP/Ray ID para el endpoint de creacion de lineas de pago.',
        ])));
    }

    private function cleanMoney(mixed $value): string
    {
        return preg_replace('/[^\d.]/', '', str_replace(',', '', (string) $value)) ?: '';
    }
}
