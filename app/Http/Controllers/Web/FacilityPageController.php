<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\FacilityType;
use Illuminate\Http\Request;

/**
 * Blade CRUD for the Facilities entity (core entity #1).
 */
class FacilityPageController extends Controller
{
    public function index()
    {
        $facilities = Facility::with('facilityType')
            ->orderBy('name')
            ->paginate(10);

        return view('facilities.index', [
            'facilities'    => $facilities,
            'facilityTypes' => FacilityType::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:150|unique:facilities,name',
            'facility_type_id' => 'required|integer|exists:facility_types,id',
            'location'         => 'required|string|max:200',
            'capacity'         => 'required|integer|min:1',
            'notes'            => 'nullable|string',
        ]);

        $validated['is_active'] = true;

        Facility::create($validated);

        return redirect()->route('facilities.index')
            ->with('success', 'Facility "' . $validated['name'] . '" created.');
    }

    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:150|unique:facilities,name,' . $facility->id,
            'facility_type_id' => 'required|integer|exists:facility_types,id',
            'location'         => 'required|string|max:200',
            'capacity'         => 'required|integer|min:1',
            'notes'            => 'nullable|string',
        ]);

        $facility->update($validated);

        return redirect()->route('facilities.index')
            ->with('success', 'Facility "' . $facility->name . '" updated.');
    }

    public function destroy(Facility $facility)
    {
        try {
            $name = $facility->name;
            $facility->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('facilities.index')
                ->with('error', 'Cannot delete "' . $facility->name . '": related bookings still reference it (FK ON DELETE RESTRICT).');
        }

        return redirect()->route('facilities.index')
            ->with('success', 'Facility "' . $name . '" deleted.');
    }
}
