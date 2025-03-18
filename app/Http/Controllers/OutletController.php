<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\Outlet;
use App\Models\OutletRevenue;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OutletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $outlet = Outlet::where("id_user", $user->id)->with("revenue")->get();

            return BaseResponse::success("Success retrieve outlets data", $outlet);
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving outlets data", 500, $error->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        try {
            $user = $request->user();
            $merchant = $user->merchant;

            $validated = $request->validate([
                "id_revenue" => "required|exists:outlet_revenues,id", // Ensure id_revenue exists in outlet_revenues table
                "name" => "required|string|max:255",
                "type" => "required|string|max:255",
                "phone" => "required|string|max:15", // Adjust max length as needed
                "email" => "required|email|max:255", // Ensure email is unique in outlets
                "address" => "required|string|max:255",
            ]);

            $outlet = Outlet::create(array_merge($validated, [
                "id_user" => $user->id,
                "id_merchant" => $merchant->id,
                "rekening" => $user['rekening']
            ]));

            $outlet->revenue;

            return BaseResponse::success("Success creating outlets data", $outlet);
        } catch (Exception $error) {
            return BaseResponse::error("Error while creating outlets data", 500, $error->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function revenue()
    {
        try {
            $revenue = OutletRevenue::all();

            return BaseResponse::success("Success retrieving outlet revenue types", $revenue);
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving revenue types", 500, $error->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($idOutlet, Outlet $outlet)
    {
        try {
            $outlet = Outlet::with("revenue")->find($idOutlet);
            if (!$outlet) {
                return BaseResponse::error("Outlet not found", 404, "Outlet not found");
            }

            return BaseResponse::success("Success retrieve outlets data", $outlet);
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving outlets data", 500, $error->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Outlet $outlet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $idOutlet)
    {
        try {
            $user = $request->user();
            $merchant = $user->merchant;

            // Find the outlet by ID
            $outlet = Outlet::where('id', $idOutlet)
                ->where('id_user', $user->id)
                ->where('id_merchant', $merchant->id)
                ->first();

            $outlet->revenue;

            // Check if the outlet exists
            if (!$outlet) {
                return BaseResponse::error("Outlet not found", 404, "Outlet not found");
            }

            // Validate incoming request data
            $validated = $request->validate([
                "id_revenue" => "required|exists:outlet_revenues,id", // Ensure id_revenue exists in outlet_revenues table
                "name" => "required|string|max:255",
                "type" => "required|string|max:255",
                "phone" => "required|string|max:15", // Adjust max length as needed
                "address" => "required|string|max:255",
            ]);

            // Update the outlet
            $outlet->update($validated);

            return BaseResponse::success("Success updating outlets data", $outlet);
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while updating outlets data", 500, $error->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request, $idOutlet)
    {
        try {
            $user = $request->user();
            $merchant = $user->merchant;

            // Find the outlet by ID
            $outlet = Outlet::where('id', $idOutlet)
                ->where('id_user', $user->id)
                ->where('id_merchant', $merchant->id)
                ->first();

            // Check if the outlet exists
            if (!$outlet) {
                return BaseResponse::error("Outlet not found", 404, "Outlet not found");
            }

            // Delete the outlet
            $outlet->delete();

            return BaseResponse::success("Outlet deleted successfully", null);
        } catch (Exception $error) {
            return BaseResponse::error("Error while deleting outlet data", 500, $error->getMessage());
        }
    }
}
