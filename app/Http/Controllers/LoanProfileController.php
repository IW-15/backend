<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\LoanProfile;
use Exception;
use Illuminate\Http\Request;

class LoanProfileController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $loanProfile = LoanProfile::where("id_user", $user->id)->get()->first();

            return BaseResponse::success("Success retrieve loan profile data", $loanProfile);
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving loan profile data", 500, $error->getMessage());
        }
    }
}
