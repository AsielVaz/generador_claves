<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PayPalPaymentController extends Controller
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
        ], [], [
            'amount' => 'monto',
        ]);

        if (! config('services.paypal.client') || ! config('services.paypal.secret')) {
            return back()
                ->withErrors(['paypal' => 'No estan configuradas las credenciales PAYPAL_CLIENT y PAYPAL_SECRET.'])
                ->withInput();
        }

        $user = $request->user();
        $amount = round((float) $validated['amount'], 2);
        $localReference = 'paypal-'.now()->format('YmdHis').'-'.Str::lower(Str::random(8));

        $payment = $user->payments()->create([
            'course_id' => null,
            'type' => Payment::TYPE_WALLET_CREDIT,
            'amount' => $amount,
            'method' => 'PayPal Sandbox',
            'status' => 'pendiente',
            'reference' => 'Orden PayPal pendiente',
            'unica' => $localReference,
        ]);

        $token = $this->accessToken();

        if (! $token) {
            $payment->update([
                'status' => 'error',
                'reference' => 'PayPal no genero token de acceso',
            ]);

            return back()
                ->withErrors(['paypal' => 'PayPal no genero token de acceso. Revisa las credenciales sandbox.'])
                ->with('paypal_api_error', session('paypal_api_error'))
                ->withInput();
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->timeout(20)
            ->post($this->baseUrl().'/v2/checkout/orders', $this->buildOrderPayload($payment));

        $body = $response->json();
        $approvalUrl = is_array($body) ? $this->approvalUrlFromResponse($body) : null;

        if (! $response->successful() || ! $approvalUrl) {
            $payment->update([
                'status' => 'error',
                'reference' => 'PayPal no genero la orden',
            ]);

            Log::warning('PayPal order could not be generated.', [
                'payment_id' => $payment->id,
                'status' => $response->status(),
                'response' => $body ?? $response->body(),
            ]);

            return back()
                ->withErrors(['paypal' => 'PayPal no genero la orden de pago. Revisa los datos e intentalo nuevamente.'])
                ->with('paypal_api_error', $this->formatPayPalErrorDetails($response, $body))
                ->withInput();
        }

        $payment->update([
            'reference' => 'PayPal order '.data_get($body, 'id', $payment->unica),
        ]);

        return redirect()->away($approvalUrl);
    }

    public function success(Request $request, string $reference): RedirectResponse
    {
        $payment = $request->user()
            ->payments()
            ->where('unica', $reference)
            ->firstOrFail();

        if ($payment->status === 'paid') {
            return redirect()
                ->route('payments.index')
                ->with('status', 'El pago de PayPal ya estaba acreditado en tu cartera.');
        }

        $orderId = $request->query('token');

        if (! $orderId) {
            return redirect()
                ->route('payments.card.create')
                ->withErrors(['paypal' => 'PayPal no regreso el identificador de la orden.'])
                ->withInput();
        }

        $token = $this->accessToken();

        if (! $token) {
            return redirect()
                ->route('payments.card.create')
                ->withErrors(['paypal' => 'PayPal no genero token de acceso para capturar la orden.'])
                ->with('paypal_api_error', session('paypal_api_error'));
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->timeout(20)
            ->post($this->baseUrl().'/v2/checkout/orders/'.$orderId.'/capture');

        $body = $response->json();
        $captureStatus = data_get($body, 'status');
        $captureId = data_get($body, 'purchase_units.0.payments.captures.0.id', $orderId);

        if ($response->successful() && $captureStatus === 'COMPLETED') {
            $payment->update([
                'status' => 'paid',
                'method' => 'PayPal Sandbox',
                'reference' => 'PayPal #'.$captureId,
                'paid_at' => now(),
            ]);

            return redirect()
                ->route('payments.index')
                ->with('status', 'Pago de PayPal acreditado correctamente en tu cartera.');
        }

        $payment->update([
            'status' => 'error',
            'reference' => 'PayPal no capturo la orden '.$orderId,
        ]);

        Log::warning('PayPal order could not be captured.', [
            'payment_id' => $payment->id,
            'order_id' => $orderId,
            'status' => $response->status(),
            'response' => $body ?? $response->body(),
        ]);

        return redirect()
            ->route('payments.card.create')
            ->withErrors(['paypal' => 'PayPal no pudo capturar la orden.'])
            ->with('paypal_api_error', $this->formatPayPalErrorDetails($response, $body));
    }

    public function cancel(string $reference): RedirectResponse
    {
        Payment::where('unica', $reference)
            ->where('status', 'pendiente')
            ->update([
                'status' => 'cancelado',
                'reference' => 'Orden PayPal cancelada',
            ]);

        return redirect()
            ->route('payments.card.create')
            ->withErrors(['paypal' => 'El pago de PayPal fue cancelado. Puedes intentarlo nuevamente.']);
    }

    private function accessToken(): ?string
    {
        $response = Http::asForm()
            ->acceptJson()
            ->withBasicAuth((string) config('services.paypal.client'), (string) config('services.paypal.secret'))
            ->timeout(20)
            ->post($this->baseUrl().'/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        $body = $response->json();
        $token = is_array($body) ? data_get($body, 'access_token') : null;

        if (! $response->successful() || ! is_string($token) || $token === '') {
            Log::warning('PayPal access token could not be generated.', [
                'status' => $response->status(),
                'response' => $body ?? $response->body(),
            ]);

            session()->flash('paypal_api_error', $this->formatPayPalErrorDetails($response, $body));

            return null;
        }

        return $token;
    }

    private function buildOrderPayload(Payment $payment): array
    {
        $value = number_format((float) $payment->amount, 2, '.', '');

        return [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $payment->unica,
                    'custom_id' => $payment->unica,
                    'description' => 'Abono a cartera CryptoEfectivo',
                    'amount' => [
                        'currency_code' => 'MXN',
                        'value' => $value,
                    ],
                ],
            ],
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'brand_name' => 'CryptoEfectivo',
                        'landing_page' => 'LOGIN',
                        'shipping_preference' => 'NO_SHIPPING',
                        'user_action' => 'PAY_NOW',
                        'return_url' => route('payments.card.success', $payment->unica),
                        'cancel_url' => route('payments.card.cancel', $payment->unica),
                    ],
                ],
            ],
        ];
    }

    private function approvalUrlFromResponse(array $response): ?string
    {
        foreach ((array) data_get($response, 'links', []) as $link) {
            if (in_array(data_get($link, 'rel'), ['approve', 'payer-action'], true)) {
                return data_get($link, 'href');
            }
        }

        return null;
    }

    private function formatPayPalErrorDetails($response, mixed $body): string
    {
        $responseBody = is_array($body)
            ? json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : $response->body();

        if (! is_string($responseBody) || trim($responseBody) === '') {
            $responseBody = 'Sin cuerpo de respuesta.';
        }

        return trim(implode("\n\n", [
            'HTTP status: '.$response->status(),
            'Content-Type: '.($response->header('Content-Type') ?: 'Sin Content-Type'),
            'Respuesta de PayPal:',
            Str::limit($responseBody, 5000, "\n... respuesta truncada ..."),
        ]));
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.paypal.base_url'), '/');
    }

    private function cleanMoney(mixed $value): string
    {
        return preg_replace('/[^\d.]/', '', str_replace(',', '', (string) $value)) ?: '';
    }
}
