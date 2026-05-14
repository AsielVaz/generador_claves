<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
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

    public function store(Request $request): RedirectResponse
    {
        $courseIds = $request->user()->courses()->pluck('courses.id')->all();

        $validated = $request->validate([
            'course_id' => ['required', Rule::in($courseIds)],
            'payment_file' => ['required', 'file', 'max:1024'],
        ]);

        $file = $request->file('payment_file');

        if (strtolower($file->getClientOriginalExtension()) !== '10hf') {
            return back()
                ->withErrors(['payment_file' => 'El archivo debe tener extension .10hf.'])
                ->withInput();
        }

        $paymentData = json_decode($file->get(), true);

        if (! is_array($paymentData)) {
            return back()
                ->withErrors(['payment_file' => 'El archivo 10hf debe contener un JSON valido.'])
                ->withInput();
        }

        $fileValidator = Validator::make($paymentData, [
            'amount' => ['required', 'numeric', 'min:1', 'max:999999.99'],
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
            return back()
                ->withErrors(['payment_file' => 'El JSON del archivo no tiene la estructura esperada.'])
                ->withInput();
        }

        if (Payment::where('unica', $paymentData['unica'])->exists()) {
            return back()
                ->withErrors(['payment_file' => 'Este pago ya fue cargado anteriormente.'])
                ->withInput();
        }

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
}
