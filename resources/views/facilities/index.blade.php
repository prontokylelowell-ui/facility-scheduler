@extends('layouts.app')

@section('title', 'Facilities — Resort Booking')

@section('content')
    <h1>Facilities <span class="muted">(core entity #1 — full CRUD)</span></h1>

    @if (Auth::user()->isAdmin())
        <div class="card">
            <h2>Add a facility <span class="role-tag">admin only</span></h2>
            <form action="{{ route('facilities.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div>
                        <label for="name">Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div>
                        <label for="facility_type_id">Type</label>
                        <select id="facility_type_id" name="facility_type_id" required>
                            <option value="">— select —</option>
                            @foreach ($facilityTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('facility_type_id') == $type->id)>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div>
                        <label for="location">Location</label>
                        <input id="location" type="text" name="location" value="{{ old('location') }}" required>
                    </div>
                    <div>
                        <label for="capacity">Capacity</label>
                        <input id="capacity" type="number" name="capacity" min="1" value="{{ old('capacity') }}" required>
                    </div>
                </div>
                <label for="notes">Notes (optional)</label>
                <input id="notes" type="text" name="notes" value="{{ old('notes') }}">

                <div style="margin-top:16px;">
                    <button type="submit">Create facility</button>
                </div>
            </form>
        </div>
    @endif

    <div class="card">
        <h2>All facilities</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Capacity</th>
                    <th>Active</th>
                    @if (Auth::user()->isAdmin())
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($facilities as $facility)
                    <tr>
                        <td>{{ $facility->id }}</td>
                        <td>{{ $facility->name }}</td>
                        <td>{{ $facility->facilityType->name ?? '—' }}</td>
                        <td>{{ $facility->location }}</td>
                        <td>{{ $facility->capacity }}</td>
                        <td>{{ $facility->is_active ? 'Yes' : 'No' }}</td>

                        @if (Auth::user()->isAdmin())
                            <td>
                                <details>
                                    <summary class="btn-sm" style="cursor:pointer;">Edit</summary>
                                    <form action="{{ route('facilities.update', $facility) }}" method="POST" style="margin-top:10px;">
                                        @csrf
                                        @method('PUT')
                                        <label>Name</label>
                                        <input type="text" name="name" value="{{ $facility->name }}" required>

                                        <label>Type</label>
                                        <select name="facility_type_id" required>
                                            @foreach ($facilityTypes as $type)
                                                <option value="{{ $type->id }}" @selected($facility->facility_type_id == $type->id)>
                                                    {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <label>Location</label>
                                        <input type="text" name="location" value="{{ $facility->location }}" required>

                                        <label>Capacity</label>
                                        <input type="number" name="capacity" min="1" value="{{ $facility->capacity }}" required>

                                        <label>Notes</label>
                                        <input type="text" name="notes" value="{{ $facility->notes }}">

                                        <div style="margin-top:12px;">
                                            <button class="btn-sm" type="submit">Save</button>
                                        </div>
                                    </form>

                                    <form action="{{ route('facilities.destroy', $facility) }}" method="POST" style="margin-top:8px;"
                                          onsubmit="return confirm('Delete {{ $facility->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger btn-sm" type="submit">Delete</button>
                                    </form>
                                </details>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">No facilities yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">{{ $facilities->links() }}</div>
    </div>
@endsection
