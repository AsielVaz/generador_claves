<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    public function index(): View
    {
        $courses = Course::with(['users' => fn ($query) => $query->orderBy('name')])
            ->orderBy('title')
            ->get();

        $enrollmentReport = $courses->map(function (Course $course): array {
            return [
                'course' => $course,
                'students_count' => $course->users->count(),
                'students' => $course->users,
            ];
        });

        $rows = $courses->flatMap(function (Course $course): Collection {
            $courseCost = (float) ($course->course_cost ?: $course->price);

            return $course->users->map(function ($user) use ($course, $courseCost) {
                $paidTotal = (float) Payment::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->where('status', 'paid')
                    ->sum('amount');

                return [
                    'user' => $user,
                    'course' => $course,
                    'paid_total' => $paidTotal,
                    'remaining' => max(0, $courseCost - $paidTotal),
                    'course_cost' => $courseCost,
                ];
            });
        });

        return view('admin.reports.index', compact('enrollmentReport', 'rows'));
    }
}
