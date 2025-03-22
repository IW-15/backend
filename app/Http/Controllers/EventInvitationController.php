<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\EventRegistered;
use App\Models\Outlet;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventInvitationController extends Controller
{
    public function getAll(Request $request) {}

    public function findAvailableOutlets(Request $request, $eventId)
    {
        try {
            // Step 1: Get the current user and their associated EO
            $user = $request->user();
            $eo = $user->eo;

            // Step 2: Validate the event
            $event = Event::where('id', $eventId)
                ->where('id_eo', $eo->id) // Ensure the event belongs to the EO
                ->where('status', 'published') // Check if the event status is published
                ->first();

            if (!$event) {
                return BaseResponse::error("Event not found or not accessible.", 404, "Event is either invalid or not accessible");
            }

            // Step 3: Get the IDs of outlets that are already invited to the event
            $invitedOutlets = EventInvitation::where('id_event', $event->id)
                ->pluck('id_outlet')
                ->toArray(); // Get all invited outlet IDs

            // Step 4: Get all outlets that belong to the EO's merchant that haven't been invited
            $availableOutlets = Outlet::with("revenue")->where('id_merchant', $eo->id) // Assuming EO is linked to a Merchant
                ->whereNotIn('id', $invitedOutlets) // Exclude already invited outlets
                ->get();

            return BaseResponse::success("Available outlets retrieved successfully.", $availableOutlets);
        } catch (ModelNotFoundException $notFoundError) {
            return BaseResponse::error("Data not found", 404, $notFoundError->getMessage());
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving available outlets", 500, $error->getMessage());
        }
    }

    public function getDetail(Request $request, $idEvent, $outletId)
    {
        try {
            // Step 2: Retrieve the outlet by ID
            $outlet = Outlet::with("revenue")->findOrFail($outletId); // Use findOrFail to automatically handle the not found case

            // Step 3: Prepare the outlet data to return
            return BaseResponse::success("Outlet details retrieved successfully.", $outlet);
        } catch (ModelNotFoundException $notFoundError) {
            return BaseResponse::error("Outlet not found", 404, $notFoundError->getMessage());
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving outlet details", 500, $error->getMessage());
        }
    }

    public function invite(Request $request, $idEvent, $idTenant)
    {
        try {
            $user = $request->user(); // Get the authenticated user
            $eo = $user->eo; // Get the associated EO

            // Step 1: Validate the existence of the event
            $event = Event::where('id', $idEvent)
                ->where('id_eo', $eo->id)
                ->where('status', 'published')
                ->first();

            if (!$event) {
                return BaseResponse::error("Event not found or not accessible.", 404, "Event is either invalid or not accessible");
            }

            // Step 2: Check if the tenant (merchant) is already invited to the event
            $existingInvitation = EventInvitation::where('id_event', $event->id)
                ->where('id_outlet', $idTenant) // Assuming id_sme refers to the tenant or merchant
                ->first();

            if ($existingInvitation) {
                return BaseResponse::error("This tenant has already been invited to this event.", 400, "Tenant already invited");
            }

            // Step 3: Check if the tenant is registered for the event
            $registration = EventRegistered::where('id_event', $event->id)
                ->where('id_sme', $idTenant)
                ->first();

            if ($registration) {
                return BaseResponse::error("This tenant is already registered for this event.", 400, "Tenant already registered");
            }

            // Step 4: Check if the tenant's outlet is open (eventOpen must be true)
            $outlet = Outlet::where('id', $idTenant)
                ->where('eventOpen', true) // Ensure the outlet's eventOpen is true
                ->first();

            if (!$outlet) {
                return BaseResponse::error("This tenant's outlet is not open for events.", 400, "Tenant outlet not open");
            }

            $sme = $outlet->merchant;

            // Step 5: Create the invitation
            $invitation = EventInvitation::create([
                'id_eo' => $eo->id,
                'id_event' => $event->id,
                'id_sme' => $sme->id,
                'id_outlet' => $outlet->id, // Assuming you are associating the outlet with the invitation
                'status' => 'pending', // Set initial status to pending
                'date' => now(), // You can set this to the current date or adjust as needed
            ]);

            return BaseResponse::success("Invitation created successfully.", $invitation);
        } catch (ModelNotFoundException $notFoundError) {
            return BaseResponse::error("Data not found", 404, $notFoundError->getMessage());
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while inviting tenant", 500, $error->getMessage());
        }
    }
}
