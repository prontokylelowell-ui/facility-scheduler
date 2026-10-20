<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStatusLog extends Model
{
    use HasFactory;

    /**
     * The table name is singular, so Eloquent's pluralization
     * ("booking_status_logs") has to be overridden.
     */
    protected $table = 'booking_status_log';

    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'old_status',
        'new_status',
        'changed_by_user_id',
        'remarks',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
