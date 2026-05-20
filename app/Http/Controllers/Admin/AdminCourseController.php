<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class AdminCourseController extends Controller
{
    public function index(): View
    {
        return view('admin.courses.index', [
            'courses' => Course::withCount('users')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.courses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'minimum_payment' => $this->normalizeMoney($request->input('minimum_payment')),
            'course_cost' => $this->normalizeMoney($request->input('course_cost')),
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'payment_start_date' => ['required', 'date'],
            'payment_end_date' => ['required', 'date', 'after_or_equal:payment_start_date'],
            'minimum_payment' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'course_cost' => ['required', 'numeric', 'min:1', 'max:999999.99', 'gte:minimum_payment'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:10000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($validated['title']);
        $baseSlug = $slug;
        $suffix = 2;

        while (Course::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        Course::create($validated + [
            'slug' => $slug,
            'price' => $validated['course_cost'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'Curso creado correctamente.');
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.edit', compact('course'));
    }

    public function show(Course $course): View
    {
        $course->load(['users' => fn ($query) => $query->orderBy('name')]);

        return view('admin.courses.show', [
            'course' => $course,
            'rows' => $this->enrollmentRows($course),
        ]);
    }

    public function export(Course $course): StreamedResponse
    {
        $course->load(['users' => fn ($query) => $query->orderBy('name')]);

        $filename = 'inscritos_'.$course->slug.'_'.now()->format('Ymd_His').'.xls';
        $rows = $this->enrollmentRows($course);

        return response()->streamDownload(function () use ($course, $rows) {
            echo view('admin.courses.export', [
                'course' => $course,
                'rows' => $rows,
            ])->render();
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $request->merge([
            'minimum_payment' => $this->normalizeMoney($request->input('minimum_payment')),
            'course_cost' => $this->normalizeMoney($request->input('course_cost')),
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'payment_start_date' => ['required', 'date'],
            'payment_end_date' => ['required', 'date', 'after_or_equal:payment_start_date'],
            'minimum_payment' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'course_cost' => ['required', 'numeric', 'min:1', 'max:999999.99', 'gte:minimum_payment'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:10000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($validated['title']);
        $baseSlug = $slug;
        $suffix = 2;

        while (Course::where('slug', $slug)->whereKeyNot($course->id)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        $course->update($validated + [
            'slug' => $slug,
            'price' => $validated['course_cost'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'Curso actualizado correctamente.');
    }

    public function confirmArchive(Course $course): View
    {
        return view('admin.courses.archive', [
            'course' => $course,
            'refunds' => $this->refundRows($course),
            'shouldRefund' => $this->shouldRefundArchivedCourse($course),
        ]);
    }

    public function archive(Course $course): RedirectResponse
    {
        if (! $course->is_active) {
            return redirect()
                ->route('admin.courses.index')
                ->with('status', 'El curso ya estaba archivado.');
        }

        $refunds = $this->shouldRefundArchivedCourse($course)
            ? $this->refundRows($course)
            : collect();

        DB::transaction(function () use ($course, $refunds) {
            foreach ($refunds as $refund) {
                Payment::create([
                    'user_id' => $refund['user']->id,
                    'course_id' => null,
                    'type' => Payment::TYPE_WALLET_CREDIT,
                    'amount' => $refund['amount'],
                    'method' => 'reembolso',
                    'status' => 'paid',
                    'reference' => 'Reembolso por curso '.$course->title,
                    'unica' => 'refund_'.$course->id.'_'.$refund['user']->id.'_'.str_replace('.', '', uniqid('', true)),
                    'paid_at' => now(),
                ]);
            }

            $course->update(['is_active' => false]);
        });

        $message = $refunds->isEmpty()
            ? 'Curso archivado correctamente.'
            : 'Curso archivado correctamente. Se procesaron '.$refunds->count().' reembolsos.';

        return redirect()
            ->route('admin.courses.index')
            ->with('status', $message);
    }

    private function enrollmentRows(Course $course): Collection
    {
        $courseCost = (float) ($course->course_cost ?: $course->price);

        return $course->users->map(function ($user) use ($course, $courseCost) {
            $paidTotal = (float) Payment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('status', 'paid')
                ->sum('amount');

            return [
                'user' => $user,
                'enrolled_at' => $user->pivot->enrolled_at,
                'paid_total' => $paidTotal,
                'remaining' => max(0, $courseCost - $paidTotal),
                'course_cost' => $courseCost,
                'status' => $paidTotal >= $courseCost ? 'Pagado' : 'Pendiente',
            ];
        });
    }

    private function shouldRefundArchivedCourse(Course $course): bool
    {
        return $course->end_date && now()->toDateString() < $course->end_date->toDateString();
    }

    private function refundRows(Course $course): Collection
    {
        if (! $this->shouldRefundArchivedCourse($course)) {
            return collect();
        }

        return $course->payments()
            ->where('type', Payment::TYPE_COURSE_PAYMENT)
            ->where('status', 'paid')
            ->where('is_condoned', false)
            ->with('user')
            ->select('user_id', DB::raw('SUM(amount) as amount'))
            ->groupBy('user_id')
            ->get()
            ->filter(fn ($payment) => (float) $payment->amount > 0 && $payment->user)
            ->map(fn ($payment) => [
                'user' => $payment->user,
                'amount' => (float) $payment->amount,
                'reference' => 'Reembolso por curso '.$course->title,
            ])
            ->values();
    }

    private function normalizeMoney(?string $value): ?string
    {
        return $value === null ? null : str_replace(',', '', $value);
    }
}
