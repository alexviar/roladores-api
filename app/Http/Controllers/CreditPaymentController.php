<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\CreditPayment;
use App\Models\Rolador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;

class CreditPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $creditId)
    {
        $query = CreditPayment::query();

        $query->where('credit_id', $creditId);

        return $query->latest()->paginate($request->get('pageSize'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Credit $credit)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $credit->balance,
            'password' => 'required|current_password',
        ], [
            'amount.max' => 'El monto del pago excede el saldo pendiente del crédito.'
        ]);
        return DB::transaction(function () use ($request) {
            $credit = Credit::findOrFail($request->credit_id);
            $credit->load('rolador');
            $oldCredit = $credit->getOriginal();
            $credit->update([
                'balance' => $credit->balance - $request->amount
            ]);
            $payment = CreditPayment::create([
                'amount' => $request->amount,
                'credit_id' => $request->credit_id,
                'date' => now(),
            ]);
            $user = $request->user();
            $descPago = $user->name . " registró un pago de $" . number_format($payment->amount, 2) . " para el crédito de " . ($credit->rolador->name ?? 'rolador desconocido') . ".";
            activity()
                ->performedOn($payment)
                ->causedBy($user)
                ->withProperties([
                    'attributes' => $payment->getAttributes()
                ])
                ->event('created')
                ->log($descPago);
            $descCredito = $user->name . " actualizó el balance del crédito de " . ($credit->rolador->name ?? 'rolador desconocido') . " a $" . number_format($credit->balance, 2) . ".";
            activity()
                ->performedOn($credit)
                ->causedBy($user)
                ->withProperties([
                    'old' => $oldCredit,
                    'attributes' => $credit->getAttributes()
                ])
                ->event('updated')
                ->log($descCredito);
            return $payment->load(['credit', 'rolador']);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return CreditPayment::with(['credit', 'rolador'])->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Credit $credit, CreditPayment $payment)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);
        $credit->load('rolador');
        DB::transaction(function () use ($credit, $payment, $request) {
            $oldCredit = $credit->getOriginal();
            $oldPayment = $payment->getAttributes();
            $user = $request->user();
            $descPago = $user->name . " eliminó el pago de $" . number_format($payment->amount, 2) . " para el crédito de " . ($credit->rolador->name ?? 'rolador desconocido') . ".";
            $credit->update([
                'balance' => $credit->balance + $payment->amount
            ]);
            $payment->delete();
            activity()
                ->performedOn($payment)
                ->causedBy($user)
                ->withProperties([
                    'old' => $oldPayment
                ])
                ->event('deleted')
                ->log($descPago);
            $descCredito = $user->name . " actualizó el balance del crédito de " . ($credit->rolador->name ?? 'rolador desconocido') . " a $" . number_format($credit->balance, 2) . ".";
            activity()
                ->performedOn($credit)
                ->causedBy($user)
                ->withProperties([
                    'old' => $oldCredit,
                    'attributes' => $credit->getAttributes()
                ])
                ->event('updated')
                ->log($descCredito);
        });
        return response()->noContent();
    }
}
