<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseArchiveRefundTest extends TestCase
{
    use RefreshDatabase;

    public function test_archiving_unfinished_course_refunds_paid_students_to_wallet(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $student = User::factory()->create();
        $course = Course::create([
            'title' => 'Curso con reembolso',
            'slug' => 'curso-con-reembolso',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'payment_start_date' => now()->subDay()->toDateString(),
            'payment_end_date' => now()->addDays(5)->toDateString(),
            'minimum_payment' => 100,
            'course_cost' => 1000,
            'price' => 1000,
            'duration_hours' => 10,
            'is_active' => true,
        ]);

        $student->courses()->attach($course);
        $student->payments()->create([
            'course_id' => null,
            'type' => Payment::TYPE_WALLET_CREDIT,
            'amount' => 1000,
            'method' => 'Tarjeta',
            'status' => 'paid',
            'reference' => 'Carga inicial',
            'unica' => 'wallet-credit-before-refund',
            'paid_at' => now(),
        ]);
        $student->payments()->create([
            'course_id' => $course->id,
            'type' => Payment::TYPE_COURSE_PAYMENT,
            'amount' => 600,
            'method' => 'cartera',
            'status' => 'paid',
            'reference' => 'Pago de curso desde cartera',
            'unica' => 'course-payment-before-refund',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.courses.archive', $course));

        $response->assertRedirect(route('admin.courses.index'));
        $this->assertFalse($course->fresh()->is_active);
        $this->assertNotNull($course->fresh()->archived_at);
        $this->assertDatabaseHas(Payment::class, [
            'user_id' => $student->id,
            'course_id' => null,
            'type' => Payment::TYPE_WALLET_CREDIT,
            'amount' => 600,
            'method' => 'reembolso',
            'status' => 'paid',
            'reference' => 'Reembolso por curso Curso con reembolso',
        ]);
        $this->assertSame(1000.0, $student->fresh()->walletBalance());
    }
}
