<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    //
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $transactions = Transaction::where("id_user", $user->id)->get();

            // Initialize totals
            $totalIncome = 0;
            $totalOutcome = 0;

            // Loop through transactions to calculate totals
            foreach ($transactions as $transaction) {
                if ($transaction->type === 'credit') {
                    $totalIncome += $transaction->amount;
                } elseif ($transaction->type === 'debit') {
                    $totalOutcome += $transaction->amount;
                }
            }

            return BaseResponse::success("lah", [
                "income" => $totalIncome,
                "outcome" => $totalOutcome,
                "history" => $transactions
            ]);
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieve transactions data", 500, $error->getMessage());
        }
    }
}
