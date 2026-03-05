<?php

namespace App\Http\Controllers;

use App\Models\ParkingSpace;
use App\Models\Ticket;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index()
    {
        return response()->json(Ticket::with(['parkingSpace', 'user'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parking_space_id' => 'required|exists:parking_spaces,id',
            'vehicle_plate' => 'required|string|max:20',
            'vehicle_model' => 'nullable|string|max:50',
            'vehicle_color' => 'nullable|string|max:30',
        ]);

        $space = ParkingSpace::findOrFail($validated['parking_space_id']);
        
        if ($space->status !== 'available') {
            return response()->json(['error' => 'Parking space is not available'], 400);
        }

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-' . strtoupper(Str::random(8)),
            'parking_space_id' => $validated['parking_space_id'],
            'vehicle_plate' => strtoupper($validated['vehicle_plate']),
            'vehicle_model' => $validated['vehicle_model'] ?? null,
            'vehicle_color' => $validated['vehicle_color'] ?? null,
            'entry_time' => now(),
            'status' => 'active',
            'user_id' => Auth::id(),
        ]);

        $space->update(['status' => 'occupied']);

        return response()->json($ticket, 201);
    }

    public function show(Ticket $ticket)
    {
        return response()->json($ticket->load(['parkingSpace', 'user', 'payments']));
    }

    public function active()
    {
        $tickets = Ticket::with('parkingSpace')
            ->where('status', 'active')
            ->get();
        return response()->json($tickets);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $tickets = Ticket::with('parkingSpace')
            ->where('vehicle_plate', 'like', "%{$query}%")
            ->orWhere('ticket_number', 'like', "%{$query}%")
            ->get();

        return response()->json($tickets);
    }

    public function calculate(Ticket $ticket)
    {
        $hours = now()->diffInMinutes($ticket->entry_time) / 60;
        $hours = max(1, ceil($hours));
        $rate = $ticket->parkingSpace->hourly_rate;
        $total = $hours * $rate;

        return response()->json([
            'hours' => $hours,
            'hourly_rate' => $rate,
            'total_amount' => $total,
        ]);
    }

    public function checkout(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'method' => 'required|in:cash,card,qr',
        ]);

        $hours = now()->diffInMinutes($ticket->entry_time) / 60;
        $hours = max(1, ceil($hours));
        $rate = $ticket->parkingSpace->hourly_rate;
        $total = $hours * $rate;

        $ticket->update([
            'exit_time' => now(),
            'status' => 'completed',
            'total_amount' => $total,
        ]);

        $ticket->parkingSpace->update(['status' => 'available']);

        $payment = Payment::create([
            'ticket_id' => $ticket->id,
            'amount' => $total,
            'method' => $validated['method'],
            'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
            'payment_time' => now(),
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'ticket' => $ticket->fresh(),
            'payment' => $payment,
        ]);
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return response()->json(null, 204);
    }
}
