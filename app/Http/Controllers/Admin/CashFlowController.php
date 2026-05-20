<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use Illuminate\View\View;

class CashFlowController extends Controller
{
    public function wallet(): View
    {
        $credits = Payment::where('type', Payment::TYPE_WALLET_CREDIT)
            ->where('is_condoned', false)
            ->with('user')
            ->latest()
            ->paginate(15);

        $totalCredits = Payment::where('type', Payment::TYPE_WALLET_CREDIT)
            ->where('is_condoned', false)
            ->sum('amount');

        return view('admin.cash-flow.wallet', compact('credits', 'totalCredits'));
    }

    public function finishedCycles(): View
    {
        $rows = Course::where('is_active', false)
            ->whereNotNull('archived_at')
            ->whereNotNull('end_date')
            ->whereRaw('DATE(archived_at) > end_date')
            ->orderByDesc('archived_at')
            ->get()
            ->map(fn (Course $course) => $this->courseFlowRow($course));

        $totalCollected = $rows->sum('collected_total');

        return view('admin.cash-flow.finished-cycles', compact('rows', 'totalCollected'));
    }

    public function activeFlow(): View
    {
        $rows = Course::where('is_active', true)
            ->orderBy('title')
            ->get()
            ->map(fn (Course $course) => $this->courseFlowRow($course));

        $totalCollected = $rows->sum('collected_total');

        return view('admin.cash-flow.active-flow', compact('rows', 'totalCollected'));
    }

    private function courseFlowRow(Course $course): array
    {
        return [
            'course' => $course,
            'students_count' => $course->users()->count(),
            'collected_total' => $this->courseCollectedTotal($course),
        ];
    }

    private function courseCollectedTotal(Course $course): float
    {
        return (float) Payment::where('course_id', $course->id)
            ->where('type', Payment::TYPE_COURSE_PAYMENT)
            ->where('status', 'paid')
            ->where('is_condoned', false)
            ->sum('amount');
    }
}
