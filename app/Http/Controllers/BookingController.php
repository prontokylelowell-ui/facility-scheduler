<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Booking::with(['user', 'facility.facilityType', 'statusLogs']);

        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        } else {
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        }

        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->facility_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->where('start_time', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('start_time', '<=', $request->to_date);
        }

        return response()->json([
            'data' => $query->orderByDesc('start_time')->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'start_time'  => 'required|date',
            'end_time'    => 'required|date|after:start_time',
            'purpose'     => 'required|string|max:255',
        ]);

        $facility = Facility::findOrFail($validated['facility_id']);

        $isAvailable = $facility->isAvailable(
            $validated['start_time'],
            $validated['end_time']
        );

        if (!$isAvailable) {
            return response()->json([
                'message' => 'The requested time slot conflicts with an existing pending/approved booking or falls on a blackout date.',
                'errors' => ['facility_id' => ['Facility is not available at the requested time.']],
            ], 422);
        }

        $booking = Booking::create([
            'user_id'     => Auth::id(),
            'facility_id' => $validated['facility_id'],
            'start_time'  => $validated['start_time'],
            'end_time'    => $validated['end_time'],
            'purpose'     => $validated['purpose'],
            'status'      => Booking::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => 'Booking created successfully. Status: pending.',
            'data'    => $booking->load('facility.facilityType'),
        ], 201);
    }

    public function show(Booking $booking)
    {
        if (!Auth::user()->isAdmin() && $booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to view this booking.');
        }

        return response()->json([
            'data' => $booking->load(['user', 'facility.facilityType', 'statusLogs.changedBy']),
        ]);
    }

    public function approve(Request $request, Booking $booking)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only admins can approve bookings.');
        }

        if ($booking->status !== Booking::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending bookings can be approved.',
            ], 422);
        }

        $request->validate([
            'remarks' => 'nullable|string|max:255',
        ]);

        $start = microtime(true);
        try {
            DB::transaction(function () use ($booking, $request) {
                $booking->approve($request->remarks);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Approval failed. Possible schedule conflict.',
                'error' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Booking approved.',
            'data' => $booking->fresh(),
        ]);
    }

    public function reject(Request $request, Booking $booking)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only admins can reject bookings.');
        }

        if ($booking->status !== Booking::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending bookings can be rejected.',
            ], 422);
        }

        $request->validate([
            'remarks' => 'required|string|max:255',
        ]);

        $booking->reject($request->remarks);

        return response()->json([
            'message' => 'Booking rejected.',
            'data' => $booking->fresh(),
        ]);
    }

    public function cancel(Booking $booking)
    {
        if (!Auth::user()->isAdmin() && $booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to cancel this booking.');
        }

        if (!in_array($booking->status, [Booking::STATUS_PENDING, Booking::STATUS_APPROVED])) {
            return response()->json([
                'message' => 'Only pending or approved bookings can be cancelled.',
            ], 422);
        }

        $booking->cancel();

        return response()->json([
            'message' => 'Booking cancelled.',
            'data' => $booking->fresh(),
        ]);
    }
}
