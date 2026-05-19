<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::where('is_active', true)
            ->withCount('users')
            ->orderBy('title')
            ->get();

        $enrolledIds = $request->user()
            ->courses()
            ->pluck('courses.id')
            ->all();

        return view('courses.index', compact('courses', 'enrolledIds'));
    }

    public function enroll(Request $request, Course $course): RedirectResponse
    {
        $request->user()->courses()->syncWithoutDetaching([
            $course->id => ['enrolled_at' => now()],
        ]);

        return back()->with('status', 'Te registraste correctamente en el curso.');
    }

    public function myCourses(Request $request): View
    {
        $today = now()->toDateString();

        $courses = $request->user()
            ->courses()
            ->withSum(['payments as paid_total' => function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->where('status', 'paid');
            }], 'amount')
            ->orderBy('title')
            ->get();

        $walletBalance = $request->user()->walletBalance();

        return view('courses.mine', compact('courses', 'today', 'walletBalance'));
    }

    public function pay(Request $request, Course $course): RedirectResponse
    {
        $user = $request->user();
        $isEnrolled = $user->courses()
            ->where('courses.id', $course->id)
            ->exists();

        if (! $isEnrolled) {
            return redirect()
                ->route('courses.mine')
                ->withErrors(['course' => 'No estas inscrito en este curso.']);
        }

        $paidTotal = (float) $course->payments()
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount');
        $courseCost = (float) ($course->course_cost ?: $course->price);
        $remainingCourseBalance = max(0, round($courseCost - $paidTotal, 2));
        $walletBalance = $user->walletBalance();
        $maxAmount = min($remainingCourseBalance, $walletBalance);

        if ($maxAmount < 1) {
            return back()
                ->withErrors(['course' => 'No tienes saldo suficiente en tu cartera para pagar este curso.'])
                ->withInput();
        }

        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'numeric', 'min:1', 'max:'.$maxAmount],
        ], [], [
            'amount' => 'monto',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors(['amount_'.$course->id => $validator->errors()->first('amount')])
                ->withInput();
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($user, $course, $validated) {
            $user->payments()->create([
                'course_id' => $course->id,
                'type' => Payment::TYPE_COURSE_PAYMENT,
                'amount' => $validated['amount'],
                'method' => 'cartera',
                'status' => 'paid',
                'reference' => 'Pago de curso desde cartera',
                'unica' => 'wallet_'.uniqid('', true),
                'paid_at' => now(),
            ]);
        });

        return back()->with('status', 'Pago aplicado correctamente al curso.');
    }

    public function show(Request $request, Course $course): View|RedirectResponse
    {
        $isEnrolled = $request->user()
            ->courses()
            ->where('courses.id', $course->id)
            ->exists();

        if (! $isEnrolled) {
            return redirect()
                ->route('courses.mine')
                ->withErrors(['course' => 'No estas inscrito en este curso.']);
        }

        $paidTotal = $course->payments()
            ->where('user_id', $request->user()->id)
            ->where('status', 'paid')
            ->sum('amount');

        $minimumPayment = (float) $course->minimum_payment;
        $courseCost = (float) ($course->course_cost ?: $course->price);
        $paymentPeriodEnded = $course->payment_end_date && now()->toDateString() > $course->payment_end_date->toDateString();

        if ($paidTotal < $minimumPayment || ($paymentPeriodEnded && $paidTotal < $courseCost)) {
            return redirect()
                ->route('courses.mine')
                ->withErrors(['course' => 'El curso esta bloqueado por reglas de pago.']);
        }

        return view('courses.show', compact('course', 'paidTotal'));
    }
}
