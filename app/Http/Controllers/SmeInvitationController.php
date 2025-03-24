<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\EventInvitation;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SmeInvitationController extends Controller
{
    //
    public function all(Request $request)
    {
        try {
            // Step 2: Get the authenticated user
            $user = $request->user();
            $merchant = $user->merchant; // Get the associated merchant

            // Step 3: Build the query to retrieve invitations
            $query = EventInvitation::where('id_sme', $merchant->id); // Assuming id_sme refers to the merchant

            // Step 5: Retrieve the invitations
            $invitations = $query->with(['event', 'outlet']) // Load related event data
                ->get();

            return BaseResponse::success("Invitations retrieved successfully.", $invitations);
        } catch (ModelNotFoundException $notFoundError) {
            return BaseResponse::error("Data not found", 404, $notFoundError->getMessage());
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving invitations", 500, $error->getMessage());
        }
    }

    public function detail(Request $request, $idInvitation)
    {
        try {
            // Step 2: Get the authenticated user
            $user = $request->user();
            $merchant = $user->merchant; // Get the associated merchant

            // Step 3: Build the query to retrieve the invitation
            $invitation = EventInvitation::where('id', $idInvitation)
                ->where('id_sme', $merchant->id) // Ensure the invitation belongs to the current merchant
                ->with(['event', 'outlet']) // Load related event data
                ->first(); // Use first() to retrieve the single invitation

            // Check if the invitation was found
            if (!$invitation) {
                return BaseResponse::error("Invitation not found or does not belong to the specified merchant.", 404, "Invitation not found");
            }

            return BaseResponse::success("Invitation detail retrieved successfully.", $invitation);
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (ModelNotFoundException $notFoundError) {
            return BaseResponse::error("Data not found", 404, $notFoundError->getMessage());
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving invitation detail", 500, $error->getMessage());
        }
    }


    public function accept(Request $request, $idInvitation)
    {
        try {
            // Step 1: Validate that the invitation exists and belongs to the user
            $user = $request->user(); // Get the authenticated user
            $merchant = $user->merchant; // Access the associated merchant

            // Step 2: Retrieve the invitation
            $invitation = EventInvitation::with(['event', 'outlet'])->where('id', $idInvitation)
                ->where('id_sme', $merchant->id) // Ensure the invitation belongs to the specified SME
                ->first();

            // Check if the invitation was found
            if (!$invitation) {
                return BaseResponse::error("Invitation not found or does not belong to the specified merchant.", 404, "Invitation not found");
            }

            // Step 3: Check the status of the invitation
            if ($invitation->status !== 'pending') {
                return BaseResponse::error("The invitation cannot be accepted because its status is not 'pending'.", 400, "Invalid invitation status");
            }

            // Step 4: Update the status to 'accepted'
            $invitation->status = 'accepted';
            $invitation->save();

            return BaseResponse::success("Invitation accepted successfully.", $invitation);
        } catch (Exception $error) {
            return BaseResponse::error("Error while accepting invitation", 500, $error->getMessage());
        }
    }

    public function reject(Request $request, $idInvitation)
    {
        try {
            // Step 1: Get the authenticated user and related merchant
            $user = $request->user();
            $merchant = $user->merchant; // Access the associated merchant

            // Step 2: Retrieve the invitation
            $invitation = EventInvitation::with(['event', 'outlet'])->where('id', $idInvitation)
                ->where('id_sme', $merchant->id) // Ensure the invitation belongs to the specified SME
                ->first();

            // Step 3: Check if the invitation was found
            if (!$invitation) {
                return BaseResponse::error("Invitation not found or does not belong to the specified merchant.", 404, "Invitation not found");
            }

            // Step 4: Check the status of the invitation
            if ($invitation->status !== 'pending') {
                return BaseResponse::error("The invitation cannot be rejected because its status is not 'pending'.", 400, "Invalid invitation status");
            }

            // Step 5: Update the status to 'rejected'
            $invitation->status = 'rejected';
            $invitation->save();

            return BaseResponse::success("Invitation rejected successfully.", $invitation);
        } catch (Exception $error) {
            return BaseResponse::error("Error while rejecting invitation", 500, $error->getMessage());
        }
    }
}
