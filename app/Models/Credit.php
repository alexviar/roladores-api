<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    /** @use HasFactory<\Database\Factories\CreditFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'amount',
        'balance',
        'rolador_id',
    ];

    public function casts(): array
    {
        return [
            'amount' => 'float',
            'balance' => 'float',
            'rolador_id' => 'integer',
        ];
    }

    public function rolador()
    {
        return $this->belongsTo(Rolador::class);
    }

    public function payments()
    {
        return $this->hasMany(CreditPayment::class);
    }
}
