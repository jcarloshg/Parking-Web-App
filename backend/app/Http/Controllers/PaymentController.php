<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Ticket;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $payments = $this->paymentService->getAll($perPage);
        
        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'payment_method' => 'required|in:efectivo,tarjeta',
        ]);

        $user = $request->user();
        $payment = $this->paymentService->processPayment(
            $validated['ticket_id'],
            $validated['payment_method'],
            $user->id
        );

        return response()->json(['data' => $payment], 201);
    }

    public function show(Payment $payment)
    {
        $payment = $this->paymentService->getById($payment->id);
        return response()->json(['data' => $payment]);
    }

    public function today(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $payments = $this->paymentService->getToday($perPage);
        
        return response()->json($payments);
    }

    public function calculate(Ticket $ticket)
    {
        try {
            $feeData = $this->paymentService->calculateFee($ticket);
            return response()->json(['data' => $feeData]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
