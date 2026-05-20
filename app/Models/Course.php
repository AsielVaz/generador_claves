<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'start_date',
        'end_date',
        'payment_start_date',
        'payment_end_date',
        'minimum_payment',
        'course_cost',
        'price',
        'duration_hours',
        'is_active',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'minimum_payment' => 'decimal:2',
            'course_cost' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'payment_start_date' => 'date',
            'payment_end_date' => 'date',
            'is_active' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('enrolled_at')
            ->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
