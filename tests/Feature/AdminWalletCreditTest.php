<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWalletCreditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_only_wallet_credits(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $student = User::factory()->create(['name' => 'Alumno Cartera']);
        $course = Course::create([
            'title' => 'Curso prueba',
            'slug' => 'curso-prueba',
            'payment_start_date' => now()->subDay()->toDateString(),
            'payment_end_date' => now()->addDay()->toDateString(),
            'minimum_payment' => 100,
            'course_cost' => 1000,
            'price' => 1000,
        ]);

        Payment::create([
            'user_id' => $student->id,
            'course_id' => null,
            'type' => Payment::TYPE_WALLET_CREDIT,
            'amount' => 500,
            'method' => 'Tarjeta',
            'status' => 'paid',
            'reference' => 'Credito visible',
            'unica' => 'visible-wallet-credit',
        ]);
        Payment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'type' => Payment::TYPE_COURSE_PAYMENT,
            'amount' => 300,
            'method' => 'cartera',
            'status' => 'paid',
            'reference' => 'Pago curso oculto',
            'unica' => 'hidden-course-payment',
        ]);
        Payment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'type' => Payment::TYPE_CONDONATION,
            'amount' => 200,
            'method' => 'condonacion',
            'status' => 'paid',
            'reference' => 'Condonacion oculta',
            'unica' => 'hidden-condonation',
            'is_condoned' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.wallet-credits.index'));

        $response
            ->assertOk()
            ->assertSee('Credito visible')
            ->assertDontSee('Pago curso oculto')
            ->assertDontSee('Condonacion oculta');
    }
}
