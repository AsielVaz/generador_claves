<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CondonationController extends Controller
{
    public function index(): View
    {
        return view('admin.condonations.index', [
            'users' => User::where('is_admin', false)->orderBy('name')->get(),
            'courses' => Course::whereHas('users')->orderBy('title')->get(),
            'condonations' => Payment::where('is_condoned', true)
                ->with(['user', 'course'])
                ->latest()
                ->take(20)
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'course_id' => ['required', 'exists:courses,id'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $course = Course::findOrFail($validated['course_id']);

        if (! $user->courses()->where('courses.id', $course->id)->exists()) {
            return back()
                ->withErrors(['condonation' => 'El usuario no esta inscrito en ese curso.'])
                ->withInput();
        }

        if (Payment::where('user_id', $user->id)->where('course_id', $course->id)->where('is_condoned', true)->exists()) {
            return back()
                ->withErrors(['condonation' => 'Este curso ya fue condonado para el usuario seleccionado.'])
                ->withInput();
        }

        $courseCost = (float) ($course->course_cost ?: $course->price);
        $paidTotal = (float) Payment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', 'paid')
            ->sum('amount');
        $remaining = max(0, $courseCost - $paidTotal);

        if ($remaining <= 0) {
            return back()
                ->withErrors(['condonation' => 'El usuario ya cubrio el costo total del curso.'])
                ->withInput();
        }

        Payment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => $remaining,
            'method' => 'condonacion',
            'status' => 'paid',
            'reference' => 'Pago condonado por administrador',
            'unica' => 'condonacion-'.$user->id.'-'.$course->id,
            'is_condoned' => true,
            'paid_at' => now(),
        ]);

        return back()->with('status', 'Pago condonado correctamente.');
    }
}
