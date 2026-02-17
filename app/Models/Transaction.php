<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'amount',
        'currency',
        'card_number_masked',
        'card_holder',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'processed_at' => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
