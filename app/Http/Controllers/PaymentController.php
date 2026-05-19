<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    private const PAYMENT_FILE_KEY = 'Encr10h-.$=2023SecretoMuajaaja';
    private const PAYMENT_FILE_CIPHER = 'aes-256-cbc';

    public function index(Request $request): View
    {
        $payments = $request->user()
            ->payments()
            ->with('course')
            ->latest()
            ->paginate(10);

        return view('payments.index', compact('payments'));
    }

    public function create(Request $request): View
    {
        $courses = $request->user()
            ->courses()
            ->orderBy('title')
            ->get();

        return view('payments.create', compact('courses'));
    }

    public function preview(Request $request): JsonResponse
    {
        $courseIds = $request->user()->courses()->pluck('courses.id')->all();

        $request->validate([
            'course_id' => ['required', Rule::in($courseIds)],
            'payment_file' => ['required', 'file', 'max:1024'],
        ]);

        $paymentFile = $this->readPaymentFile($request->file('payment_file'));

        if (isset($paymentFile['error'])) {
            return response()->json([
                'message' => $paymentFile['error'],
                'decrypted_content' => $paymentFile['decrypted_content'] ?? null,
            ], 422);
        }

        $paymentData = $paymentFile['data'];

        return response()->json([
            'payment' => [
                'amount' => (float) $paymentData['amount'],
                'method' => $paymentData['method'],
                'status' => $paymentData['status'],
                'reference' => $paymentData['reference'] ?? null,
                'unica' => $paymentData['unica'],
                'paid_at' => $paymentData['paid_at'] ?? null,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $courseIds = $request->user()->courses()->pluck('courses.id')->all();

        $validated = $request->validate([
            'course_id' => ['required', Rule::in($courseIds)],
            'payment_file' => ['required', 'file', 'max:1024'],
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $today = now()->toDateString();

        if (! $course->payment_start_date || ! $course->payment_end_date) {
            return back()
                ->withErrors(['course_id' => 'Este curso aun no tiene un periodo de pago configurado.'])
                ->withInput();
        }

        if ($today < $course->payment_start_date->toDateString() || $today > $course->payment_end_date->toDateString()) {
            return back()
                ->withErrors(['course_id' => 'Solo puedes cargar pagos dentro del periodo permitido del curso.'])
                ->withInput();
        }

        $paymentFile = $this->readPaymentFile($request->file('payment_file'));

        if (isset($paymentFile['error'])) {
            return back()
                ->withErrors(['payment_file' => $paymentFile['error']])
                ->with('decrypted_payment_content', $paymentFile['decrypted_content'] ?? null)
                ->withInput();
        }

        $paymentData = $paymentFile['data'];

        $request->user()->payments()->create([
            'course_id' => $validated['course_id'],
            'amount' => $paymentData['amount'],
            'method' => $paymentData['method'],
            'status' => $paymentData['status'],
            'reference' => $paymentData['reference'] ?? null,
            'unica' => $paymentData['unica'],
            'paid_at' => $paymentData['paid_at'] ?? null,
        ]);

        return redirect()
            ->route('payments.index')
            ->with('status', 'Pago cargado correctamente desde el archivo 10hf.');
    }

    private function readPaymentFile($file): array
    {
        if (strtolower($file->getClientOriginalExtension()) !== '10hf') {
            return ['error' => 'El archivo debe tener extension .10hf.'];
        }

        $decryptedContent = $this->decryptPaymentFile($file->get());

        if ($decryptedContent === false) {
            return ['error' => 'No se pudo desencriptar el archivo 10hf.'];
        }

        $paymentData = json_decode($decryptedContent, true);

        if (! is_array($paymentData)) {
            return [
                'error' => 'El archivo 10hf desencriptado debe contener un JSON valido.',
                'decrypted_content' => $decryptedContent,
            ];
        }

        $fileValidator = Validator::make($paymentData, [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'method' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:255'],
            'unica' => ['required', 'string', 'max:128'],
            'paid_at' => ['nullable', 'date'],
        ], [], [
            'amount' => 'monto',
            'method' => 'metodo',
            'status' => 'estado',
            'reference' => 'referencia',
            'unica' => 'clave unica',
            'paid_at' => 'fecha de pago',
        ]);

        if ($fileValidator->fails()) {
            return [
                'error' => 'El JSON del archivo no tiene la estructura esperada: '.$fileValidator->errors()->first(),
                'decrypted_content' => $decryptedContent,
            ];
        }

        if (Payment::where('unica', $paymentData['unica'])->exists()) {
            return [
                'error' => 'Este pago ya fue cargado anteriormente.',
                'decrypted_content' => $decryptedContent,
            ];
        }

        return ['data' => $paymentData];
    }

    private function decryptPaymentFile(string $encryptedContent): string|false
    {
        $decodedContent = base64_decode(trim($encryptedContent), true);

        if ($decodedContent === false) {
            return false;
        }

        $ivLength = openssl_cipher_iv_length(self::PAYMENT_FILE_CIPHER);

        if ($ivLength === false || strlen($decodedContent) <= $ivLength) {
            return false;
        }

        $iv = substr($decodedContent, 0, $ivLength);
        $encryptedPayload = substr($decodedContent, $ivLength);

        return openssl_decrypt(
            $encryptedPayload,
            self::PAYMENT_FILE_CIPHER,
            self::PAYMENT_FILE_KEY,
            0,
            $iv
        );
    }
}
