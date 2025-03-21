<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\Eo;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\User;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'phone' => 'required|regex:/^08[0-9]{8,10}$/',
                'password' => 'required|string|min:8',
            ]);

            $user = User::where("phone", $validated['phone'])->first();

            if (!$user) {
                return BaseResponse::error("Invalid phone/password", 401, "Invalid phone/password");
            }

            if (!Hash::check($validated['password'], $user->password)) {
                return BaseResponse::error("Incorrect password", 401, "Incorrect password");
            }

            $token = $user->createToken("app_token")->plainTextToken;
            return BaseResponse::success("Login Succeed", ["token" => $token, "type" => $user['type']]);
        } catch (Exception $error) {
            return BaseResponse::error("Invalid phone/password", 500, $error->getMessage());
        }
    }

    public function signup(Request $request)
    {
        try {
            $validated = $request->validate([
                "email" => "required|email|unique:users,email",
                "phone" => "required|regex:/^(?:\+62|0)8\d{2,3}\s?\d{4,5}\s?\d{0,5}$/|unique:users,phone",
                "password" => "required|string|min:8|confirmed",
                "rekening" => "required|string",
                "fullName" => "required|string",
                "firstName" => "required|string",
                "lastName" => "required|string",
                "merchantName" => "required|string",
                "merchantAddress" => "required|string",
                "merchantPhone" => "required|string",
                "merchantEmail" => "required|email"
            ]);

            DB::transaction(function () use ($validated, &$user) {
                $user = User::create([
                    "email" => $validated['email'],
                    "phone" => $validated['phone'],
                    "password" => Hash::make($validated['password']),
                    "rekening" => Crypt::encryptString($validated['rekening']),
                    "fullName" => $validated['fullName'],
                    "firstName" => $validated['firstName'],
                    "lastName" => $validated['lastName'],
                ]);

                $merchant = Merchant::create([
                    "id_user" => $user->id,
                    "name" => $validated['merchantName'],
                    "address" => $validated['merchantAddress'],
                    "phone" => $validated['merchantPhone'],
                    "email" => $validated['merchantEmail'],
                ]);

                $dummyTransactions = json_decode(file_get_contents(storage_path('app/public/dummy_transactions.json')), true);
                $selectedTransactions = $dummyTransactions[array_rand($dummyTransactions)];

                // Replace placeholder with the actual user's ID
                foreach ($selectedTransactions as &$transaction) {
                    $transaction['id_user'] = $user->id;
                    $randomDaysAgo = random_int(0, 2); // Random number from 0 to 3
                    $transaction['date'] = now()->subDays($randomDaysAgo);
                }

                Transaction::insert($selectedTransactions);
            });

            return BaseResponse::success("Signup Successfully", $user);
        } catch (Exception $error) {
            return BaseResponse::error("Error while signup", 500, $error->getMessage());
        }
    }

    public function signupEo(Request $request)
    {
        try {
            $validated = $request->validate([
                "email" => "required|email|unique:users,email",
                "phone" => "required|regex:/^08[0-9]{2,20}$/|unique:users,phone",
                "password" => "required|string|min:8|confirmed",
                "rekening" => "required|string",
                "companyName" => "required",
                "companyNib" => "required",
                "companyPic" => "required",
                "companyPicPhone" => "required|regex:/^08[0-9]{2,20}$/",
                "companyPicEmail" => "required|email",
                "companyAddress" => "required",

            ]);

            DB::transaction(function () use ($validated, &$user) {
                $user = User::create([
                    "email" => $validated['email'],
                    "phone" => $validated['phone'],
                    "password" => Hash::make($validated['password']),
                    "rekening" => Crypt::encryptString($validated['rekening']),
                    "type" => "eo",
                    "fullName" => "",
                    "firstName" => "",
                    "lastName" => "",
                ]);

                $eo = Eo::create([
                    "id_user" => $user->id,
                    "name" => $validated['companyName'],
                    "nib" => $validated['companyNib'],
                    "pic" => $validated['companyPic'],
                    "picPhone" => $validated['companyPicPhone'],
                    "email" => $validated['companyPicEmail'],
                    "address" => $validated['companyAddress'],
                    "document" => "https://cdn.mfadlilhs.site/dpka/activities/banner/1732302890812-ASA.png"
                ]);
            });

            return BaseResponse::success("Signup Successfully", $user);
        } catch (Exception $error) {
            return BaseResponse::error("Error while signup", 500, $error->getMessage());
        }
    }

    public function checkPhone(Request $request)
    {
        try {
            $validated = $request->validate([
                "phone" => "required|regex:/^08[0-9]{2,20}$/|unique:users,phone",
            ]);

            $user = User::where("phone", $validated['phone'])->first();

            if ($user) {
                return BaseResponse::error("Phone number is taken", 500, "Phone number is taken");
            }
            return BaseResponse::success("Phone number is available", []);
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Phone number is taken", 500, $error->getMessage());
        }
    }

    public function checkEmail(Request $request)
    {
        try {
            $validated = $request->validate([
                "email" => "required|email|unique:users,email",
            ]);

            $user = User::where("email", $validated['email'])->first();

            if ($user) {
                return BaseResponse::error("Email address is taken", 500, "Email address is taken");
            }
            return BaseResponse::success("Email address is available", []);
        } catch (Exception $error) {
            return BaseResponse::error("Email address is taken", 500, $error->getMessage());
        }
    }

    public function user(Request $request)
    {
        $user = User::all();
        return BaseResponse::success("Email address is available", $user->first());
    }
}
