<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\Event;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EoEventController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            $validated = $request->validate([
                "filter" => "nullable|in:progress,coming_soon,draft,all",
                "date" => "nullable|in:asc,desc"
            ]);

            if (!isset($validated['filter'])) {
                $validated['filter'] = "all";
            }

            if (!isset($validated['date'])) {
                $validated['date'] = "desc";
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

            $events = $query->orderBy("date", $validated['date'])->get()->groupBy("date");
            return BaseResponse::success("Success retrieving events data", $events);
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

            $event = Event::create($validator);

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
}
