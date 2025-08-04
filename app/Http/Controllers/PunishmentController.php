<?php

namespace App\Http\Controllers;

use App\Models\Punishment;
use Illuminate\Http\Request;

class PunishmentController extends Controller
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
        $payload = $request->validate([
            'password' => ['required', 'current_password'],
            'description' => ['required', 'string', 'max:255'],
            'end_date' => ['required', 'date'],
            'rolador_id' => ['required', 'integer', 'exists:roladors,id'],
        ]);

        $punishment = Punishment::create([
            'start_date' => now(),
        ] + $payload);

        $user = $request->user();
        $desc = $user->name . " registró un castigo para el rolador <b>" . ($punishment->rolador->name ?? '<i>desconocido</i>') . "</b> hasta el <b>" . date('d/m/Y', strtotime($payload['end_date'])) . "</b>.";
        activity()
            ->performedOn($punishment)
            ->causedBy($user)
            ->withProperties([
                'attributes' => $punishment->getAttributes()
            ])
            ->event('created')
            ->log($desc);

        return $punishment;
    }

    /**
     * Display the specified resource.
     */
    public function show(Punishment $punishment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Punishment $punishment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Punishment $punishment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Punishment $punishment)
    {
        //
    }
}
