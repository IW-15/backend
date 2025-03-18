<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\CreditScore;
use App\Models\LoanProfile;
use App\Models\Merchant;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchantController extends Controller
{
    public function show(Request $request)
    {
        try {
            $user = $request->user();
            $merchant = $user->merchant;

            return BaseResponse::success("Success retrieving merchant data", $merchant);
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving merchant data", 500, $error->getMessage());
        }
    }


    public function verify(Request $request)
    {
        try {
            $user = $request->user();
            // Check loan profile
            $validLoanProfile = LoanProfile::where("id_user", $user->id)->get()->first();
            if ($validLoanProfile) {
                $validCreditScore = CreditScore::where("id_loan_profile", $validLoanProfile->id)->get()->first();
                return BaseResponse::success("Success verify document", [
                    "loanProfile" => $validLoanProfile,
                    "creditScore" => $validCreditScore,
                ]);
            }

            DB::transaction(function () use ($user, &$merchant, &$loanProfile, &$creditScore) {
                $merchant =  Merchant::where("id_user", $user->id)->get()->first();
                $merchant->update([
                    "applyForm" => "https://cdn.mfadlilhs.site/dpka/activities/banner/1732302890812-ASA.png",
                    "ktp" => "https://cdn.mfadlilhs.site/dpka/activities/banner/1732302890812-ASA.png",
                    "photo" => "https://cdn.mfadlilhs.site/dpka/activities/banner/1732302890812-ASA.png",
                    "license" => "https://cdn.mfadlilhs.site/dpka/activities/banner/1732302890812-ASA.png",
                    "npwp" => "https://cdn.mfadlilhs.site/dpka/activities/banner/1732302890812-ASA.png",
                ]);

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

                $difference = $totalIncome - $totalOutcome;
                $maxAmount = floor(0.8 * $difference / 100000) * 100000;

                $loanProfile = LoanProfile::create([
                    "id_merchant" => $merchant->id,
                    "id_user" => $user->id,
                    "minAmount" => 300000,
                    "limit" => 0,
                    "maxAmount" => $maxAmount
                ]);

                $score = random_int(720, 900);
                $creditScore = CreditScore::create([
                    'id_loan_profile' => $loanProfile->id,
                    "score" => $score,
                    "indicator" => $score > 750 ? "Excellent" : "Good"
                ]);
            });

            return BaseResponse::success("Success verify document", [
                "loanProfile" => $loanProfile,
                "creditScore" => $creditScore,
            ]);
        } catch (Exception $error) {
            return BaseResponse::error("Error while verifying document", 500, $error->getMessage());
        }
    }
}
