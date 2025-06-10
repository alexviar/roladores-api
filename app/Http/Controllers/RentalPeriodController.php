<?php

namespace App\Http\Controllers;

use App\Models\RentalPeriod;
use App\Models\Rolador;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RentalPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'rolador_id' => ['required', Rule::exists(Rolador::class, 'id')],
        ]);

        $rolador = Rolador::find($request->rolador_id);

        $today = today();
        $rentalPeriod = RentalPeriod::create([
            'payment_date' => $today,
            'start_date' => $today,
            'end_date' => $today,
            'amount_due' => $rolador->weekly_payment,
            'rolador_id' => $rolador->id
        ]);

        return $rentalPeriod;
    }

    /**
     * Display the specified resource.
     */
    public function show(RentalPeriod $rentalPeriod)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RentalPeriod $rentalPeriod)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RentalPeriod $rentalPeriod)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function markAsPaid(Request $request, RentalPeriod $rentalPeriod)
    {
        $request->validate([
            'password' => ['required', 'current_password']
        ]);

        $rentalPeriod->update([
            'payment_date' => now()
        ]);

        return $rentalPeriod;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RentalPeriod $rentalPeriod)
    {
        //
    }
}
