<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ParkingSpace extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'type',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'disponible');
    }

    public function scopeOccupied(Builder $query): Builder
    {
        return $query->where('status', 'ocupado');
    }

    public function scopeOutOfService(Builder $query): Builder
    {
        return $query->where('status', 'fuera_servicio');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'disponible';
    }

    public function isOccupied(): bool
    {
        return $this->status === 'ocupado';
    }
}
