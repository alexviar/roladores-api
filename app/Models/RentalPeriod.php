<?php

namespace App\Models;

use App\Enums\RentalPeriodStatuses;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalPeriod extends Model
{
    /** @use HasFactory<\Database\Factories\RentalPeriodFactory> */
    use HasFactory;

    protected $appends = [
        'status',
    ];

    #region Attributes

    public function status(): Attribute
    {
        return Attribute::get(function () {
            $now = now();

            if ($this->payment_date === null) {
                return $this->end_date < $now
                    ? RentalPeriodStatuses::Overdue
                    : RentalPeriodStatuses::Unpaid;
            }

            return RentalPeriodStatuses::Paid;
        });
    }

    #endregion

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

    /**
     * Scope a query to only include records that are paid.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    #[Scope]
    public function isPaid(Builder $query): void
    {
        $query->whereNotNull('payment_date');
    }

    /**
     * Scope a query to only include records that are unpaid.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    #[Scope]
    public function isUnpaid(Builder $query): void
    {
        $query->whereNull('payment_date');
    }

    /**
     * Scope a query to only include records that are overdue.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    #[Scope]
    public function isOverdue(Builder $query): void
    {
        $query->isUnpaid()
            ->where('end_date', '<', now());
    }

    #endregion
}
