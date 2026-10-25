@extends('layouts.app')

@section('title', 'Facilities — Resort Booking')

@section('content')
    <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            @if(Auth::user()->isAdmin())
                <nav style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
                    Administration / Facilities
                </nav>
            @endif
            <h1>Facilities</h1>
            <p class="section-desc">Keep every resort space organized and ready for your guests.</p>
        </div>
        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; color: var(--text-muted); margin-bottom: 30px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            Welcome back, {{ Auth::user()->first_name }}
        </div>
    </div>

    @if (!Auth::user()->isAdmin())
        <div class="hero-banner">
            <div class="hero-content">
                <div class="hero-tag">Make yourself at home</div>
                <h2 class="hero-title">A space for every kind of stay.</h2>
                <p class="hero-desc">From quiet cottages to rooms for a celebration, explore the facilities that make your resort experience your own.</p>
            </div>
            <div class="hero-image" style="background-image: url('https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=luxury%20resort%20exterior%20with%20palm%20trees%20and%20infinity%20pool%20during%20daylight%2C%20bright%20and%20airy%2C%20high%20quality%2C%20realistic%20photography&image_size=landscape_16_9');"></div>
        </div>
    @endif

    @if (Auth::user()->isAdmin())
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                <div>
                    <h2 style="margin: 0;">Add a facility</h2>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 4px 0 0;">Create a new space in your resort directory.</p>
                </div>
                <div style="display: flex; align-items: center; gap: 6px; font-size: 0.75rem; font-weight: 700; color: var(--primary); text-transform: uppercase;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    Admin only
                </div>
            </div>

            <form action="{{ route('facilities.store') }}" method="POST">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Beachfront Cottage C" required>
                    </div>
                    <div class="form-group">
                        <label for="facility_type_id">Type</label>
                        <select id="facility_type_id" name="facility_type_id" required>
                            <option value="">Select a type</option>
                            @foreach ($facilityTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('facility_type_id') == $type->id)>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input id="location" type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Beachfront" required>
                    </div>
                    <div class="form-group">
                        <label for="capacity">Capacity</label>
                        <div style="position: relative;">
                            <input id="capacity" type="number" name="capacity" min="1" value="{{ old('capacity') }}" placeholder="Number of guests" required>
                            <svg style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                    </div>
                    <div class="form-group full">
                        <label for="notes">Notes (optional)</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Add any helpful details about this facility">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Create facility
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <h2 style="margin: 0;">All facilities</h2>
                <span style="background: var(--primary-light); color: var(--primary); padding: 2px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                    {{ $facilities->total() }}
                </span>
            </div>
            <form method="GET" action="{{ route('facilities.index') }}" style="display: flex; gap: 12px;">
                <div style="position: relative; width: 240px;">
                    <input id="facility-search" type="search" name="search" value="{{ request('search') }}" aria-label="Search facilities" placeholder="Search facilities" style="padding-left: 36px; padding-right: 12px; height: 38px; font-size: 0.85rem;">
                    <svg style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
                <select name="facility_type_id" aria-label="Filter by facility type" onchange="this.form.submit()" style="width: 140px; height: 38px; font-size: 0.85rem; padding: 0 12px;">
                    <option value="">All types</option>
                    @foreach ($facilityTypes as $type)
                        <option value="{{ $type->id }}" @selected((string) request('facility_type_id') === (string) $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="ghost" style="height: 38px; padding: 0 16px;">Search</button>
            </form>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Facility Name</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Capacity</th>
                        <th>Is Busy</th>
                        @if (Auth::user()->isAdmin())
                            <th style="width: 100px;">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($facilities as $facility)
                        @php $isBusy = $facility->isBusy(); @endphp
                        <tr>
                            <td style="color: var(--text-muted); font-size: 0.8rem;">{{ $facility->id }}</td>
                            <td style="font-weight: 600;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; background: var(--bg-light); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                        @if(str_contains(strtolower($facility->facilityType->name), 'pool'))
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 6c.6.5 1.2 1 2.5 1C7 7 7 5 9.5 5c2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"></path><path d="M2 12c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"></path><path d="M2 18c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"></path></svg>
                                        @elseif(str_contains(strtolower($facility->facilityType->name), 'hall') || str_contains(strtolower($facility->facilityType->name), 'pavilion'))
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                        @elseif(str_contains(strtolower($facility->facilityType->name), 'cottage'))
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                                        @elseif(str_contains(strtolower($facility->facilityType->name), 'court'))
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                                        @else
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                                        @endif
                                    </div>
                                    {{ $facility->name }}
                                </div>
                            </td>
                            <td style="color: var(--text-muted);">{{ $facility->facilityType->name ?? '—' }}</td>
                            <td style="color: var(--text-muted);">{{ $facility->location }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted);" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                    {{ $facility->capacity }}
                                </div>
                            </td>
                            <td>
                                <div class="status-dot">
                                    <div class="dot {{ $isBusy ? 'dot-red' : 'dot-green' }}"></div>
                                    {{ $isBusy ? 'Yes' : 'No' }}
                                </div>
                            </td>
                            @if (Auth::user()->isAdmin())
                                <td>
                                    <form action="{{ route('facilities.destroy', $facility) }}" method="POST" onsubmit="return confirm('Delete this facility?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ghost" style="padding: 6px 12px; font-size: 0.75rem; border-color: #FED7D7; color: #C53030;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ Auth::user()->isAdmin() ? 7 : 6 }}" style="text-align: center; padding: 40px; color: var(--text-muted);">No facilities yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pagination', ['paginator' => $facilities])
    </div>
@endsection
