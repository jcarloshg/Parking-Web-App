<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        $tickets = Ticket::whereDate('entry_time', $date)->get();
        
        $revenue = Payment::whereDate('payment_time', $date)
            ->sum('amount');

        $completed = $tickets->where('status', 'completed')->count();
        $active = $tickets->where('status', 'active')->count();

        return response()->json([
            'date' => $date,
            'total_tickets' => $tickets->count(),
            'completed_tickets' => $completed,
            'active_tickets' => $active,
            'total_revenue' => $revenue,
        ]);
    }

    public function monthly(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $startDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $tickets = Ticket::whereBetween('entry_time', [$startDate, $endDate])->get();
        
        $revenue = Payment::whereBetween('payment_time', [$startDate, $endDate])
            ->sum('amount');

        $dailyRevenue = Payment::whereBetween('payment_time', [$startDate, $endDate])
            ->select(DB::raw('DATE(payment_time) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->get();

        return response()->json([
            'year' => $year,
            'month' => $month,
            'total_tickets' => $tickets->count(),
            'total_revenue' => $revenue,
            'daily_revenue' => $dailyRevenue,
        ]);
    }
}
