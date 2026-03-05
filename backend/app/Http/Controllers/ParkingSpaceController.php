<?php

namespace App\Http\Controllers;

use App\Models\ParkingSpace;
use Illuminate\Http\Request;

class ParkingSpaceController extends Controller
{
    public function index()
    {
        return response()->json(ParkingSpace::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'space_number' => 'required|string|unique:parking_spaces,space_number',
            'type' => 'required|in:standard,compact,electric,disabled',
            'hourly_rate' => 'required|numeric|min:0',
        ]);

        $validated['status'] = 'available';
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
            'space_number' => 'sometimes|string|unique:parking_spaces,space_number,' . $parkingSpace->id,
            'type' => 'sometimes|in:standard,compact,electric,disabled',
            'status' => 'sometimes|in:available,occupied,maintenance',
            'hourly_rate' => 'sometimes|numeric|min:0',
        ]);

        $parkingSpace->update($validated);

        return response()->json($parkingSpace);
    }

    public function destroy(ParkingSpace $parkingSpace)
    {
        $parkingSpace->delete();
        return response()->json(null, 204);
    }

    public function availableCount()
    {
        $count = ParkingSpace::where('status', 'available')->count();
        return response()->json(['available_count' => $count]);
    }
}
