<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private FeeCalculator $feeCalculator
    ) {}

    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Payment::with(['ticket', 'user'])->paginate($perPage);
    }

    public function getById(int $id): ?Payment
    {
        return Payment::with(['ticket', 'user'])->find($id);
    }

    public function getToday(int $perPage = 15): LengthAwarePaginator
    {
        return Payment::with(['ticket', 'user'])
            ->whereDate('paid_at', today())
            ->paginate($perPage);
    }

    public function calculateFee(Ticket $ticket): array
    {
        if ($ticket->status !== 'activo') {
            throw new \InvalidArgumentException('El ticket no está activo');
        }

        return $this->feeCalculator->calculateForTicket($ticket);
    }

    public function processPayment(int $ticketId, string $paymentMethod, int $userId): Payment
    {
        $ticket = Ticket::with('parkingSpace')->findOrFail($ticketId);

        if ($ticket->status !== 'activo') {
            throw new \InvalidArgumentException('El ticket ya está finalizado');
        }

        if ($ticket->payment) {
            throw new \InvalidArgumentException('El ticket ya tiene un pago registrado');
        }

        return DB::transaction(function () use ($ticket, $paymentMethod, $userId) {
            $feeData = $this->calculateFee($ticket);

            $ticket->update([
                'exit_time' => now(),
                'status' => 'finalizado',
            ]);

            $payment = Payment::create([
                'ticket_id' => $ticket->id,
                'total' => $feeData['total'],
                'payment_method' => $paymentMethod,
                'paid_at' => now(),
                'user_id' => $userId,
            ]);

            if ($ticket->parkingSpace) {
                $ticket->parkingSpace->update(['status' => 'disponible']);
            }

            return $payment->fresh(['ticket', 'user']);
        });
    }

    public function searchByPlate(string $plate): ?Payment
    {
        $ticket = Ticket::where('plate_number', 'like', "%{$plate}%")
            ->where('status', 'finalizado')
            ->latest()
            ->first();

        if (!$ticket) {
            return null;
        }

        return $ticket->payment;
    }
}
