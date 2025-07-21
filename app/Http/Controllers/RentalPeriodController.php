<?php

namespace App\Http\Controllers;

use App\Models\RentalPeriod;
use App\Models\Rolador;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        $user = $request->user();
        $desc = $user->name . " registró un pago semanal de $" . number_format($rentalPeriod->amount_due, 2) . " para " . ($rolador->name ?? 'rolador desconocido') . ".";
        activity()
            ->performedOn($rentalPeriod)
            ->causedBy($user)
            ->withProperties([
                'attributes' => $rentalPeriod->getAttributes()
            ])
            ->log($desc);
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
        $old = $rentalPeriod->getOriginal();
        $rentalPeriod->update([
            'payment_date' => now()
        ]);
        $user = $request->user();
        $rolador = $rentalPeriod->rolador;
        $desc = $user->name . " marcó como pagado el periodo semanal de " . ($rolador->name ?? 'rolador desconocido') . " por $" . number_format($rentalPeriod->amount_due, 2) . ".";
        activity()
            ->performedOn($rentalPeriod)
            ->causedBy($user)
            ->withProperties([
                'old' => $old,
                'attributes' => $rentalPeriod->getAttributes()
            ])
            ->log($desc);
        return $rentalPeriod;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, RentalPeriod $rentalPeriod)
    {
        Gate::allowIf(fn(User $user) => $user->email === 'admin@plazadelvestido.com');
        $request->validate([
            'password' => 'required|current_password'
        ]);
        $old = $rentalPeriod->getAttributes();
        $rolador = $rentalPeriod->rolador;
        $desc = $request->user()->name . " eliminó el pago semanal de " . ($rolador->name ?? 'rolador desconocido') . " por $" . number_format($rentalPeriod->amount_due, 2) . ".";
        $rentalPeriod->delete();
        activity()
            ->performedOn($rentalPeriod)
            ->causedBy($request->user())
            ->withProperties([
                'old' => $old
            ])
            ->log($desc);
        return response()->noContent();
    }
}
