<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseWalletPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_pay_course_from_wallet_balance(): void
    {
        $user = User::factory()->create();
        $course = Course::create([
            'title' => 'Curso cartera',
            'slug' => 'curso-cartera',
            'payment_start_date' => now()->subDay()->toDateString(),
            'payment_end_date' => now()->addDay()->toDateString(),
            'minimum_payment' => 100,
            'course_cost' => 1000,
            'price' => 1000,
        ]);

        $user->courses()->attach($course);
        $user->payments()->create([
            'course_id' => null,
            'type' => Payment::TYPE_WALLET_CREDIT,
            'amount' => 500,
            'method' => 'Tarjeta',
            'status' => 'paid',
            'reference' => 'Credito de prueba',
            'unica' => 'wallet-credit-test',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('courses.pay', $course), [
            'amount' => 250,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas(Payment::class, [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'type' => Payment::TYPE_COURSE_PAYMENT,
            'amount' => 250,
            'method' => 'cartera',
            'status' => 'paid',
        ]);
        $this->assertSame(250.0, $user->fresh()->walletBalance());
    }
}
