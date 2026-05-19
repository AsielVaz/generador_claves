<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const TYPE_WALLET_CREDIT = 'wallet_credit';
    public const TYPE_COURSE_PAYMENT = 'course_payment';
    public const TYPE_CONDONATION = 'condonation';

    protected $fillable = [
        'user_id',
        'course_id',
        'type',
        'amount',
        'method',
        'status',
        'reference',
        'unica',
        'is_condoned',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_condoned' => 'boolean',
            'paid_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
