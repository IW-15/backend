<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\Event;
use App\Models\EventRegistered;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use stdClass;

class EoEventController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            $validated = $request->validate([
                "filter" => "nullable|in:progress,coming_soon,draft,all,published",
                "date" => "nullable|in:asc,desc",
                "group" => "nullable|in:date,no"
            ]);

            if (!isset($validated['filter'])) {
                $validated['filter'] = "all";
            }

            if (!isset($validated['date'])) {
                $validated['date'] = "desc";
            }

            if (!isset($validated['group'])) {
                $validated['group'] = "date";
            }

            $user = $request->user();
            $eo = $user->eo;

            // Start building the query
            $query = Event::where("id_eo", $eo->id);

            // Apply filters based on the validated filter value
            switch ($validated['filter']) {
                case 'draft':
                    $query->where("status", "draft");
                    break;
                case 'published':
                    $query->where("status", "published");
                    break;
                case 'progress':
                    $query->where("status", "published")->where("date", '>=', now()); // Include events whose date is today or in the future
                    break;
                case 'coming_soon':
                    $query->where("status", "published")->where("date", '>', now()); // Include events whose date is in the future
                    break;
                case 'all':
                    // No additional conditions needed
                    break;
                default:
                    // Handle unexpected value, though this shouldn't occur due to validation
                    break;
            }

            $events = $query->orderBy("date", $validated['date'])->get();
            switch ($validated['group']) {
                case "date":
                    $events = $events->groupBy("date");
                    break;
                default:
                    break;
            }

            if (sizeof($events) == 0) {
                $events = $validated['group'] == 'date' ? new stdClass() : [];
            }

            return BaseResponse::success("Success retrieving events data", $events);
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving events data", 500, $error->getMessage());
        }
    }

    public function getDetail(Request $request, $idEvent)
    {
        try {
            $user = $request->user();
            $eo = $user->eo;
            $event = Event::findOrFail($idEvent);

            return BaseResponse::success("Success retrieving events data", $event);
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving events data", 500, $error->getMessage());
        }
    }

    public function create(Request $request)
    {
        try {
            $user = $request->user();
            $eo = $user->eo;

            $validator = $request->validate([
                'name' => 'required||max:255',
                'date' => 'required|date',
                'time' => 'required|date_format:H:i',
                'category' => 'required|string|max:255',
                'location' => 'required|string|max:255',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'venue' => 'required|string|max:255',
                'visitorNumber' => 'required|integer',
                'tenantNumber' => 'required|integer',
                'tenantPrice' => 'required|numeric',
                'description' => 'required|max:10000',
                'banner' => 'required|image|mimes:jpeg,jpg,png|max:10240',
                // 'status' => 'nullable|string|max:50',
                // 'pic' => 'nullable|string|max:255',
                // 'picNumber' => 'nullable|integer',
            ]);

            // Process the file upload
            if ($request->hasFile('banner')) {
                $bannerPath = $request->file('banner')->store('events', 'public');
            }
            $validator['banner'] = $bannerPath;
            $validator['id_eo'] = $eo->id;
            $validator['pic'] = $eo->pic;
            $validator['picNumber'] = $eo->picPhone;

            $event = Event::create($validator)->refresh();

            return BaseResponse::success("Event created successfully", $event);
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while creating event data", 500, $error->getMessage());
        }
    }

    public function delete(Request $request, $idEvent)
    {
        try {
            $user = $request->user();
            $eo = $user->eo;

            $event = Event::where("id", $idEvent)->where("id_eo", $eo->id)->get()->first();
            if (!$event) {
                return BaseResponse::error("Event data not found", 404, "Event data not found");
            }

            if ($event->status != "draft") {
                return BaseResponse::error("Event data already published and cannot be deleted", 400, "Event data already published and cannot be deleted");
            }
            $event->delete();

            return BaseResponse::success("Success delete event data", $event);
        } catch (Exception $error) {
            return BaseResponse::error("Error while deleting event data", 500, $error->getMessage());
        }
    }


    public function update(Request $request, $idEvent)
    {
        try {
            $user = $request->user();
            $eo = $user->eo;

            $event = Event::where("id", $idEvent)->where("id_eo", $eo->id)->get()->first();
            if (!$event) {
                return BaseResponse::error("Event data not found", 404, "Event data not found");
            }
            if ($event->status != "draft") {
                return BaseResponse::error("Event data already published and cannot be updated", 400, "Event data already published and  cannot be updated");
            }

            $validator = $request->validate([
                'name' => 'required||max:255',
                'date' => 'required|date',
                'time' => 'required|date_format:H:i',
                'category' => 'required|string|max:255',
                'location' => 'required|string|max:255',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'venue' => 'required|string|max:255',
                'visitorNumber' => 'required|integer',
                'tenantNumber' => 'required|integer',
                'tenantPrice' => 'required|numeric',
                'description' => 'required|max:10000',
                'banner' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
            ]);

            // Process the file upload
            if ($request->hasFile('banner')) {
                $bannerPath = $request->file('banner')->store('events', 'public');
                $validator['banner'] = $bannerPath;
            }
            $validator['id_eo'] = $eo->id;
            $validator['pic'] = $eo->pic;
            $validator['picNumber'] = $eo->picPhone;

            // $event = Event::create($validator);
            $event->update($validator);

            return BaseResponse::success("Event updated successfully", $event);
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while update event data", 500, $error->getMessage());
        }
    }

    public function publish(Request $request, $idEvent)
    {
        try {
            $user = $request->user();
            $eo = $user->eo;

            $event = Event::where("id", $idEvent)->where("id_eo", $eo->id)->get()->first();
            if (!$event) {
                return BaseResponse::error("Event data not found", 404, "Event data not found");
            }
            if ($event->status != "draft") {
                return BaseResponse::error("Event data already published", 400, "Event data already published");
            }

            $event->update(['status' => 'published']);
            return BaseResponse::success("Event data publish successfully", $event);
        } catch (Exception $error) {
            return BaseResponse::error("Error while publish event data", 500, $error->getMessage());
        }
    }

    public function outletRegistered(Request $request, $idEvent)
    {
        try {
            $user = $request->user();
            $eo = $user->eo;

            // Step 1: Check if the event is valid
            $event = Event::where('id', $idEvent)
                ->where('id_eo', $eo->id) // Check that the event belongs to the EO
                ->first(); // Use first() since we expect a single event

            if (!$event) {
                return BaseResponse::error("Event not found or does not belong to the specified EO.", 404, "Event not found");
            }

            // Step 2: Check if the event is published
            if ($event->status !== "published") {
                return BaseResponse::error("Event is not published.", 403, "Event status is not published");
            }

            // Step 3: Get all registered outlets for the event
            $registeredOutlets = EventRegistered::where('id_event', $event->id)->where("status", "received")
                ->with(['outlet', 'event', 'sme']) // Assume you have a relationship defined in the EventRegistered model
                ->get();

            // Return the list of registered outlets
            return BaseResponse::success("Registered outlets retrieved successfully.", $registeredOutlets);
        } catch (ModelNotFoundException $notFoundError) {
            return BaseResponse::error("Data not found", 404, $notFoundError->getMessage());
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving outlet registered data", 500, $error->getMessage());
        }
    }

    public function detailRegistered(Request $request, $idEvent, $idRegistered)
    {
        try {
            $user = $request->user();
            $eo = $user->eo;

            // Step 1: Check if the event is valid
            $event = Event::where('id', $idEvent)
                ->where('id_eo', $eo->id) // Check that the event belongs to the EO
                ->first(); // Use first() since we expect a single event

            if (!$event) {
                return BaseResponse::error("Event not found or does not belong to the specified EO.", 404, "Event not found");
            }

            // Step 2: Check if the event is published
            if ($event->status !== "published") {
                return BaseResponse::error("Event is not published.", 403, "Event status is not published");
            }

            // Step 3: Get all registered outlets for the event
            $registeredOutlets = EventRegistered::with(['outlet', 'event', 'sme'])
                ->find($idRegistered);

            if (!$registeredOutlets) {
                return BaseResponse::error("Event registered data not found", 404, "Event registered data not found");
            }

            // Return the list of registered outlets
            return BaseResponse::success("Registered outlets retrieved successfully.", $registeredOutlets);
        } catch (ModelNotFoundException $notFoundError) {
            return BaseResponse::error("Data not found", 404, $notFoundError->getMessage());
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while retrieving outlet registered data", 500, $error->getMessage());
        }
    }

    public function acceptOutlet(Request $request, $eventId)
    {
        try {
            // Step 1: Validate input
            $validated = $request->validate([
                'outletId' => 'required|array', // Expecting it to be an array
                'outletId.*' => 'exists:outlets,id', // Each outletId must exist in the outlets table
            ]);

            // If a single outlet ID is provided, convert it to an array
            if (isset($request['outletId']) && !is_array($request['outletId'])) {
                $validated['outletId'] = [$request['outletId']];
            }

            // Step 2: Check if the event exists and belongs to the EO
            $user = $request->user();
            $eo = $user->eo;

            $event = Event::where('id', $eventId)
                ->where('id_eo', $eo->id)
                ->first();

            if (!$event) {
                return BaseResponse::error("Event not found or does not belong to the specified EO.", 404, "Event not found");
            }

            // Step 3: Check if the event status is published
            if ($event->status !== "published") {
                return BaseResponse::error("Event is not published.", 403, "Event status is not published");
            }

            // Step 4: Update the status of Event Registered entries
            // Find the registered outlets that need to be updated
            $updatedCount = EventRegistered::where('id_event', $event->id)
                ->where('status', 'received')
                ->whereIn('id_outlet', $validated['outletId'])
                ->update(['status' => 'waiting']);

            if ($updatedCount === 0) {
                $msg = "No outlets updated; ensure outlet statuses are received.";
                return BaseResponse::error($msg, 400, $msg);
            }

            return BaseResponse::success("Outlet registrations updated successfully to 'waiting'.", $updatedCount);
        } catch (ModelNotFoundException $notFoundError) {
            return BaseResponse::error("Data not found", 404, $notFoundError->getMessage());
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while accepting outlet registrations", 500, $error->getMessage());
        }
    }


    public function reject(Request $request, $eventId)
    {
        try {
            // Step 1: Validate input
            $validated = $request->validate([
                'outletId' => 'required|array', // Expecting it to be an array
                'outletId.*' => 'exists:outlets,id', // Each outletId must exist in the outlets table
            ]);

            // If a single outlet ID is provided, convert it to an array
            if (isset($request['outletId']) && !is_array($request['outletId'])) {
                $validated['outletId'] = [$request['outletId']];
            }

            // Step 2: Check if the event exists and belongs to the EO
            $user = $request->user();
            $eo = $user->eo;

            $event = Event::where('id', $eventId)
                ->where('id_eo', $eo->id)
                ->first();

            if (!$event) {
                return BaseResponse::error("Event not found or does not belong to the specified EO.", 404, "Event not found");
            }

            // Step 3: Check if the event status is published
            if ($event->status !== "published") {
                return BaseResponse::error("Event is not published.", 403, "Event status is not published");
            }

            // Step 4: Update the status of Event Registered entries
            $updatedCount = EventRegistered::where('id_event', $event->id)
                ->where('status', 'received')
                ->whereIn('id_outlet', $validated['outletId'])
                ->update(['status' => 'rejected']);

            if ($updatedCount === 0) {
                $msg = "No outlets updated; ensure outlet statuses are received.";
                return BaseResponse::error($msg, 400, $msg);
            }

            return BaseResponse::success("Outlet registrations rejected'.", $updatedCount);
        } catch (ModelNotFoundException $notFoundError) {
            return BaseResponse::error("Data not found", 404, $notFoundError->getMessage());
        } catch (ValidationException $validationError) {
            return BaseResponse::error("Validation error", 422, json_encode($validationError->errors()));
        } catch (Exception $error) {
            return BaseResponse::error("Error while accepting outlet registrations", 500, $error->getMessage());
        }
    }
}
