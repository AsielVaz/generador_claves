<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $courses = $request->user()
            ->courses()
            ->withSum(['payments as paid_total' => function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->where('status', 'paid');
            }], 'amount')
            ->orderBy('title')
            ->get();

        return view('courses.mine', compact('courses'));
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

        if ($paidTotal < $course->price) {
            return redirect()
                ->route('courses.mine')
                ->withErrors(['course' => 'Completa el pago total del curso para desbloquearlo.']);
        }

        return view('courses.show', compact('course', 'paidTotal'));
    }
}
