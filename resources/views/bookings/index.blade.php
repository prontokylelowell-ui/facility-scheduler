@extends('layouts.app')

@section('title', 'Bookings — Resort Booking')

@section('content')
    <h1>
        Bookings <span class="muted">(core entity #2 — full CRUD)</span>
        @if (Auth::user()->isAdmin())
            <span class="role-tag">showing all users' bookings (app_admin)</span>
        @else
            <span class="role-tag">showing only your bookings (RLS)</span>
        @endif
    </h1>

    <div class="card">
        <h2>Request a booking</h2>
        <form action="{{ route('bookings.store') }}" method="POST">
            @csrf

            <div class="row">
                <div>
                    <label for="facility_id">Facility</label>
                    <select id="facility_id" name="facility_id" required>
                        <option value="">— select —</option>
                        @foreach ($facilities as $facility)
                            <option value="{{ $facility->id }}" @selected(old('facility_id') == $facility->id)>
                                {{ $facility->name }} ({{ $facility->facilityType->name ?? '—' }}, cap {{ $facility->capacity }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="purpose">Purpose</label>
                    <input id="purpose" type="text" name="purpose" value="{{ old('purpose') }}" required>
                </div>
            </div>

            <div class="row">
                <div>
                    <label for="start_time">Start time</label>
                    <input id="start_time" type="datetime-local" name="start_time" value="{{ old('start_time') }}" required>
                </div>
                <div>
                    <label for="end_time">End time</label>
                    <input id="end_time" type="datetime-local" name="end_time" value="{{ old('end_time') }}" required>
                </div>
            </div>

            <div style="margin-top:16px;">
                <button type="submit">Submit booking</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Booking list</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Facility</th>
                    @if (Auth::user()->isAdmin())
                        <th>Requested by</th>
                    @endif
                    <th>Start</th>
                    <th>End</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    <tr>
                        <td>{{ $booking->id }}</td>
                        <td>{{ $booking->facility->name ?? '—' }}</td>
                        @if (Auth::user()->isAdmin())
                            <td>{{ $booking->user->full_name ?? '—' }}</td>
                        @endif
                        <td>{{ $booking->start_time?->format('Y-m-d H:i') }}</td>
                        <td>{{ $booking->end_time?->format('Y-m-d H:i') }}</td>
                        <td>{{ $booking->purpose }}</td>
                        <td>
                            <span class="badge badge-{{ $booking->status }}">{{ $booking->status }}</span>
                            @if ($booking->admin_remarks)
                                <div class="muted">{{ $booking->admin_remarks }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="actions">
                                @if (Auth::user()->isAdmin() && $booking->status === 'pending')
                                    <form action="{{ route('bookings.approve', $booking) }}" method="POST">
                                        @csrf
                                        <button class="btn-sm" type="submit">Approve</button>
                                    </form>

                                    <form action="{{ route('bookings.reject', $booking) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="remarks" value="Rejected by admin.">
                                        <button class="danger btn-sm" type="submit">Reject</button>
                                    </form>
                                @endif

                                @if (in_array($booking->status, ['pending', 'approved'], true))
                                    <form action="{{ route('bookings.cancel', $booking) }}" method="POST">
                                        @csrf
                                        <button class="ghost btn-sm" type="submit">Cancel</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="muted">No bookings yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">{{ $bookings->links() }}</div>
    </div>
@endsection
