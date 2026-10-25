@extends('layouts.app')

@section('title', 'Bookings — Resort Booking')

@section('content')
    <div style="margin-bottom: 30px;">
        @if(Auth::user()->isAdmin())
            <nav style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
                Administration / Bookings
            </nav>
            <h1>Admin Workspace</h1>
            <p class="section-desc">Manage guest bookings, approvals, and resort schedules.</p>
        @else
            <h1>My Bookings</h1>
            <p class="section-desc">Keep track of your upcoming resort visits and facility requests.</p>
        @endif
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
            <div>
                <h2 style="margin: 0;">Request a booking</h2>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 4px 0 0;">Fill in the details to reserve a resort facility.</p>
            </div>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 0.75rem; font-weight: 700; color: var(--primary); text-transform: uppercase;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                Reservation system
            </div>
        </div>

        <form action="{{ route('bookings.store') }}" method="POST">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label for="facility_id">Facility</label>
                    <select id="facility_id" name="facility_id" required>
                        <option value="">Select a facility</option>
                        @foreach ($facilities as $facility)
                            <option value="{{ $facility->id }}" @selected(old('facility_id') == $facility->id)>
                                {{ $facility->name }} ({{ $facility->facilityType->name ?? '—' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="purpose">Purpose of stay</label>
                    <input id="purpose" type="text" name="purpose" value="{{ old('purpose') }}" placeholder="e.g. Family vacation, Team building" required>
                </div>
                <div class="form-group">
                    <label for="start_time">Check-in time</label>
                    <input id="start_time" type="datetime-local" name="start_time" value="{{ old('start_time') }}" required>
                </div>
                <div class="form-group">
                    <label for="end_time">Check-out time</label>
                    <input id="end_time" type="datetime-local" name="end_time" value="{{ old('end_time') }}" required>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                <button type="submit">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Submit request
                </button>
            </div>
        </form>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <h2 style="margin: 0;">Booking history</h2>
                <span style="background: var(--primary-light); color: var(--primary); padding: 2px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                    {{ $bookings->total() }}
                </span>
            </div>
            <form method="GET" action="{{ route('bookings.index') }}">
                <select name="status" aria-label="Filter bookings by status" onchange="this.form.submit()" style="width: 160px; height: 38px; font-size: 0.85rem; padding: 0 12px;">
                    <option value="">All statuses</option>
                    @foreach (['pending', 'approved', 'rejected', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Facility</th>
                        @if (Auth::user()->isAdmin())
                            <th>Requested by</th>
                        @endif
                        <th>Schedule</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th style="width: 120px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td style="color: var(--text-muted); font-size: 0.8rem;">{{ $booking->id }}</td>
                            <td>
                                <div style="font-weight: 600;">{{ $booking->facility->name ?? '—' }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $booking->facility->facilityType->name ?? '—' }}</div>
                            </td>
                            @if (Auth::user()->isAdmin())
                                <td>
                                    <div style="font-weight: 500;">{{ $booking->user->full_name ?? '—' }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $booking->user->email }}</div>
                                </td>
                            @endif
                            <td>
                                <div style="font-size: 0.85rem; font-weight: 500;">{{ $booking->start_time?->format('M d, Y') }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $booking->start_time?->format('h:i A') }} — {{ $booking->end_time?->format('h:i A') }}</div>
                            </td>
                            <td style="color: var(--text-dark); font-size: 0.85rem;">{{ $booking->purpose }}</td>
                            <td>
                                @php
                                    $statusColor = match($booking->status) {
                                        'approved' => '#10B981',
                                        'pending' => '#EAB308',
                                        'rejected' => '#EF4444',
                                        default => '#94A3B8',
                                    };
                                @endphp
                                <div class="status-dot">
                                    <div class="dot" style="background: {{ $statusColor }}"></div>
                                    <span style="text-transform: capitalize; font-weight: 600; font-size: 0.8rem;">{{ $booking->status }}</span>
                                </div>
                                @if ($booking->admin_remarks)
                                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        "{{ $booking->admin_remarks }}"
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                    @if (Auth::user()->isAdmin() && $booking->status === 'pending')
                                        <form action="{{ route('bookings.approve', $booking) }}" method="POST">
                                            @csrf
                                            <button type="submit" style="padding: 6px 12px; font-size: 0.75rem;">Approve</button>
                                        </form>
                                        <form action="{{ route('bookings.reject', $booking) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="remarks" value="Rejected by admin.">
                                            <button type="submit" class="ghost" style="padding: 6px 12px; font-size: 0.75rem; border-color: #FED7D7; color: #C53030;">Reject</button>
                                        </form>
                                    @endif

                                    @if (in_array($booking->status, ['pending', 'approved'], true))
                                        <form action="{{ route('bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('Cancel this booking?');">
                                            @csrf
                                            <button type="submit" class="ghost" style="padding: 6px 12px; font-size: 0.75rem;">Cancel</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ Auth::user()->isAdmin() ? 7 : 6 }}" style="text-align: center; padding: 40px; color: var(--text-muted);">No bookings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pagination', ['paginator' => $bookings])
    </div>
@endsection
