<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'total',
        'payment_method',
        'user_id',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCash(): bool
    {
        return $this->payment_method === 'efectivo';
    }

    public function isCard(): bool
    {
        return $this->payment_method === 'tarjeta';
    }
}
