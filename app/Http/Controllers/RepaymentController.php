<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\Loan;
use App\Models\Repayment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RepaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($idLoan, Request $request)
    {
        try {
            $loan = Loan::with("repayment")->find($idLoan);
            if (!$loan) {
                return BaseResponse::error("Loan data not found", 404, "Loan data not found");
            }
            $repayment = $loan->repayment;

            return BaseResponse::success("Success retrieve repayments data", $repayment);
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving repayments data", 500, $error->getMessage());
        }
    }

    public function pay($idLoan, Request $request)
    {
        try {
            $loan = Loan::with("repayment")->find($idLoan);

            if (!$loan) {
                return BaseResponse::error("Loan data not found", 404, "Loan data not found");
            }
            $loanRepayment = $loan->repayment;

            DB::transaction(function () use ($loan, $loanRepayment, &$repayment) {
                $countRepayment = count($loanRepayment);

                $repayment = Repayment::create([
                    "id_loan" => $loan->id,
                    "date" => now(),
                    "order" => $countRepayment + 1,
                    "amount" => $loan->monthlyBill,
                ]);
            });

            return BaseResponse::success(
                "jancok",
                ["loan" => $loan, "repayment" => $repayment,],
            );
        } catch (Exception $error) {
            return BaseResponse::error("Error while repayment loan", 500, $error->getMessage());
        }
    }

    public function show($idLoan, $idRepayment, Request $request)
    {
        try {
            $loan = Loan::with("repayment")->find($idLoan);
            if (!$loan) {
                return BaseResponse::error("Loan data not found", 404, "Loan data not found");
            }
            $repayment = Repayment::find($idRepayment);
            if (!$repayment) {
                return BaseResponse::error("Repayment data not found", 404, "Repayment data not found");
            }

            return BaseResponse::success("Success retrieve repayments data", $repayment);
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving repayments data", 500, $error->getMessage());
        }
    }
}
