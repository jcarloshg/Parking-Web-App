<?php

namespace App\Services;

use App\Models\ParkingSpace;
use Illuminate\Pagination\LengthAwarePaginator;

class ParkingSpaceService
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return ParkingSpace::paginate($perPage);
    }

    public function getAvailable(int $perPage = 15): LengthAwarePaginator
    {
        return ParkingSpace::available()->paginate($perPage);
    }

    public function getAvailableCount(): int
    {
        return ParkingSpace::available()->count();
    }

    public function getById(int $id): ?ParkingSpace
    {
        return ParkingSpace::find($id);
    }

    public function create(array $data): ParkingSpace
    {
        $data['status'] = $data['status'] ?? 'disponible';
        
        return ParkingSpace::create($data);
    }

    public function update(ParkingSpace $parkingSpace, array $data): ParkingSpace
    {
        $parkingSpace->update($data);
        return $parkingSpace->fresh();
    }

    public function delete(ParkingSpace $parkingSpace): void
    {
        $parkingSpace->delete();
    }

    public function setStatus(ParkingSpace $parkingSpace, string $status): ParkingSpace
    {
        $parkingSpace->update(['status' => $status]);
        return $parkingSpace->fresh();
    }

    public function isAvailable(ParkingSpace $parkingSpace): bool
    {
        return $parkingSpace->status === 'disponible';
    }
}
