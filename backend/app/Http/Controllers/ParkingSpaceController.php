<?php

namespace App\Http\Controllers;

use App\Models\ParkingSpace;
use Illuminate\Http\Request;

class ParkingSpaceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $spaces = ParkingSpace::paginate($perPage);
        
        return response()->json($spaces);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|unique:parking_spaces,number|regex:/^[A-Z0-9-]+$/i',
            'type' => 'required|in:general,discapacitado,eléctrico',
            'status' => 'sometimes|in:disponible,ocupado,fuera_servicio',
        ]);

        if (!isset($validated['status'])) {
            $validated['status'] = 'disponible';
        }

        $parkingSpace = ParkingSpace::create($validated);

        return response()->json($parkingSpace, 201);
    }

    public function show(ParkingSpace $parkingSpace)
    {
        return response()->json($parkingSpace);
    }

    public function update(Request $request, ParkingSpace $parkingSpace)
    {
        $validated = $request->validate([
            'number' => 'sometimes|string|unique:parking_spaces,number,' . $parkingSpace->id . '|regex:/^[A-Z0-9-]+$/i',
            'type' => 'sometimes|in:general,discapacitado,eléctrico',
            'status' => 'sometimes|in:disponible,ocupado,fuera_servicio',
        ]);

        $parkingSpace->update($validated);

        return response()->json($parkingSpace);
    }

    public function destroy(ParkingSpace $parkingSpace)
    {
        $parkingSpace->delete();
        return response()->json(null, 204);
    }

    public function available(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $spaces = ParkingSpace::available()->paginate($perPage);
        
        return response()->json($spaces);
    }

    public function availableCount()
    {
        $count = ParkingSpace::where('status', 'disponible')->count();
        return response()->json(['available_count' => $count]);
    }
}
