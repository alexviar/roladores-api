<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
