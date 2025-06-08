<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalPeriod extends Model
{
    /** @use HasFactory<\Database\Factories\RentalPeriodFactory> */
    use HasFactory;

    #region Relationships

    public function rolador(): BelongsTo
    {
        return $this->belongsTo(Rolador::class);
    }

    #endregion
}
