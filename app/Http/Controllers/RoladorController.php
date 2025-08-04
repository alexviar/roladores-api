<?php

namespace App\Http\Controllers;

use App\Enums\RentalPeriodStatuses;
use App\Models\Rolador;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;

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
            } else if ($status === 'paid') {
                $query->whereDoesntHave('currentPunishment')
                    ->whereHas('currentRentalPeriod');
            } else if ($status === 'unpaid') {
                $query->whereDoesntHave('currentPunishment')
                    ->whereDoesntHave('currentRentalPeriod');
            } else if ($status === 'with_active_credit') {
                $query->whereHas('credits', function ($q) {
                    $q->where('balance', '>', 0);
                });
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
        $payload = $request->validate([
            'name' => 'required|string|unique:roladors,name',
            'category_id' => 'required|integer',
            'photo' => 'required|image',
            'activity_description' => 'nullable|string|max:255',
            'weekly_payment' => 'required|numeric',
        ]);

        /** @var \Illuminate\Http\UploadedFile $photo */
        $photo = $payload['photo'];
        $payload['photo'] = $photo->store('roladores', 'public');

        $rolador = Rolador::create($payload);
        $rolador->load(['category']);

        $user = $request->user();
        $desc = $user->name . " registró un nuevo rolador: <b>" . $rolador->name . "<b>.";

        activity()
            ->performedOn($rolador)
            ->causedBy($user)
            ->withProperties([
                'attributes' => $rolador->getAttributes()
            ])
            ->event('created')
            ->log($desc);

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
        $payload = $request->validate([
            'name' => 'required|string|unique:roladors,name,' . $rolador->id,
            'category_id' => 'required|integer',
            'photo' => 'sometimes|required|image',
            'activity_description' => 'nullable|string|max:255',
            'weekly_payment' => 'required|numeric',
        ]);

        if (Arr::has($payload, 'photo')) {
            /** @var \Illuminate\Http\UploadedFile $photo */
            $photo = Arr::get($payload, 'photo');
            $payload['photo'] = $photo->store('roladores', 'public');
        }

        $old = $rolador->getOriginal();
        $oldPhoto = $rolador->photo;
        $rolador->update($payload);

        if ($oldPhoto !== $rolador->photo) {
            Storage::disk('public')->delete($oldPhoto);
        }

        $rolador->load(['category', 'currentPunishment', 'currentRentalPeriod']);

        $user = $request->user();
        $desc = $user->name . " actualizó los datos del rolador: <b>" . $rolador->name . "</b>.";

        activity()
            ->performedOn($rolador)
            ->causedBy($user)
            ->withProperties([
                'old' => $old,
                'attributes' => $rolador->getAttributes()
            ])
            ->event('updated')
            ->log($desc);

        return $rolador;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rolador $rolador, Request $request)
    {
        Gate::allowIf(fn(User $user) => $user->email === 'admin@plazadelvestido.com');

        // Verificar que no tenga créditos activos
        if ($rolador->credits()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar el rolador porque tiene créditos registrados.'
            ], 409);
        }

        $old = $rolador->getAttributes();
        $roladorName = $rolador->name;
        $rolador->delete();
        if ($rolador->photo) {
            Storage::disk('public')->delete($rolador->photo);
        }

        $user = $request->user();
        $desc = $user->name . " eliminó al rolador: <b>" . $roladorName . "</b>.";

        activity()
            ->performedOn($rolador)
            ->causedBy($user)
            ->withProperties([
                'old' => $old
            ])
            ->event('deleted')
            ->log($desc);

        return response()->noContent();
    }
}
