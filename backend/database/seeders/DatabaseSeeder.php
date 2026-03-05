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
            'name' => 'Administrador',
            'email' => 'admin@parking.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $cajero = User::create([
            'name' => 'Cajero Principal',
            'email' => 'cajero@parking.com',
            'password' => bcrypt('password'),
            'role' => 'cajero',
        ]);

        $supervisor = User::create([
            'name' => 'Supervisor',
            'email' => 'supervisor@parking.com',
            'password' => bcrypt('password'),
            'role' => 'supervisor',
        ]);

        $spaces = [
            ['number' => 'A1', 'type' => 'general', 'status' => 'disponible'],
            ['number' => 'A2', 'type' => 'general', 'status' => 'disponible'],
            ['number' => 'A3', 'type' => 'general', 'status' => 'disponible'],
            ['number' => 'A4', 'type' => 'general', 'status' => 'disponible'],
            ['number' => 'A5', 'type' => 'general', 'status' => 'disponible'],
            ['number' => 'B1', 'type' => 'general', 'status' => 'disponible'],
            ['number' => 'B2', 'type' => 'general', 'status' => 'disponible'],
            ['number' => 'B3', 'type' => 'general', 'status' => 'disponible'],
            ['number' => 'B4', 'type' => 'general', 'status' => 'disponible'],
            ['number' => 'E1', 'type' => 'eléctrico', 'status' => 'disponible'],
            ['number' => 'E2', 'type' => 'eléctrico', 'status' => 'disponible'],
            ['number' => 'D1', 'type' => 'discapacitado', 'status' => 'disponible'],
            ['number' => 'D2', 'type' => 'discapacitado', 'status' => 'disponible'],
        ];

        foreach ($spaces as $space) {
            ParkingSpace::create($space);
        }
    }
}
