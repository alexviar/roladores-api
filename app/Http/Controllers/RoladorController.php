<?php

namespace App\Http\Controllers;

use App\Enums\RentalPeriodStatuses;
use App\Models\Rolador;
use Illuminate\Http\Request;

class RoladorController extends Controller
{

    protected function applyFilters(Request $request, $query)
    {
        $query->when($request->get('search'), function ($query, $search) {
            $query->where('name', 'like', "%$search%");
        });

        $query->when($request->input('filter.status'), function ($query, $status) {
            if ($status === 'punished') {
                $query->whereHas('currentPunishment');
            } else {
                $queryMethod = $status == RentalPeriodStatuses::Paid->value ? 'isPaid' : 'isUnpaid';
                $query->whereDoesntHave('currentPunishment')
                    ->whereHas('currentRentalPeriod', fn($query) => $query->$queryMethod());
            }
        });

        $query->latest('id');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Rolador::query();

        $this->applyFilters($request, $query);

        $query->with(['category', 'currentPunishment', 'currentRentalPeriod']);
        $result = $query->paginate(12);
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
        $payload = $request->all();

        /** @var \Illuminate\Http\UploadedFile $photo */
        $photo = $payload['photo'];
        $payload['photo'] = $photo->store('roladores', 'public');

        return Rolador::create($payload);
    }

    /**
     * Display the specified resource.
     */
    public function show(Rolador $rolador)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rolador $rolador)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rolador $rolador)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rolador $rolador)
    {
        //
    }
}
