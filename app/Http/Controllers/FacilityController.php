<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        $query = Facility::with('facilityType')
            ->where('is_active', true);

        if ($request->filled('type')) {
            $query->whereHas('facilityType', function ($q) use ($request) {
                $q->where('name', $request->type);
            });
        }

        if ($request->filled('capacity_min')) {
            $query->where('capacity', '>=', (int) $request->capacity_min);
        }

        return response()->json([
            'data' => $query->orderBy('name')->get(),
        ]);
    }

    public function show(Facility $facility)
    {
        return response()->json([
            'data' => $facility->load('facilityType', 'operatingHours', 'blackoutDates'),
        ]);
    }

    public function availability(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after:start_time',
        ]);

        $available = DB::selectOne(
            'SELECT is_facility_available(?, ?::timestamptz, ?::timestamptz) AS available',
            [$facility->id, $validated['start_time'], $validated['end_time']]
        );

        return response()->json([
            'facility_id' => $facility->id,
            'facility_name' => $facility->name,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'available' => (bool) $available->available,
        ]);
    }

    /**
     * CREATE — mass assignment through Facility::$fillable, validated at the boundary.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:150|unique:facilities,name',
            'facility_type_id' => 'required|integer|exists:facility_types,id',
            'location'         => 'required|string|max:200',
            'capacity'         => 'required|integer|min:1',
            'notes'            => 'nullable|string',
            'is_active'        => 'sometimes|boolean',
        ]);

        $facility = Facility::create($validated);

        return response()->json([
            'message' => 'Facility created successfully.',
            'data'    => $facility->load('facilityType'),
        ], 201);
    }

    /**
     * UPDATE
     */
    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'name'             => 'sometimes|required|string|max:150|unique:facilities,name,' . $facility->id,
            'facility_type_id' => 'sometimes|required|integer|exists:facility_types,id',
            'location'         => 'sometimes|required|string|max:200',
            'capacity'         => 'sometimes|required|integer|min:1',
            'notes'            => 'nullable|string',
            'is_active'        => 'sometimes|boolean',
        ]);

        $facility->update($validated);

        return response()->json([
            'message' => 'Facility updated successfully.',
            'data'    => $facility->fresh()->load('facilityType'),
        ]);
    }

    /**
     * DELETE
     */
    public function destroy(Facility $facility)
    {
        try {
            $facility->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Cannot delete: facility still has related bookings (FK ON DELETE RESTRICT).',
            ], 409);
        }

        return response()->json(['message' => 'Facility deleted successfully.']);
    }
}
