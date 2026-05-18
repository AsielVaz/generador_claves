<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $paidStatuses = ['paid', 'pagado', 'aprobado'];

        return view('admin.dashboard', [
            'studentsCount' => User::where('is_admin', false)->count(),
            'activeCoursesCount' => Course::where('is_active', true)->count(),
            'enrollmentsCount' => DB::table('course_user')->count(),
            'reportedPaymentsTotal' => Payment::sum('amount'),
            'approvedPaymentsTotal' => Payment::whereIn('status', $paidStatuses)->sum('amount'),
            'pendingPaymentsCount' => Payment::where('status', 'pendiente')->count(),
            'condonedTotal' => Payment::where('is_condoned', true)->sum('amount'),
            'recentPayments' => Payment::with(['user', 'course'])
                ->latest()
                ->take(6)
                ->get(),
            'topCourses' => Course::withCount('users')
                ->orderByDesc('users_count')
                ->orderBy('title')
                ->take(5)
                ->get(),
            'paymentDeadlines' => Course::withCount('users')
                ->where('is_active', true)
                ->whereNotNull('payment_end_date')
                ->whereDate('payment_end_date', '>=', now()->toDateString())
                ->orderBy('payment_end_date')
                ->take(5)
                ->get(),
            'recentUsers' => User::where('is_admin', false)
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
