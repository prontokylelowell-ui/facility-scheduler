<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlackoutDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'blackout_date',
        'reason',
        'is_global',
    ];

    protected $casts = [
        'blackout_date' => 'date',
        'is_global' => 'boolean',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
}
