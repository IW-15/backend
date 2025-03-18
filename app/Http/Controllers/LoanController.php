<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\Loan;
use App\Models\LoanProfile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $loanProfile = LoanProfile::where('id_user', $user->id)->get()->first();

            if (!$loanProfile) {
                return BaseResponse::error("Loan profile not found", 404, "Loan profile not found");
            }
            // $loans = $loanProfile->loans;
            $loans = Loan::where("id_loan_profile", $loanProfile->id)->with("repayment")->get();

            return BaseResponse::success("Success retrieve loans data", $loans);
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving loans data", 500, $error->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function apply(Request $request)
    {
        try {
            // Validate the input
            $validator = $request->validate([
                'amount' => 'required|numeric',
                'tenor' => 'required|integer',
            ]);

            $amount = $validator['amount'];
            $tenor = $validator['tenor'];

            $user = $request->user();

            // Assuming the user's LoanProfile needs to be fetched somehow
            $loanProfile = LoanProfile::where('id_user', $user->id)->get()->first();

            if (!$loanProfile) {
                return BaseResponse::error("Loan profile not found", 404, "Loan profile not found");
            }

            // Calculate the maximum limit
            $maxLimit = $loanProfile->maxAmount - $loanProfile->limit;

            // Check if the amount exceeds max limits
            if ($amount > $maxLimit) {
                return BaseResponse::error("Your amount is exceed max limits", 400, "Your amount is exceed max limits");
            }

            // Calculate monthly bill
            $monthlyBill = ($amount / $tenor) * 1.05;

            // Create the loan
            DB::transaction(function () use ($loanProfile, &$loan, $amount, $tenor, $monthlyBill) {
                $loan = Loan::create([
                    'id_loan_profile' => $loanProfile->id,
                    'amount' => $amount,
                    'tenor' => $tenor,
                    'monthlyBill' => $monthlyBill,
                    'applicationDate' => now(),
                    'status' => 'progress',
                ]);

                $loanProfile->update([
                    "limit" => $loanProfile->limit + $amount
                ]);
            });

            // Return a successful response (you can customize this response as needed)
            return BaseResponse::success("Apply loan success", $loan);
        } catch (Exception $error) {
            return BaseResponse::error("Error while apply loan", 500, $error->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id, Request $request)
    {
        try {
            $user = $request->user();
            $loanProfile = LoanProfile::where('id_user', $user->id)->get()->first();

            if (!$loanProfile) {
                return BaseResponse::error("Loan profile not found", 404, "Loan profile not found");
            }

            // $loans = Loan::where("id_loan_profile", $loanProfile->id)->with("repayment")->get();
            $loan = Loan::with("repayment")->find($id);

            return BaseResponse::success("Success retrieve loans data", $loan);
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving loans data", 500, $error->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Loan $loan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Loan $loan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Loan $loan)
    {
        //
    }
}
