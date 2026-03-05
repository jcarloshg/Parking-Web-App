<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\ParkingSpace;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getDailyReport(?string $date = null): array
    {
        $date = $date ?? now()->toDateString();

        $payments = Payment::whereDate('paid_at', $date)->get();
        $totalIngresos = $payments->sum('total');
        $ticketsAtendidos = $payments->count();
        $promedioPorTicket = $ticketsAtendidos > 0 ? $totalIngresos / $ticketsAtendidos : 0;

        $cajonesDisponibles = ParkingSpace::where('status', 'disponible')->count();
        $ticketsActivos = Ticket::where('status', 'activo')->count();

        return [
            'total_ingresos' => $totalIngresos,
            'tickets_atendidos' => $ticketsAtendidos,
            'promedio_por_ticket' => round($promedioPorTicket, 2),
            'cajones_disponibles' => $cajonesDisponibles,
            'tickets_activos' => $ticketsActivos,
            'fecha' => $date,
        ];
    }

    public function getMonthlyReport(?int $year = null, ?int $month = null): array
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $startDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $payments = Payment::whereBetween('paid_at', [$startDate, $endDate])->get();
        $tickets = Ticket::whereBetween('entry_time', [$startDate, $endDate])->get();

        $ingresosPorDia = Payment::whereBetween('paid_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(paid_at) as dia'), DB::raw('SUM(total) as total'))
            ->groupBy('dia')
            ->orderBy('dia')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->dia => $item->total]);

        $horaPica = Ticket::whereBetween('entry_time', [$startDate, $endDate])
            ->select(DB::raw('HOUR(entry_time) as hora'), DB::raw('COUNT(*) as count'))
            ->groupBy('hora')
            ->orderByDesc('count')
            ->first();

        $tipoVehiculoFrecuente = Ticket::whereBetween('entry_time', [$startDate, $endDate])
            ->select('vehicle_type', DB::raw('COUNT(*) as count'))
            ->groupBy('vehicle_type')
            ->orderByDesc('count')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->vehicle_type => $item->count]);

        return [
            'ingresos_por_día' => $ingresosPorDia,
            'hora_pica' => $horaPica ? $horaPica->hora : null,
            'tipo_vehiculo_frecuente' => $tipoVehiculoFrecuente,
            'total_ingresos_mes' => $payments->sum('total'),
            'total_tickets_mes' => $tickets->count(),
            'año' => $year,
            'mes' => $month,
        ];
    }

    public function getDashboardSummary(): array
    {
        $cajonesDisponibles = ParkingSpace::where('status', 'disponible')->count();
        $ingresosDia = Payment::whereDate('paid_at', today())->sum('total');
        $ticketsActivos = Ticket::where('status', 'activo')->count();
        
        $ultimosTickets = Ticket::with(['parkingSpace', 'user'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return [
            'cajones_disponibles' => $cajonesDisponibles,
            'ingresos_dia' => $ingresosDia,
            'tickets_activos' => $ticketsActivos,
            'ultimos_tickets' => $ultimosTickets,
        ];
    }

    public function canAccessReports($user): bool
    {
        return in_array($user->role, ['admin', 'supervisor']);
    }
}
