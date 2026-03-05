<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    public function daily(Request $request)
    {
        $user = $request->user();
        
        if (!$this->reportService->canAccessReports($user)) {
            return response()->json(['error' => 'No tienes permiso para ver reportes'], 403);
        }

        $date = $request->get('date', now()->toDateString());
        $report = $this->reportService->getDailyReport($date);

        return response()->json($report);
    }

    public function monthly(Request $request)
    {
        $user = $request->user();
        
        if (!$this->reportService->canAccessReports($user)) {
            return response()->json(['error' => 'No tienes permiso para ver reportes'], 403);
        }

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $report = $this->reportService->getMonthlyReport($year, $month);

        return response()->json($report);
    }

    public function summary()
    {
        $report = $this->reportService->getDashboardSummary();

        return response()->json($report);
    }
}
