<?php

namespace App\Http\Controllers;

use App\Models\Rolador;
use App\Models\RoladorVisit;
use Illuminate\Http\Request;

class RoladorVisitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Rolador::query();

        $query->withCount([
            'visits' => function ($query) use ($request) {
                $request->whenFilled('filter.date', function ($date) use ($query) {
                    $query->whereDate('visited_at', $date);
                });
            }
        ]);

        $request->whenFilled('filter.visited', function ($visited) use ($query) {
            $query->having('visits_count', $visited ? '>' : '=', 0);
        });

        $result = $query->paginate();
        return $result;
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
        $rolador = Rolador::findOrFail($request->rolador_id);

        return $rolador->visits()->create([
            'visited_at' => now(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(RoladorVisit $roladorVisit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoladorVisit $roladorVisit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RoladorVisit $roladorVisit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoladorVisit $roladorVisit)
    {
        //
    }
}
