<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Punishment extends Model
{
    /** @use HasFactory<\Database\Factories\PunishmentFactory> */
    use HasFactory;

    protected $fillable = [
        'rolador_id',
        'start_date',
        'end_date',
        'description',
    ];

    #region Relationships

    public function rolador(): BelongsTo
    {
        return $this->belongsTo(Rolador::class);
    }

    #endregion

    #region QueryScopes

    /**
     * Scope a query to only include records that are currently active within their start and end dates.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    #[Scope]
    public function isCurrent(Builder $query): void
    {
        $query->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    #endregion

    public function casts()
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime'
        ];
    }
}
