<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

    public function update(Request $request, Course $course): RedirectResponse
    {
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
}
