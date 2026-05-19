<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EncryptedPaymentUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_encrypted_payment_file(): void
    {
        $user = User::factory()->create();
        $course = Course::create([
            'title' => 'Curso de prueba',
            'slug' => 'curso-de-prueba',
            'payment_start_date' => now()->subDay()->toDateString(),
            'payment_end_date' => now()->addDay()->toDateString(),
            'minimum_payment' => 100,
            'course_cost' => 1000,
            'price' => 1000,
        ]);

        $user->courses()->attach($course);

        $payload = [
            'amount' => 1500,
            'method' => 'transferencia',
            'status' => 'paid',
            'reference' => 'REF-123',
            'unica' => 'encrypted-upload-test',
            'paid_at' => now()->toDateString(),
        ];

        $file = UploadedFile::fake()->createWithContent(
            'payment.10hf',
            $this->encryptPaymentPayload(json_encode($payload, JSON_THROW_ON_ERROR))
        );

        $response = $this->actingAs($user)->post(route('payments.store'), [
            'course_id' => $course->id,
            'payment_file' => $file,
        ]);

        $response->assertRedirect(route('payments.index'));

        $this->assertDatabaseHas(Payment::class, [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 1500,
            'method' => 'transferencia',
            'status' => 'paid',
            'reference' => 'REF-123',
            'unica' => 'encrypted-upload-test',
        ]);
    }

    public function test_user_can_preview_encrypted_payment_file(): void
    {
        $user = User::factory()->create();
        $course = Course::create([
            'title' => 'Curso de prueba',
            'slug' => 'curso-de-prueba-preview',
            'payment_start_date' => now()->subDay()->toDateString(),
            'payment_end_date' => now()->addDay()->toDateString(),
            'minimum_payment' => 100,
            'course_cost' => 1000,
            'price' => 1000,
        ]);

        $user->courses()->attach($course);

        $payload = [
            'amount' => 0.04,
            'method' => 'Tarjeta',
            'status' => 'paid',
            'reference' => 'REF-PREVIEW',
            'email' => 'asielsempai@gmail.com',
            'unica' => 'encrypted-preview-test',
            'paid_at' => now()->toDateString(),
        ];

        $file = UploadedFile::fake()->createWithContent(
            'payment.10hf',
            $this->encryptPaymentPayload(json_encode($payload, JSON_THROW_ON_ERROR))
        );

        $response = $this->actingAs($user)->postJson(route('payments.preview'), [
            'course_id' => $course->id,
            'payment_file' => $file,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('payment.amount', 0.04)
            ->assertJsonPath('payment.method', 'Tarjeta')
            ->assertJsonPath('payment.status', 'paid')
            ->assertJsonPath('payment.reference', 'REF-PREVIEW')
            ->assertJsonPath('payment.unica', 'encrypted-preview-test');
    }

    private function encryptPaymentPayload(string $payload): string
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encryptedPayload = openssl_encrypt($payload, 'aes-256-cbc', 'Encr10h-.$=2023SecretoMuajaaja', 0, $iv);

        return base64_encode($iv . $encryptedPayload);
    }
}
