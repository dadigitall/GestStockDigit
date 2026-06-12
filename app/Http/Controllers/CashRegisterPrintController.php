<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\CashRegister;
use Illuminate\Http\Request;

class CashRegisterPrintController extends Controller
{
    public function rapport(CashRegister $cashRegister, Request $request)
    {
        if ($cashRegister->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $period = $request->get('period', 'shift');
        $dateFrom = $request->get('from');
        $dateTo = $request->get('to');

        $query = CashMovement::where('cash_register_id', $cashRegister->id)
            ->with('user');

        if ($period === 'shift' && $cashRegister->opened_at) {
            $query->where('movement_date', '>=', $cashRegister->opened_at);
            if ($cashRegister->closed_at) {
                $query->where('movement_date', '<=', $cashRegister->closed_at);
            }
        } elseif ($period === 'today') {
            $query->whereDate('movement_date', today());
        } elseif ($period === 'week') {
            $query->whereBetween('movement_date', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereMonth('movement_date', now()->month)->whereYear('movement_date', now()->year);
        } elseif ($period === 'custom' && $dateFrom) {
            $query->whereDate('movement_date', '>=', $dateFrom);
            if ($dateTo) {
                $query->whereDate('movement_date', '<=', $dateTo);
            }
        }

        $movements = $query->orderBy('movement_date', 'desc')->get();

        $byPaymentMethod = $movements->groupBy('payment_method')->map(fn ($g) => $g->sum('amount'));
        $byType = $movements->groupBy('type')->map(fn ($g) => $g->sum('amount'));

        $totalIn = $movements->where('direction', 'in')->sum('amount');
        $totalOut = $movements->where('direction', 'out')->sum('amount');

        return view('cash-registers.rapport-print', compact(
            'cashRegister', 'movements', 'period',
            'byPaymentMethod', 'byType', 'totalIn', 'totalOut'
        ));
    }
}
