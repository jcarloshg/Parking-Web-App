<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ParkingSpace;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@parking.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $attendant = User::create([
            'name' => 'Cajero',
            'email' => 'attendant@parking.com',
            'password' => bcrypt('password'),
            'role' => 'cajero',
            'is_active' => true,
        ]);

        $spaces = [
            ['number' => 'A1', 'type' => 'general'],
            ['number' => 'A2', 'type' => 'general'],
            ['number' => 'A3', 'type' => 'general'],
            ['number' => 'A4', 'type' => 'general'],
            ['number' => 'B1', 'type' => 'general'],
            ['number' => 'B2', 'type' => 'general'],
            ['number' => 'E1', 'type' => 'eléctrico'],
            ['number' => 'E2', 'type' => 'eléctrico'],
            ['number' => 'D1', 'type' => 'discapacitado'],
        ];

        foreach ($spaces as $space) {
            ParkingSpace::create($space);
        }
    }
}
