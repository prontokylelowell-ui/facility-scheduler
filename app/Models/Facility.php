<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'facility_type_id',
        'location',
        'capacity',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function facilityType(): BelongsTo
    {
        return $this->belongsTo(FacilityType::class);
    }

    public function operatingHours(): HasMany
    {
        return $this->hasMany(OperatingHour::class);
    }

    public function blackoutDates(): HasMany
    {
        return $this->hasMany(BlackoutDate::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isAvailable($startTime, $endTime, $excludeBookingId = null): bool
    {
        return \DB::selectOne(
            'SELECT is_facility_available(?, ?::timestamptz, ?::timestamptz, ?) AS available',
            [$this->id, $startTime, $endTime, $excludeBookingId]
        )->available ?? false;
    }

    public function isBusy(): bool
    {
        return $this->bookings()
            ->where('status', 'approved')
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->exists();
    }
}
