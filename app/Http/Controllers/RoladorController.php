<?php

namespace App\Http\Controllers;

use App\Enums\RentalPeriodStatuses;
use App\Models\Rolador;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

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

        $rolador = Rolador::create($payload);
        $rolador->load(['category']);
        return $rolador;
    }

    /**
     * Display the specified resource.
     */
    public function show(Rolador $rolador)
    {
        $rolador->load(['category', 'currentPunishment', 'currentRentalPeriod']);
        return $rolador;
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
        $payload = $request->all();

        Gate::allowIf(fn(User $user) => $user->email === 'admin@plazadelvestido.com');

        if (Arr::has($payload, 'photo')) {
            /** @var \Illuminate\Http\UploadedFile $photo */
            $photo = Arr::get($payload, 'photo');
            $payload['photo'] = $photo->store('roladores', 'public');
        }

        $oldPhoto = $rolador->photo;
        $rolador->update($payload);

        if ($oldPhoto !== $rolador->photo) {
            Storage::disk('public')->delete($oldPhoto);
        }

        $rolador->load(['category', 'currentPunishment', 'currentRentalPeriod']);
        return $rolador;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rolador $rolador)
    {
        Gate::allowIf(fn(User $user) => $user->email === 'admin@plazadelvestido.com');

        $rolador->delete();
        if ($rolador->photo) {
            Storage::disk('public')->delete($rolador->photo);
        }

        return response()->noContent();
    }
}
