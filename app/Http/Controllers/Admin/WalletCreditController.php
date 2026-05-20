<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\View\View;

class WalletCreditController extends Controller
{
    public function index(): View
    {
        $credits = Payment::where('type', Payment::TYPE_WALLET_CREDIT)
            ->where('is_condoned', false)
            ->with('user')
            ->latest()
            ->paginate(15);

        $totalCredits = Payment::where('type', Payment::TYPE_WALLET_CREDIT)
            ->where('is_condoned', false)
            ->sum('amount');

        return view('admin.wallet-credits.index', compact('credits', 'totalCredits'));
    }
}
