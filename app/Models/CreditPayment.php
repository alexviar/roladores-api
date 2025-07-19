<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditPayment extends Model
{
    /** @use HasFactory<\Database\Factories\CreditPaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'date',
        'amount',
        'credit_id',
    ];

    public function casts(): array
    {
        return [
            'date' => 'datetime',
            'amount' => 'float'
        ];
    }

    public function credit()
    {
        return $this->belongsTo(Credit::class);
    }

    public function rolador()
    {
        return $this->belongsTo(Rolador::class);
    }
}
