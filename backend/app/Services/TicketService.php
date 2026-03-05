<?php

namespace App\Services;

use App\Models\ParkingSpace;
use App\Models\Ticket;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Ticket::with(['parkingSpace', 'user'])->paginate($perPage);
    }

    public function getById(int $id): ?Ticket
    {
        return Ticket::with(['parkingSpace', 'user', 'payment'])->find($id);
    }

    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return Ticket::active()
            ->with(['parkingSpace', 'user'])
            ->paginate($perPage);
    }

    public function searchByPlate(string $plate, int $perPage = 15): LengthAwarePaginator
    {
        return Ticket::where('plate_number', 'like', "%{$plate}%")
            ->with(['parkingSpace', 'user'])
            ->paginate($perPage);
    }

    public function create(array $data, int $userId): Ticket
    {
        return DB::transaction(function () use ($data, $userId) {
            $parkingSpace = ParkingSpace::findOrFail($data['parking_space_id']);

            if ($parkingSpace->status !== 'disponible') {
                throw new \InvalidArgumentException('El espacio de estacionamiento no está disponible');
            }

            $ticket = Ticket::create([
                'plate_number' => strtoupper($data['plate_number']),
                'vehicle_type' => $data['vehicle_type'],
                'entry_time' => now(),
                'parking_space_id' => $data['parking_space_id'],
                'status' => 'activo',
                'user_id' => $userId,
            ]);

            $parkingSpace->update(['status' => 'ocupado']);

            return $ticket->fresh(['parkingSpace', 'user']);
        });
    }

    public function findAvailableSpace(?string $vehicleType = null): ?ParkingSpace
    {
        $query = ParkingSpace::available();

        if ($vehicleType) {
            if ($vehicleType === 'moto') {
                $space = $query->where('type', 'general')->first();
                if (!$space) {
                    $space = $query->first();
                }
            } else {
                $space = $query->first();
            }
        } else {
            $space = $query->first();
        }

        return $space;
    }

    public function calculateFee(Ticket $ticket): array
    {
        $entryTime = $ticket->entry_time;
        $now = now();
        
        $minutes = $entryTime->diffInMinutes($now);
        $hours = ceil($minutes / 60);
        
        $rates = [
            'auto' => 20,
            'moto' => 10,
            'camioneta' => 30,
        ];
        
        $rate = $rates[$ticket->vehicle_type] ?? 20;
        $total = $hours * $rate;
        
        return [
            'entry_time' => $entryTime,
            'exit_time' => $now,
            'hours' => $hours,
            'minutes' => $minutes,
            'rate_per_hour' => $rate,
            'total' => $total,
        ];
    }

    public function checkout(Ticket $ticket, string $paymentMethod): Ticket
    {
        if ($ticket->status !== 'activo') {
            throw new \InvalidArgumentException('El ticket no está activo');
        }

        return DB::transaction(function () use ($ticket, $paymentMethod) {
            $feeData = $this->calculateFee($ticket);

            $ticket->update([
                'exit_time' => $feeData['exit_time'],
                'status' => 'finalizado',
            ]);

            $ticket->payment()->create([
                'total' => $feeData['total'],
                'payment_method' => $paymentMethod,
                'paid_at' => now(),
                'user_id' => $ticket->user_id,
            ]);

            $ticket->parkingSpace->update(['status' => 'disponible']);

            return $ticket->fresh(['parkingSpace', 'user', 'payment']);
        });
    }
}
