<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Filesystem\LocalFilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class Rolador extends Model
{
    /** @use HasFactory<\Database\Factories\RoladorFactory> */
    use HasFactory;

    protected $fillable = [
        'photo',
        'name',
        'category_id',
        'activity_description',
        'weekly_payment',
    ];

    protected $appends = [
        'credits_summary',
    ];

    public function creditsSummary(): Attribute
    {
        return Attribute::get(function () {
            $totalActiveCredits = $this->credits()->where('balance', '>', 0)->count();
            $totalBalance = $this->credits()->sum('balance');
            $latestActiveCredit = $this->credits()->where('balance', '>', 0)->latest()->first();

            return [
                'activeCreditsCount' => (int) $totalActiveCredits,
                'totalPendingBalance' => (float) $totalBalance,
                'latestActiveCredit' => $latestActiveCredit,
            ];
        });
    }

    #region Relationships

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function currentPunishment(): HasOne
    {
        return $this->hasOne(Punishment::class)
            ->isCurrent();
    }

    public function currentRentalPeriod(): HasOne
    {
        return $this->hasOne(RentalPeriod::class)
            ->isCurrent();
    }

    public function credits(): HasMany
    {
        return $this->hasMany(Credit::class);
    }

    #endregion

    public function toArray()
    {
        /** @var LocalFilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $array = parent::toArray();
        $array['photo'] = $disk->url($array['photo']);
        return $array;
    }
}
