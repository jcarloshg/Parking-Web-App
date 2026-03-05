<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    public function __construct(
        private TicketService $ticketService
    ) {}

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $tickets = $this->ticketService->getAll($perPage);
        
        return response()->json($tickets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|regex:/^[A-Z0-9-]+$/i|max:20',
            'vehicle_type' => 'required|in:auto,moto,camioneta',
            'parking_space_id' => 'required|exists:parking_spaces,id',
        ]);

        $user = $request->user();
        $ticket = $this->ticketService->create($validated, $user->id);

        return response()->json(['data' => $ticket], 201);
    }

    public function show(Ticket $ticket)
    {
        $ticket = $this->ticketService->getById($ticket->id);
        return response()->json(['data' => $ticket]);
    }

    public function active(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $tickets = $this->ticketService->getActive($perPage);
        
        return response()->json($tickets);
    }

    public function search(Request $request)
    {
        $request->validate([
            'plate' => 'required|string|min:1|max:20',
        ]);

        $perPage = $request->get('per_page', 15);
        $tickets = $this->ticketService->searchByPlate($request->plate, $perPage);
        
        return response()->json($tickets);
    }

    public function calculate(Ticket $ticket)
    {
        $ticket = $this->ticketService->getById($ticket->id);
        
        if (!$ticket) {
            return response()->json(['error' => 'Ticket no encontrado'], 404);
        }

        if ($ticket->status !== 'activo') {
            return response()->json(['error' => 'El ticket ya está finalizado'], 400);
        }

        $feeData = $this->ticketService->calculateFee($ticket);
        
        return response()->json(['data' => $feeData]);
    }

    public function checkout(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:efectivo,tarjeta',
        ]);

        $ticket = $this->ticketService->getById($ticket->id);
        
        if (!$ticket) {
            return response()->json(['error' => 'Ticket no encontrado'], 404);
        }

        if ($ticket->status !== 'activo') {
            return response()->json(['error' => 'El ticket ya está finalizado'], 400);
        }

        $ticket = $this->ticketService->checkout($ticket, $validated['payment_method']);
        
        return response()->json(['data' => $ticket]);
    }
}
