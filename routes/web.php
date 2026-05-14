<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/registro', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/registro', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/cursos', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/mis-cursos', [CourseController::class, 'myCourses'])->name('courses.mine');
    Route::get('/mis-cursos/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::post('/cursos/{course}/registro', [CourseController::class, 'enroll'])->name('courses.enroll');

    Route::get('/pagos', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/pagos/nuevo', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/pagos', [PaymentController::class, 'store'])->name('payments.store');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
