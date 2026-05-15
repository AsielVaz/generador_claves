<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'usersCount' => User::where('is_admin', false)->count(),
            'adminsCount' => User::where('is_admin', true)->count(),
            'coursesCount' => Course::count(),
            'condonedTotal' => Payment::where('is_condoned', true)->sum('amount'),
        ]);
    }
}
