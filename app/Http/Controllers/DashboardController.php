<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        $enrolledCourses = $user->courses()
            ->latest('course_user.enrolled_at')
            ->take(4)
            ->get();

        $recentPayments = $user->payments()
            ->with('course')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', [
            'availableCourses' => Course::where('is_active', true)->count(),
            'enrolledCoursesCount' => $user->courses()->count(),
            'enrolledCourses' => $enrolledCourses,
            'recentPayments' => $recentPayments,
            'paymentsTotal' => Payment::where('user_id', $user->id)
                ->where('type', Payment::TYPE_WALLET_CREDIT)
                ->sum('amount'),
            'walletBalance' => $user->walletBalance(),
        ]);
    }
}
