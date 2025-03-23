<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\Event;
use App\Models\EventRegistered;
use App\Models\Outlet;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SmeEventController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            $validated = $request->validate([
                "category" => "nullable|array",
                "category.*" => "in:Bazaar,Festival Makanan,Konser,Pameran", // Validate as an array of strings
                "minDate" => "nullable|date",
                "maxDate" => "nullable|date",
                "minPrice" => "nullable|numeric",
                "maxPrice" => "nullable|numeric",
            ]);

            // Start querying from the Event model
            $query = Event::where("status", "published");

            // Apply filters based on the validated input
            if (isset($validated['category'])) {
                $query->whereIn("category", $validated['category']);
            }
            if (isset($validated['minDate'])) {
                $query->where("date", ">=", $validated['minDate']);
            }
            if (isset($validated['maxDate'])) {
                $query->where("date", "<=", $validated['maxDate']);
            }
            if (isset($validated['minPrice'])) {
                $query->where("tenantPrice", ">=", $validated['minPrice']);
            }
            if (isset($validated['maxPrice'])) {
                $query->where("tenantPrice", "<=", $validated['maxPrice']);
            }

            // Get the filtered events
            $events = $query->get();

            return BaseResponse::success("Success retrieve events data", $events);
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieve event data", 500, $error->getMessage());
        }
    }

    public function getDetail($idEvent)
    {
        try {
            $event = Event::findOrFail($idEvent);
            if ($event->status  != "published") {
                return BaseResponse::error("Event not found", 404, "Event not found");
            }

            return BaseResponse::success("Success retrieve event data", $event);
        } catch (ModelNotFoundException $notFoundError) {
            return BaseResponse::error("Event not found", 404, $notFoundError->getMessage());
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieve event data", 500, $error->getMessage());
        }
    }

    public function regist(Request $request, $idEvent)
    {
        try {
            $user = $request->user();
            $sme = $user->merchant;

            // Validate the request
            $validated = $request->validate([
                "outletId" => "required|exists:outlets,id", // Ensure outletId exists in the outlets table
            ]);

            // Check if the outlet belongs to the SME/merchant
            $outlet = Outlet::where("id", $validated['outletId'])
                ->where("id_merchant", $sme->id)
                ->first();

            if (!$outlet) {
                return BaseResponse::error("Outlet data not found or not open to event", 404, "Outlet data not found");
            }
            if (!$outlet->eventOpen) {
                return BaseResponse::error("Please make sure your outlet is open for event", 404, "Outlet data not found");
            }

            // Check if the event exists and is published
            $event = Event::findOrFail($idEvent);
            if ($event->status != "published") {
                return BaseResponse::error("Event not found", 404, "Event not found");
            }

            // Check if the outlet has already registered for the event
            $existingRegistration = EventRegistered::where('id_event', $event->id)
                ->where('id_outlet', $outlet->id)
                ->first();

            if ($existingRegistration) {
                $errorMessage = "Anda telah mendaftarkan outlet ini sebelumnya, mohon tunggu konfirmasi dari EO";
                return BaseResponse::error($errorMessage, 400, $errorMessage);
            }

            $eventRegistered = EventRegistered::create([
                'id_eo' => $event->eo->id,
                'id_event' => $event->id,
                'id_sme' => $sme->id,
                'id_outlet' => $outlet->id,
                'status' => 'received',
                'score' => $sme->score,
                'date' => now(),
            ])->with(["sme", "outlet", "event"])->get();

            return BaseResponse::success("Outlet registered for the event successfully", $eventRegistered);
        } catch (ModelNotFoundException $notFoundError) {
            return BaseResponse::error("Data not found", 404, $notFoundError->getMessage());
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while registering event data", 500, $error->getMessage());
        }
    }

    public function getAllRegis(Request $request)
    {
        try {
            $user = $request->user();
            $sme = $user->merchant;

            // Step 1: Validate the input
            $validated = $request->validate([
                'status' => 'nullable|in:rejected,received,waiting,accepted',
                'date' => 'nullable|in:asc,desc',
            ]);

            // Step 2: Build the query to get all registrations related to the merchant
            $query = EventRegistered::with(["sme", "outlet", "event"])->where('id_sme', $sme->id);

            // Step 3: Apply status filter if provided
            if (isset($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            // Step 4: Apply date ordering if provided
            if (isset($validated['date'])) {
                $query->orderBy('date', $validated['date']);
            } else {
                // Default ordering if no date parameter is provided
                $query->orderBy('date', 'asc'); // Change this to 'desc' if you want a different default
            }

            // Step 5: Retrieve the results
            $eventRegistrations = $query->get();

            return BaseResponse::success("Event registrations retrieved successfully.", $eventRegistrations);
        } catch (ModelNotFoundException $notFoundError) {
            return BaseResponse::error("Data not found", 404, $notFoundError->getMessage());
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving event registrations", 500, $error->getMessage());
        }
    }

    public function getDetailRegis(Request $request, $idRegisteredEvent)
    {
        try {
            $user = $request->user();
            $sme = $user->merchant;

            $eventRegistrations = EventRegistered::with(["sme", "outlet", "event"])->where("id", $idRegisteredEvent)->where("id_sme", $sme->id)->get()->first();
            if (!$eventRegistrations) {
                return BaseResponse::error("Data not found", 404, "Data not found");
            }

            return BaseResponse::success("Event registrations retrieved successfully.", $eventRegistrations);
        } catch (ModelNotFoundException $notFoundError) {
            return BaseResponse::error("Data not found", 404, $notFoundError->getMessage());
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving event registrations", 500, $error->getMessage());
        }
    }

    public function pay(Request $request, $idEventRegistered)
    {
        try {
            $user = $request->user();
            $sme = $user->merchant;

            // Validate the Event Registration
            $registeredEvent = EventRegistered::with(["sme", "outlet", "event"])->where('id', $idEventRegistered)
                ->where('id_sme', $sme->id)
                ->first();

            if (!$registeredEvent) {
                return BaseResponse::error("Event registration not found or does not belong to the merchant.", 404, "Event registration not found");
            }

            // Check the status if it is "waiting"
            if ($registeredEvent->status !== "waiting") {
                return BaseResponse::error("Payment cannot be processed because the status is not 'waiting'.", 400, "Payment cancelled");
            }

            // Update the status to "accepted"
            $registeredEvent->update(["status" => "accepted"]);

            return BaseResponse::success("Payment successful, registration accepted.", $registeredEvent);
        } catch (ModelNotFoundException $notFoundError) {
            return BaseResponse::error("Data not found", 404, $notFoundError->getMessage());
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while processing payment for event registration", 500, $error->getMessage());
        }
    }
}
