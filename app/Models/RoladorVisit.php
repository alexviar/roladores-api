<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoladorVisit extends Model
{
    /** @use HasFactory<\Database\Factories\RoladorVisitFactory> */
    use HasFactory;

    protected $fillable = [
        'rolador_id',
        'visited_at',
        'visits'
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'visits' => 'integer'
    ];

    public function rolador(): BelongsTo
    {
        return $this->belongsTo(Rolador::class);
    }
}
