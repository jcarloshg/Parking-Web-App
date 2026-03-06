<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ParkingSpace;
use App\Models\Ticket;
use App\Models\Payment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private array $plates = [
        'ABC-1234', 'XYZ-5678', 'DEF-9012', 'GHI-3456', 'JKL-7890',
        'MNO-1234', 'PQR-5678', 'STU-9012', 'VWX-3456', 'YZA-7890',
        'BCD-2345', 'EFG-6789', 'HIJ-0123', 'KLM-4567', 'NOP-8901',
        'QRS-2345', 'TUV-6789', 'WXY-0123', 'ZAB-4567', 'CDE-8901',
    ];

    private array $vehicleTypes = ['auto', 'moto', 'camioneta'];

    private array $paymentMethods = ['efectivo', 'tarjeta'];

    private array $rates = [
        'auto' => ['hourly' => 20, 'daily' => 150],
        'moto' => ['hourly' => 10, 'daily' => 80],
        'camioneta' => ['hourly' => 30, 'daily' => 200],
    ];

    public function run(bool $includeHistoricalData = false): void
    {
        $this->seedUsers();
        $this->seedParkingSpaces();

        if ($includeHistoricalData || env('SEED_HISTORICAL_DATA', false)) {
            $this->seedJanuary2026();
            $this->seedFebruary2026();
            $this->seedMarch2026();
        }
    }

    private function seedUsers(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@parking.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Cajero Principal',
            'email' => 'cajero@parking.com',
            'password' => bcrypt('password'),
            'role' => 'cajero',
        ]);

        User::create([
            'name' => 'Supervisor',
            'email' => 'supervisor@parking.com',
            'password' => bcrypt('password'),
            'role' => 'supervisor',
        ]);
    }

    private function seedParkingSpaces(): void
    {
        $spaces = [];

        $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        foreach ($rows as $row) {
            for ($i = 1; $i <= 10; $i++) {
                $spaces[] = ['number' => $row . $i, 'type' => 'general', 'status' => 'disponible'];
            }
        }

        for ($i = 1; $i <= 5; $i++) {
            $spaces[] = ['number' => 'ELEC-' . $i, 'type' => 'eléctrico', 'status' => 'disponible'];
        }

        for ($i = 1; $i <= 5; $i++) {
            $spaces[] = ['number' => 'DISC-' . $i, 'type' => 'discapacitado', 'status' => 'disponible'];
        }

        foreach ($spaces as $space) {
            ParkingSpace::create($space);
        }
    }

    public function seedJanuary2026(?int $days = null): void
    {
        $this->seedMonth(2026, 1, 'January', $days);
    }

    public function seedFebruary2026(?int $days = null): void
    {
        $this->seedMonth(2026, 2, 'February', $days);
    }

    public function seedMarch2026(?int $days = null): void
    {
        $this->seedMonth(2026, 3, 'March', $days);
    }

    private function seedMonth(int $year, int $month, string $monthName, ?int $days = null): void
    {
        $users = User::whereIn('role', ['admin', 'cajero', 'supervisor'])->get();
        $spaces = ParkingSpace::all();

        if ($users->isEmpty() || $spaces->isEmpty()) {
            if ($this->command) {
                $this->command->error('Run seedUsers and seedParkingSpaces first.');
            } else {
                echo "Run seedUsers and seedParkingSpaces first.\n";
            }
            return;
        }

        if ($this->command) {
            $this->command->info("Seeding $monthName $year data...");
        }

        $daysInMonth = $days ?? Carbon::create($year, $month)->daysInMonth;
        $endDate = Carbon::create($year, $month, $daysInMonth, 23, 59, 59);

        for ($day = 1; $day <= $daysInMonth; $day++) {
            if ($this->command && $day % 7 === 0) {
                $this->command->info("  Processing day $day/$daysInMonth...");
            }

            $currentDate = Carbon::create($year, $month, $day);

            foreach ($spaces as $space) {
                $space->status = 'disponible';
                $space->save();
            }

            $ticketsPerDay = rand(20, 50);

            for ($i = 0; $i < $ticketsPerDay; $i++) {
                $entryHour = rand(6, 22);
                $entryMinute = rand(0, 59);
                $entryTime = $currentDate->copy()->setTime($entryHour, $entryMinute, 0);

                $stayHours = rand(1, 12);
                if (rand(1, 100) <= 10) {
                    $stayHours = rand(24, 48);
                }

                $exitTime = $entryTime->copy()->addHours($stayHours);

                if ($exitTime->gt($endDate)) {
                    continue;
                }

                $vehicleType = $this->vehicleTypes[array_rand($this->vehicleTypes)];
                $space = $spaces->random();

                $plate = $this->plates[array_rand($this->plates)] . '-' . rand(10, 99);

                $ticket = Ticket::create([
                    'plate_number' => $plate,
                    'vehicle_type' => $vehicleType,
                    'entry_time' => $entryTime,
                    'exit_time' => $exitTime,
                    'parking_space_id' => $space->id,
                    'status' => 'finalizado',
                    'user_id' => $users->random()->id,
                ]);

                $total = $this->calculateFee($vehicleType, $entryTime, $exitTime);

                Payment::create([
                    'ticket_id' => $ticket->id,
                    'total' => $total,
                    'payment_method' => $this->paymentMethods[array_rand($this->paymentMethods)],
                    'paid_at' => $exitTime,
                    'user_id' => $users->random()->id,
                ]);

                $space->status = 'disponible';
                $space->save();
            }

            $activeTickets = rand(5, 15);
            for ($i = 0; $i < $activeTickets; $i++) {
                $availableSpaces = $spaces->where('status', 'disponible');
                if ($availableSpaces->isEmpty()) {
                    break;
                }

                $entryHour = rand(6, 22);
                $entryMinute = rand(0, 59);
                $entryTime = $currentDate->copy()->setTime($entryHour, $entryMinute, 0);

                $vehicleType = $this->vehicleTypes[array_rand($this->vehicleTypes)];
                $space = $availableSpaces->random();

                $plate = $this->plates[array_rand($this->plates)] . '-' . rand(10, 99);

                Ticket::create([
                    'plate_number' => $plate,
                    'vehicle_type' => $vehicleType,
                    'entry_time' => $entryTime,
                    'exit_time' => null,
                    'parking_space_id' => $space->id,
                    'status' => 'activo',
                    'user_id' => $users->random()->id,
                ]);

                $space->status = 'ocupado';
                $space->save();
            }
        }

        $ticketCount = Ticket::count();
        $paymentCount = Payment::count();

        if ($this->command) {
            $this->command->info("$monthName $year data seeded successfully! ($ticketCount tickets, $paymentCount payments)");
        } else {
            echo "$monthName $year data seeded successfully! ($ticketCount tickets, $paymentCount payments)\n";
        }
    }

    private function calculateFee(string $vehicleType, Carbon $entryTime, Carbon $exitTime): float
    {
        $hours = $entryTime->diffInMinutes($exitTime) / 60;

        if ($hours <= 10 / 60) {
            return 0;
        }

        if ($hours >= 24) {
            $days = ceil($hours / 24);
            return $days * $this->rates[$vehicleType]['daily'];
        }

        $completeHours = ceil($hours);
        return $completeHours * $this->rates[$vehicleType]['hourly'];
    }
}
