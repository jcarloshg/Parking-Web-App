<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'parking_space_id',
        'vehicle_plate',
        'vehicle_model',
        'vehicle_color',
        'entry_time',
        'exit_time',
        'status',
        'total_amount',
        'user_id',
    ];

    protected $casts = [
        'entry_time' => 'datetime',
        'exit_time' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function parkingSpace()
    {
        return $this->belongsTo(ParkingSpace::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
