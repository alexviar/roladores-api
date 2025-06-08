<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Punishment extends Model
{
    /** @use HasFactory<\Database\Factories\PunishmentFactory> */
    use HasFactory;

    #region Relationships

    public function rolador(): BelongsTo
    {
        return $this->belongsTo(Rolador::class);
    }

    #endregion
}
