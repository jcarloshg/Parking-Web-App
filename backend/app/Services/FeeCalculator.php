<?php

namespace App\Services;

use Carbon\Carbon;

class FeeCalculator
{
    private const RATES = [
        'auto' => ['hourly' => 20, 'daily' => 150],
        'moto' => ['hourly' => 10, 'daily' => 80],
        'camioneta' => ['hourly' => 30, 'daily' => 200],
    ];

    private const TOLERANCE_MINUTES = 10;

    public function calculate(string $vehicleType, Carbon $entryTime): array
    {
        $now = Carbon::now();
        $minutes = $entryTime->diffInMinutes($now);

        if ($minutes <= self::TOLERANCE_MINUTES) {
            return [
                'total' => 0,
                'hours' => 0,
                'minutes' => $minutes,
                'rate_type' => 'tolerance',
                'rate_per_hour' => self::RATES[$vehicleType]['hourly'] ?? 20,
                'daily_rate' => self::RATES[$vehicleType]['daily'] ?? 150,
            ];
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes > 0) {
            $hours += 1;
        }

        $rates = self::RATES[$vehicleType] ?? self::RATES['auto'];
        
        if ($hours >= 24) {
            $days = floor($hours / 24);
            $remainingHours = $hours % 24;
            
            $total = ($days * $rates['daily']) + ($remainingHours * $rates['hourly']);
            
            return [
                'total' => $total,
                'hours' => $hours,
                'days' => $days,
                'remaining_hours' => $remainingHours,
                'minutes' => $minutes,
                'rate_type' => 'mixed',
                'rate_per_hour' => $rates['hourly'],
                'daily_rate' => $rates['daily'],
            ];
        }

        return [
            'total' => $hours * $rates['hourly'],
            'hours' => $hours,
            'minutes' => $minutes,
            'rate_type' => 'hourly',
            'rate_per_hour' => $rates['hourly'],
            'daily_rate' => $rates['daily'],
        ];
    }

    public function calculateForTicket($ticket): array
    {
        return $this->calculate($ticket->vehicle_type, $ticket->entry_time);
    }

    public function getHourlyRate(string $vehicleType): int
    {
        return self::RATES[$vehicleType]['hourly'] ?? 20;
    }

    public function getDailyRate(string $vehicleType): int
    {
        return self::RATES[$vehicleType]['daily'] ?? 150;
    }

    public function getToleranceMinutes(): int
    {
        return self::TOLERANCE_MINUTES;
    }
}
