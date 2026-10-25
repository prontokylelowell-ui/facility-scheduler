<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Facility;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Blade CRUD for the Bookings entity (core entity #2).
 *
 * RLS note: because SetAppUserIdForRls has run, a plain Booking::all() here
 * would already be filtered by the PostgreSQL policy for app_user. We still
 * scope explicitly so the UI is correct even when connecting as a
 * non-RLS-enforced role (e.g. app_admin).
 */
class BookingPageController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['facility.facilityType', 'user', 'statusLogs']);

        if (! Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('status') && in_array($request->status, Booking::validStatuses(), true)) {
            $query->where('status', $request->status);
        }

        return view('bookings.index', [
            'bookings' => $query->orderByDesc('start_time')->paginate(10)->withQueryString(),
            'facilities' => Facility::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|integer|exists:facilities,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'purpose' => 'required|string|max:255',
        ]);

        $facility = Facility::findOrFail($validated['facility_id']);

        if (! $facility->isAvailable($validated['start_time'], $validated['end_time'])) {
            return back()
                ->withErrors(['facility_id' => 'That slot is already taken or falls on a blackout date.'])
                ->withInput();
        }

        try {
            Booking::create([
                'user_id' => Auth::id(),
                'facility_id' => $validated['facility_id'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'purpose' => $validated['purpose'],
                'status' => Booking::STATUS_PENDING,
            ]);
        } catch (QueryException $e) {
            // Postgres exclusion constraint (no_overlap) — the DB-level safety net.
            return back()
                ->withErrors(['facility_id' => 'The database rejected this booking: the slot overlaps an existing pending/approved booking.'])
                ->withInput();
        }

        return redirect()->route('bookings.index')
            ->with('success', 'Booking submitted. Status: pending.');
    }

    public function cancel(Booking $booking)
    {
        if (! Auth::user()->isAdmin() && $booking->user_id !== Auth::id()) {
            abort(403, 'You can only cancel your own bookings.');
        }

        if (! in_array($booking->status, [Booking::STATUS_PENDING, Booking::STATUS_APPROVED], true)) {
            return back()->with('error', 'Only pending or approved bookings can be cancelled.');
        }

        $booking->cancel();

        return redirect()->route('bookings.index')->with('success', 'Booking cancelled.');
    }

    public function approve(Request $request, Booking $booking)
    {
        if ($booking->status !== Booking::STATUS_PENDING) {
            return back()->with('error', 'Only pending bookings can be approved.');
        }

        $request->validate(['remarks' => 'nullable|string|max:255']);

        try {
            $booking->approve($request->remarks);
        } catch (QueryException $e) {
            return back()->with('error', 'Approval failed: an approved booking already occupies that slot.');
        }

        return redirect()->route('bookings.index')->with('success', 'Booking approved.');
    }

    public function reject(Request $request, Booking $booking)
    {
        if ($booking->status !== Booking::STATUS_PENDING) {
            return back()->with('error', 'Only pending bookings can be rejected.');
        }

        $validated = $request->validate(['remarks' => 'required|string|max:255']);

        $booking->reject($validated['remarks']);

        return redirect()->route('bookings.index')->with('success', 'Booking rejected.');
    }
}
