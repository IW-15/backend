<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\CreditScore;
use App\Models\LoanProfile;
use Exception;
use Illuminate\Http\Request;

class CreditScoreController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $loanProfile = LoanProfile::where("id_user", $user->id)->get()->first();
            if (!$loanProfile) {
                return BaseResponse::error("Loan profile data not found", 404, "Loan profile data not found");
            }

            $creditScore = CreditScore::where("id_loan_profile", $loanProfile->id)->with("loanProfile")->get()->first();
            if (!$creditScore) {
                return BaseResponse::error("Credit score data not found", 404, "Credit score data not found");
            }

            return BaseResponse::success("Success retrieve creditscore data", $creditScore);
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving creditscore data", 500, $error->getMessage());
        }
    }
}
