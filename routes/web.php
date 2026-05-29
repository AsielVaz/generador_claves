<?php

use App\Http\Controllers\Admin\AdminCourseController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\CashFlowController;
use App\Http\Controllers\Admin\CondonationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayPalPaymentController;
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
    Route::post('/mis-cursos/{course}/pagar', [CourseController::class, 'pay'])->name('courses.pay');
    Route::post('/cursos/{course}/registro', [CourseController::class, 'enroll'])->name('courses.enroll');

    Route::get('/pagos', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/pagos/tarjeta', [PayPalPaymentController::class, 'create'])->name('payments.card.create');
    Route::post('/pagos/tarjeta', [PayPalPaymentController::class, 'store'])->name('payments.card.store');
    Route::get('/pagos/tarjeta/{reference}/exito', [PayPalPaymentController::class, 'success'])->name('payments.card.success');
    Route::get('/pagos/tarjeta/{reference}/cancelado', [PayPalPaymentController::class, 'cancel'])->name('payments.card.cancel');
    Route::get('/pagos/nuevo', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/pagos/previsualizar', [PaymentController::class, 'preview'])->name('payments.preview');
    Route::post('/pagos', [PaymentController::class, 'store'])->name('payments.store');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::get('/usuarios', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/usuarios/nuevo', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/usuarios', [AdminUserController::class, 'store'])->name('users.store');
        Route::patch('/usuarios/{user}/admin', [AdminUserController::class, 'toggleAdmin'])->name('users.toggle-admin');

        Route::get('/cursos', [AdminCourseController::class, 'index'])->name('courses.index');
        Route::get('/cursos/nuevo', [AdminCourseController::class, 'create'])->name('courses.create');
        Route::post('/cursos', [AdminCourseController::class, 'store'])->name('courses.store');
        Route::get('/cursos/{course}/detalles', [AdminCourseController::class, 'show'])->name('courses.show');
        Route::get('/cursos/{course}/inscritos/excel', [AdminCourseController::class, 'export'])->name('courses.export');
        Route::get('/cursos/{course}/editar', [AdminCourseController::class, 'edit'])->name('courses.edit');
        Route::put('/cursos/{course}', [AdminCourseController::class, 'update'])->name('courses.update');
        Route::get('/cursos/{course}/archivar', [AdminCourseController::class, 'confirmArchive'])->name('courses.archive.confirm');
        Route::patch('/cursos/{course}/archivar', [AdminCourseController::class, 'archive'])->name('courses.archive');

        Route::get('/condonaciones', [CondonationController::class, 'index'])->name('condonations.index');
        Route::post('/condonaciones', [CondonationController::class, 'store'])->name('condonations.store');

        Route::redirect('/saldos-cartera', '/admin/flujo-efectivo');
        Route::get('/flujo-efectivo', [CashFlowController::class, 'wallet'])->name('cash-flow.wallet');
        Route::get('/ciclos-finiquitados', [CashFlowController::class, 'finishedCycles'])->name('cash-flow.finished-cycles');
        Route::get('/flujo-activo', [CashFlowController::class, 'activeFlow'])->name('cash-flow.active');
        Route::get('/saldo-sin-usar', [CashFlowController::class, 'unusedBalance'])->name('cash-flow.unused-balance');

        Route::get('/reportes', [AdminReportController::class, 'index'])->name('reports.index');
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
