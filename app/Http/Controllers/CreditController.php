<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\Rolador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;

class CreditController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Credit::query();

        $request->whenFilled('search', fn($value) => $query->where('title', 'like', "%{$value}%"));
        $request->whenFilled('filter.rolador_id', fn($value) => $query->where('rolador_id', $value));

        $paginator = $query->clone()->paginate($request->get('page_size'));

        $request->whenFilled('include', function ($value) use (&$paginator, $query) {
            if ($value === 'summary') {
                $summary = $query->where('balance', '>', 0)->selectRaw('
                    COUNT(1) as active_credits_count,
                    SUM(balance) as total_pending_balance
                ')->first();

                $paginator = collect([
                    'summary' => [
                        'active_credits_count' => (int) $summary->active_credits_count,
                        'total_pending_balance' => (float) $summary->total_pending_balance,
                    ],
                ])->merge($paginator);
            }
        });

        return $paginator;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $payload = $this->preparePayload($request);

        return DB::transaction(function () use ($payload, $request) {
            $credit = Credit::create($payload);
            $credit->load('rolador');
            $user = $request->user();
            $desc = $user->name . " registró un nuevo crédito de <b>$" . number_format($credit->amount, 2) . "</b> con motivo <b><i>\"" . $credit->title . "\"</i></b> para el rolador <b>" . ($credit->rolador->name ?? '<i>desconocido</i>') . "</b>.";
            activity()
                ->performedOn($credit)
                ->causedBy($user)
                ->withProperties([
                    'attributes' => $credit->fresh()->getAttributes()
                ])
                ->event('created')
                ->log($desc);
            return $credit;
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Credit::with('rolador', 'payments')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $credit = Credit::findOrFail($id);
        $credit->load('rolador');
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'credit_amount' => 'sometimes|numeric|min:0',
            'pending_balance' => 'sometimes|numeric|min:0',
            'password' => 'required|string',
        ]);
        if (!Hash::check($request->password, config('auth.admin_password'))) {
            throw ValidationException::withMessages([
                'password' => ['La contraseña es incorrecta.'],
            ]);
        }
        return DB::transaction(function () use ($request, $credit) {
            $old = $credit->getOriginal();
            $credit->update($request->only(['name', 'credit_amount', 'pending_balance']));
            $credit->load('rolador');
            $user = $request->user();
            $desc = $user->name . " actualizó el crédito <b><i>\"" . $credit->title . "\"</i></b> del rolador <b>" . ($credit->rolador->name ?? '<i>desconocido</i>') . "</b>.";
            activity()
                ->performedOn($credit)
                ->causedBy($user)
                ->withProperties([
                    'old' => $old,
                    'attributes' => $credit->fresh()->getAttributes()
                ])
                ->event('updated')
                ->log($desc);
            return $credit;
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Credit $credit)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);
        $credit->load('rolador');
        if ($credit->payments()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar el crédito porque tiene pagos registrados.'
            ], 409);
        }
        return DB::transaction(function () use ($credit, $request) {
            $old = $credit->getAttributes();
            $user = $request->user();
            $desc = $user->name . " eliminó el crédito <b><i>\"" . $credit->title . "\"</i></b> del rolador <b>" . ($credit->rolador->name ?? '<i>desconocido</i>') . "</b>.";
            $credit->delete();
            activity()
                ->performedOn($credit)
                ->causedBy($user)
                ->withProperties([
                    'old' => $old
                ])
                ->event('deleted')
                ->log($desc);
            return response()->json(['message' => 'Crédito eliminado correctamente']);
        });
    }

    /**
     * Reset credit pending balance to original amount.
     */
    public function resetCredit(Request $request, Credit $credit)
    {
        $request->validate([
            'password' => 'required|password',
        ]);

        return DB::transaction(function () use ($credit) {
            $credit->update([
                'balance' => $credit->amount
            ]);

            return $credit->fresh()->load('rolador');
        });
    }

    private function preparePayload(Request $request)
    {
        $payload = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'rolador_id' => 'required|exists:roladors,id'
        ]);

        $payload['balance'] = $payload['amount'];

        return $payload;
    }
}
