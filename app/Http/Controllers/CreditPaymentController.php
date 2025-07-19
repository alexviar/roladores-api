<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\CreditPayment;
use App\Models\Rolador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
        ], [
            'amount.max' => 'El monto del pago excede el saldo pendiente del crédito.'
        ]);

        return DB::transaction(function () use ($request) {
            $credit = Credit::findOrFail($request->credit_id);

            $credit->update([
                'balance' => $credit->balance - $request->amount
            ]);

            $payment = CreditPayment::create([
                'amount' => $request->amount,
                'credit_id' => $request->credit_id,
                'date' => now(),
            ]);

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

        DB::transaction(function () use ($credit, $payment) {

            $credit->update([
                'balance' => $credit->balance + $payment->amount
            ]);

            $payment->delete();
        });

        return response()->noContent();
    }
}
