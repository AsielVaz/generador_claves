<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EspiralPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_generate_espiral_card_payment_link(): void
    {
        config([
            'services.espiral.key' => 'test-key',
            'services.espiral.base_url' => 'https://cart.espiralapp.com',
        ]);

        Http::fake([
            'cart.espiralapp.com/payOrder*' => Http::response([
                'generatedToken' => 'test-token',
            ]),
        ]);

        $user = User::factory()->create([
            'name' => 'Cliente Prueba',
            'email' => 'cliente@example.com',
        ]);

        $response = $this->actingAs($user)->post(route('payments.card.store'), [
            'amount' => '1,250.50',
            'phone' => '5551234567',
            'street' => 'Av. Patria',
            'number_ext' => '100',
            'number_int' => 'A',
            'zip_code' => '45100',
            'city' => 'Zapopan',
            'state' => 'Jalisco',
        ]);

        $response->assertRedirect('https://cart.espiralapp.com/test-token');

        $this->assertDatabaseHas(Payment::class, [
            'user_id' => $user->id,
            'course_id' => null,
            'type' => Payment::TYPE_WALLET_CREDIT,
            'amount' => 1250.50,
            'method' => 'Tarjeta Espiral',
            'status' => 'pendiente',
        ]);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/payOrder?key=test-key')
            && $request['transaction']['total'] === 1250.50
            && $request['metadata']['user_id'] === $user->id);
    }

    public function test_espiral_webhook_credits_wallet_when_payment_is_approved(): void
    {
        $user = User::factory()->create();
        $payment = $user->payments()->create([
            'course_id' => null,
            'type' => Payment::TYPE_WALLET_CREDIT,
            'amount' => 300,
            'method' => 'Tarjeta Espiral',
            'status' => 'pendiente',
            'reference' => 'Pago con tarjeta pendiente',
            'unica' => 'espiral-test-reference',
        ]);

        $response = $this->postJson(route('webhooks.espiral', $payment->unica), [
            'response' => [
                'message' => 'Approved',
                'data' => [
                    'transactionId' => '1607',
                    'autStatusResult' => 'A',
                    'autResult' => '00',
                    'reference' => '695512279890',
                ],
            ],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'paid');

        $this->assertDatabaseHas(Payment::class, [
            'id' => $payment->id,
            'status' => 'paid',
            'reference' => 'Espiral #1607',
        ]);
        $this->assertSame(300.0, $user->fresh()->walletBalance());
    }
}
