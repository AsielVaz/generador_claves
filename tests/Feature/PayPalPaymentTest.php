<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayPalPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_generate_paypal_order(): void
    {
        $this->configurePayPal();

        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'sandbox-token',
                'token_type' => 'Bearer',
            ]),
            'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'ORDER-123',
                'status' => 'CREATED',
                'links' => [
                    [
                        'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=ORDER-123',
                        'rel' => 'approve',
                        'method' => 'GET',
                    ],
                ],
            ], 201),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('payments.card.store'), [
            'amount' => '1,250.50',
        ]);

        $response->assertRedirect('https://www.sandbox.paypal.com/checkoutnow?token=ORDER-123');

        $this->assertDatabaseHas(Payment::class, [
            'user_id' => $user->id,
            'course_id' => null,
            'type' => Payment::TYPE_WALLET_CREDIT,
            'amount' => 1250.50,
            'method' => 'PayPal Sandbox',
            'status' => 'pendiente',
            'reference' => 'PayPal order ORDER-123',
        ]);

        Http::assertSent(fn ($request) => $request->url() === 'https://api-m.sandbox.paypal.com/v2/checkout/orders'
            && $request['intent'] === 'CAPTURE'
            && $request['purchase_units'][0]['amount']['currency_code'] === 'MXN'
            && $request['purchase_units'][0]['amount']['value'] === '1250.50'
            && $request['payment_source']['paypal']['experience_context']['brand_name'] === 'CryptoEfectivo'
            && $request['payment_source']['paypal']['experience_context']['user_action'] === 'PAY_NOW');
    }

    public function test_paypal_return_captures_order_and_credits_wallet(): void
    {
        $this->configurePayPal();

        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'sandbox-token',
                'token_type' => 'Bearer',
            ]),
            'api-m.sandbox.paypal.com/v2/checkout/orders/ORDER-123/capture' => Http::response([
                'id' => 'ORDER-123',
                'status' => 'COMPLETED',
                'purchase_units' => [
                    [
                        'payments' => [
                            'captures' => [
                                [
                                    'id' => 'CAPTURE-123',
                                    'status' => 'COMPLETED',
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $user = User::factory()->create();
        $payment = $user->payments()->create([
            'course_id' => null,
            'type' => Payment::TYPE_WALLET_CREDIT,
            'amount' => 300,
            'method' => 'PayPal Sandbox',
            'status' => 'pendiente',
            'reference' => 'PayPal order ORDER-123',
            'unica' => 'paypal-test-reference',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('payments.card.success', $payment->unica).'?token=ORDER-123');

        $response->assertRedirect(route('payments.index'));

        $this->assertDatabaseHas(Payment::class, [
            'id' => $payment->id,
            'status' => 'paid',
            'reference' => 'PayPal #CAPTURE-123',
        ]);
        $this->assertSame(300.0, $user->fresh()->walletBalance());
    }

    private function configurePayPal(): void
    {
        config([
            'services.paypal.client' => 'sandbox-client',
            'services.paypal.secret' => 'sandbox-secret',
            'services.paypal.base_url' => 'https://api-m.sandbox.paypal.com',
        ]);
    }
}
