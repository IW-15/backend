<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\Merchant;
use Exception;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $merchant = $user->merchant;

            return BaseResponse::success("true", ["user" => $user, "merchant" => $merchant]);
        } catch (Exception $error) {
            return BaseResponse::error("Error while check store data", 500, $error->getMessage());
        }
    }
}
