<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Badge;
use App\Models\Checkpoint;
use App\Models\Event;
use App\Models\Participation;
use App\Models\Route;
use Google\Cloud\Storage\StorageClient;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mollie\Laravel\Facades\Mollie;
use OpenCage\Geocoder\Geocoder;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/user/events",
     *     summary="Get events from logged in user",
     *     tags={"Events"},
     *     security={{ "Token": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieved events",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The events of the user have been returned successfully"),
     *             @OA\Property(
     *                  property="event",
     *                  type="object",
     *                  example={"id": 1, "title": "Test event", "description": "Test description", "start": "2023-12-27 09:00:00", "end": "2023-12-27 15:00:00",
     *                      "price": "50.00", "max_participant": 100, "city": "City", "zip": 1703, "street": "streetname", "house_number": "37",
     *                      "visible": 1, "image": "app_url/storage/JCRhhv7hD35ARVWgKQGpI4O0ZkpnYJWywJbsPoFN.png", "type": "GENERAL", "organisation_id": 1, "badge_id": 1,
     *                      "created_at": "2023-12-06T14:53:31.000000Z", "updated_at": "2023-12-06T14:53:31.000000Z", "slug": "test-event-1", "status": "UPCOMING", "going_count": 0,
     *                      "participations": {"id": 1, "paid": false, "present": false, "reaction": "GOING", "qr_code": "app_url/storage/qr-codes/0b38d49e-e6db-4846-ae0f-b2a43c533afb.svg", "checkout_url": "https://www.mollie.com/payscreen/select-method/7UhSN1zuXS",
     *                      "carpool": false, "carpool_role": null, "club_name": null, "problem": null, "user_id": 1, "event_id": 1, "badge_id": null},
     *                      "routes": {"id": 1, "routeyou_route_id": 1234567, "event_id": 1, "checkpoints": {"id": 1, "longitude": "140.123685", "latitude": "50.123650",
     *                     "coin": 10, "qr_code": "app_url/storage/qr-codes/0b38d49e-e6db-4846-ae0f-b2a43c533afb.svg", "route_id": 7}}
     *
     *      }
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *     )
     * )
     */
    public function getUserEvents(Request $request)
    {
        $request->validate([
            'paginate' => 'nullable|numeric|min:1'
        ]);

        $paginate = $request->filled('paginate') ? $request->paginate : 6;

        $events = null;

        if ($request->user()->organisation_id !== null) {
            $events = Event::with('routes.checkpoints')->where('organisation_id', $request->user()->organisation_id)->paginate($paginate);
            foreach ($events as $event) {
                $routes = $event->routes;
                foreach ($routes as $route) {
                    $checkpoints = $route->checkpoints;
                    foreach ($checkpoints as $checkpoint) {
                        $checkpoint->makeVisible('qr_code');
                    }
                }
            }
        } else {
            $events = Event::whereHas('participations', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })->with(['participations' => function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            }])->paginate($paginate);
            foreach ($events as $event) {
                $event->participations->where('user_id', $request->user()->id)->makeVisible(['checkout_url', 'qr_code']);
            }
        }

        return response([
            'message' => 'The events of the user have been returned successfully',
            'events' => $events
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/events",
     *     summary="Get events",
     *     tags={"Events"},
     *     @OA\Parameter(
     *          name="title",
     *          in="query",
     *          description="Title to filter events (optional)",
     *          @OA\Schema(type="string", maxLength=45),
     *      ),
     *     @OA\Parameter(
     *          name="organisation_name",
     *          in="query",
     *          description="Organisation name to filter events (optional)",
     *          @OA\Schema(type="string", maxLength=45),
     *     ),
     *     @OA\Parameter(
     *          name="start",
     *          in="query",
     *          description="Start date to filter events (optional)",
     *          @OA\Schema(type="string", format="date-time"),
     *      ),
     *      @OA\Parameter(
     *          name="end",
     *          in="query",
     *          description="End date to filter events (optional)",
     *          @OA\Schema(type="string", format="date-time"),
     *      ),
     *     @OA\Parameter(
     *          name="max_participant",
     *          in="query",
     *          description="Maximum participants to filter events (optional)",
     *          @OA\Schema(type="integer", minimum=1),
     *      ),
     *      @OA\Parameter(
     *          name="price",
     *          in="query",
     *          description="Price to filter events (optional)",
     *          @OA\Schema(type="integer"),
     *      ),
     *      @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="Type to filter events (optional)",
     *          @OA\Schema(type="string", enum={"webinar", "clubevent", "general", "routebuddy"}),
     *      ),
     *     @OA\Parameter(
     *          name="paginate",
     *          in="query",
     *          description="Events on a page (optional)",
     *          @OA\Schema(type="integer", minimum=1),
     *     ),
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Page number (optional)",
     *          @OA\Schema(type="integer", minimum=1),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieved events",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The events of the user have been returned successfully"),
     *             @OA\Property(
     *                 property="events",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     example={
     *                         "id": 1,
     *                         "title": "Test event",
     *                         "description": "Test description",
     *                         "start": "2023-12-27 09:00:00",
     *                         "end": "2023-12-27 15:00:00",
     *                         "price": "50.00",
     *                         "max_participant": 100,
     *                         "city": "City",
     *                         "zip": 1703,
     *                         "street": "streetname",
     *                         "house_number": "37",
     *                         "visible": 1,
     *                         "image": "app_url/storage/JCRhhv7hD35ARVWgKQGpI4O0ZkpnYJWywJbsPoFN.png",
     *                         "type": "GENERAL",
     *                         "organisation_id": 1,
     *                         "badge_id": 1,
     *                         "created_at": "2023-12-06T14:53:31.000000Z",
     *                         "updated_at": "2023-12-06T14:53:31.000000Z",
     *                         "slug": "test-event-1",
     *                         "status": "UPCOMING",
     *                         "going_count": 0,
     *                         "routes": {
     *                             "id": 1,
     *                             "routeyou_route_id": 1234567,
     *                             "event_id": 1,
     *                             "checkpoints": {
     *                                 "id": 1,
     *                                 "longitude": "140.123685",
     *                                 "latitude": "50.123650",
     *                                 "coin": 10,
     *                                 "qr_code": "app_url/storage/qr-codes/0b38d49e-e6db-4846-ae0f-b2a43c533afb.svg",
     *                                 "route_id": 7
     *                             }
     *                         }
     *                     }
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={
     *                 "title": {"The title must be a string."},
     *                 "organisation_name": {"The organisation name must be a string."},
     *                 "start": {"The start must be a date."},
     *                 "end": {"The end must be a date."},
     *                 "max_participant": {"The max participant must be a number."},
     *                 "price": {"The price must be a number."},
     *                 "type": {"The type must be a string."},
     *                 "top_right_lat": {"The top right lat field is required when top right long, bottom left lat, bottom left long are present."},
     *                 "top_right_long": {"The top right long field is required when top right lat, bottom left lat, bottom left long are present."},
     *                 "bottom_left_lat": {"The bottom left lat field is required when top right lat, top right long, bottom left long are present."},
     *                 "bottom_left_long": {"The bottom left long field is required when top right lat, top right long, bottom left lat are present."},
     *                 "paginate": {"The paginate must be a number."},
     *             })
     *         )
     *     ),
     * )
     */
    public function getEvents(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:45',
            'organisation_name' => 'nullable|string|max:45',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
            'max_participant' => 'nullable|numeric|min:1',
            'price' => 'nullable|numeric',
            'type' => 'nullable|string|in:webinar,clubevent,general,routebuddy',
            'top_right_lat' => 'required_with:top_right_long,bottom_left_lat,bottom_left_long|numeric|between:-90,90',
            'top_right_long' => 'required_with:top_right_lat,bottom_left_lat,bottom_left_long|numeric|between:-180,180',
            'bottom_left_lat' => 'required_with:top_right_lat,top_right_long,bottom_left_long|numeric|between:-90,90',
            'bottom_left_long' => 'required_with:top_right_lat,top_right_long,bottom_left_lat|numeric|between:-180,180',
            'paginate' => 'nullable|numeric|min:1'
        ]);

        $events = Event::query();
        $paginate = $request->filled('paginate') ? $request->paginate : 6;

        $events->orderBy('start', 'asc');
        $events->orderBy('title', 'asc');

        if ($request->filled('title')) {
            $events->where('title', 'LIKE', '%' . $request->title . '%');
        }
        if ($request->filled('organisation_name')) {
            $events->whereHas('organisation', function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->organisation_name . '%');
            });
        }
        if ($request->filled('start')) {
            $events->where('start', '>=', $request->start);
        }
        if ($request->filled('end')) {
            $events->where('end', '<=', $request->end);
        }
        if ($request->isNotFilled('start') && $request->isNotFilled('end')) {
            $events->where('start', '>=', now());
        }
        if ($request->filled('max_participant')) {
            $events->where('max_participant', '<=', $request->max_participant);
        }
        if ($request->filled('price')) {
            $events->where('price', '<=', $request->price);
        }
        if ($request->filled('type')) {
            $events->where('type', '=', $request->type);
        }
        if ($request->filled('top_right_lat')) {
            $events->whereBetween('latitude', [$request->bottom_left_lat, $request->top_right_lat])
                ->whereBetween('longitude', [$request->bottom_left_long, $request->top_right_long]);
        }

        return response([
            'message' => 'The events have been returned successfully',
            'events' => $events->with(['routes.checkpoints.achievements', 'badge', 'participations'])->paginate($paginate)
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/events/{event}",
     *     summary="Get a specific event",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="ID of the event to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieved event",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The event has been returned successfully"),
     *             @OA\Property(
     *                 property="event",
     *                 type="object",
     *                 example={
     *                      "id": 1,
     *                      "title": "Test event",
     *                          "description": "Test description",
     *                          "start": "2023-12-27 09:00:00",
     *                          "end": "2023-12-27 15:00:00",
     *                          "price": "50.00",
     *                          "max_participant": 100,
     *                          "city": "City",
     *                          "zip": 1703,
     *                          "street": "streetname",
     *                          "house_number": "37",
     *                          "visible": 1,
     *                          "image": "app_url/storage/JCRhhv7hD35ARVWgKQGpI4O0ZkpnYJWywJbsPoFN.png",
     *                          "type": "GENERAL",
     *                          "organisation_id": 1,
     *                          "badge_id": 1,
     *                          "created_at": "2023-12-06T14:53:31.000000Z",
     *                          "updated_at": "2023-12-06T14:53:31.000000Z",
     *                          "slug": "test-event-1",
     *                          "status": "UPCOMING",
     *                          "going_count": 0,
     *                          "routes": {
     *                              "id": 1,
     *                              "routeyou_route_id": 1234567,
     *                              "event_id": 1,
     *                              "checkpoints": {
     *                                  "id": 1,
     *                                  "longitude": "140.123685",
     *                                  "latitude": "50.123650",
     *                                  "coin": 10,
     *                                  "qr_code": "app_url/storage/qr-codes/0b38d49e-e6db-4846-ae0f-b2a43c533afb.svg",
     *                                  "route_id": 7,
     *                                  "achievements": {
     *                                      "id": 1,
     *                                      "completed": false,
     *                                      "checkpoint_id": 1,
     *                                      "participation_id": 1,
     *                                   }
     *                                }
     *                           },
     *                           "badge": {
     *                                  "id": 1,
     *                                  "name": "Badge Name",
     *                                  "description": "Badge Description",
     *                                  "image": "app_url/storage/JCRhhv7hD35ARVWgKQGpI4O0ZkpnYJWywJbsPoFN.png"
     *                           },
     *                           "participations": {
     *                                  "id": 1,
     *                                  "paid": false,
     *                                  "present": false,
     *                                  "reaction": "GOING",
     *                                  "carpool": false,
     *                                  "carpool_role": null,
     *                                  "club_name": null,
     *                                  "problem": null,
     *                                  "user_id": 1,
     *                                  "event_id": 1,
     *                                  "badge_id": null
     *                           }
     *                      }
     *                  )
     *              )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event not found.")
     *         )
     *     ),
     * )
     */
    public function getEvent(Event $event)
    {
        return response([
            'message' => 'The event has been returned successfully',
            'event' => $event->load(['routes.checkpoints.achievements', 'badge', 'participations.user:id,first_name,last_name,email,phone_number'])
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/events",
     *     summary="Create a new event",
     *     tags={"Events"},
     *     security={{ "Token": {"ORGANISER"} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Event data",
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", maxLength=45, example="Event Title"),
     *             @OA\Property(property="description", type="string", maxLength=255, example="Event Description"),
     *             @OA\Property(property="start", type="string", format="date-time", example="2023-12-27 09:00:00"),
     *             @OA\Property(property="end", type="string", format="date-time", example="2023-12-27 15:00:00"),
     *             @OA\Property(property="price", type="number", example=50.00),
     *             @OA\Property(property="max_participant", type="integer", example=100),
     *             @OA\Property(property="city", type="string", maxLength=45, example="City Name"),
     *             @OA\Property(property="zip", type="integer", example=1703),
     *             @OA\Property(property="street", type="string", maxLength=45, example="Street Name"),
     *             @OA\Property(property="house_number", type="string", example="37"),
     *             @OA\Property(property="visible", type="boolean", example=true),
     *             @OA\Property(property="event_image", type="string", format="binary", description="Event image file (PNG, JPG, JPEG)"),
     *             @OA\Property(property="type", type="string", enum={"WEBINAR", "CLUBEVENT", "GENERAL", "ROUTEBUDDY"}, example="GENERAL"),
     *             @OA\Property(property="badge_name", type="string", maxLength=45, example="Badge Name"),
     *             @OA\Property(property="badge_description", type="string", maxLength=255, example="Badge Description"),
     *             @OA\Property(property="badge_image", type="string", format="binary", description="Badge image file (PNG, JPG, JPEG)"),
     *             @OA\Property(property="routes", type="array", nullable=true,
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Event created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The event has been created successfully"),
     *             @OA\Property(
     *                 property="event",
     *                 type="object",
     *                 example={
     *                     "id": 1,
     *                     "title": "Test event",
     *                     "description": "Test description",
     *                     "start": "2023-12-27 09:00:00",
     *                     "end": "2023-12-27 15:00:00",
     *                     "price": "50.00",
     *                     "max_participant": 100,
     *                     "city": "City",
     *                     "zip": 1703,
     *                     "street": "streetname",
     *                     "house_number": "37",
     *                     "visible": 1,
     *                     "image": "app_url/storage/JCRhhv7hD35ARVWgKQGpI4O0ZkpnYJWywJbsPoFN.png",
     *                     "type": "GENERAL",
     *                     "organisation_id": 1,
     *                     "badge_id": 1,
     *                     "created_at": "2023-12-06T14:53:31.000000Z",
     *                     "updated_at": "2023-12-06T14:53:31.000000Z",
     *                     "slug": "test-event-1",
     *                     "status": "UPCOMING",
     *                     "going_count": 0,
     *                     "routes": {
     *                         "id": 1,
     *                         "routeyou_route_id": 1234567,
     *                         "event_id": 1,
     *                         "checkpoints": { }
     *                     },
     *                     "badge": {
     *                         "id": 1,
     *                         "name": "Badge Name",
     *                         "description": "Badge Description",
     *                         "image": "app_url/storage/JCRhhv7hD35ARVWgKQGpI4O0ZkpnYJWywJbsPoFN.png"
     *                     },
     *                     "participations": { }
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Forbidden.")
     *          )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="The given address is not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given address is not found."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={
     *                 "title": {"The title field is required."},
     *                 "start": {"The start field is required."},
     *                  "end": {"The end field is required."},
     *                  "price": {"The price field is required."},
     *                  "max_participant": {"The max participant field is required."},
     *                  "city": {"The city field is required."},
     *                  "zip": {"The zip field is required."},
     *                  "street": {"The street field is required."},
     *                  "house_number": {"The house number field is required."},
     *                  "visible": {"The visible field is required."},
     *                  "event_image": {"The event image field is required."},
     *                  "type": {"The type field is required."},
     *                  "badge_name": {"The badge name field is required."},
     *                  "badge_description": {"The badge description field is required."},
     *                  "badge_image": {"The badge image field is required."},
     *                  "routes": {"The routes field is required."},
     *                  "routes.0.id": {"The route field is required."},
     *             })
     *         )
     *     ),
     * )
     */
    public function postEvent(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:45',
            'description' => 'required|string|max:255',
            'start' => 'required|date|after:now',
            'end' => 'required|date|after:start',
            'price' => 'required|numeric',
            'max_participant' => 'required|numeric|min:1',
            'city' => 'required|string|max:45',
            'zip' => 'required|numeric',
            'street' => 'required|string|max:45',
            'house_number' => 'required|string',
            'visible' => 'required|boolean',
            'event_image' => 'required|mimes:png,jpg,jpeg',
            'type' => 'required|string|in:WEBINAR,CLUBEVENT,GENERAL,ROUTEBUDDY',
            'badge_name' => 'required|string|max:45',
            'badge_description' => 'required|string|max:255',
            'badge_image' => 'required|mimes:png,jpg,jpeg',
            'routes' => 'nullable|array',
            'routes.*.id' => 'required|integer|min:1'
        ]);

        $badge_image_path = Storage::url($request->file('badge_image')->store('/'));
        $event_image_path = Storage::url($request->file('event_image')->store('/'));

        $badge = Badge::create([
            'name' => $request->badge_name,
            'description' => $request->badge_description,
            'image' => $badge_image_path
        ]);

        $geocoder = new Geocoder(env('OPENCAGE_GEOCODE_API_KEY'));
        $address = $request->street . ' ' . $request->house_number . ', ' . $request->zip . ' ' . $request->city;
        $result = $geocoder->geocode($address);

        if (empty($result['results'])) {
            return response([
                'message' => 'The given address is not found.',
            ], 404);
        }

        $event = Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'start' => $request->start,
            'end' => $request->end,
            'price' => $request->price,
            'max_participant' => $request->max_participant,
            'city' => $request->city,
            'zip' => $request->zip,
            'street' => $request->street,
            'house_number' => $request->house_number,
            'visible' => $request->visible,
            'image' => $event_image_path,
            'type' => $request->type,
            'latitude' => $result['results'][0]['geometry']['lat'],
            'longitude' => $result['results'][0]['geometry']['lng'],
            'organisation_id' => $request->user()->organisation_id,
            'badge_id' => $badge->id
        ]);

        if ($request->filled('routes')) {
            foreach ($request->routes as $route) {
                Route::firstOrCreate([
                    'routeyou_route_id' => $route['id'],
                    'event_id' => $event->id,
                ]);
            }
        }

        return response([
            'message' => 'The event has been created successfully',
            'event' => $event->load(['routes.checkpoints.achievements', 'badge', 'participations'])
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/events/{event}/participate",
     *     summary="Register participation for an event",
     *     tags={"Events"},
     *     security={{ "Token": {"USER"} }},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="ID of the event to participate in event",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Participation data",
     *         @OA\JsonContent(
     *             @OA\Property(property="reaction", type="string", enum={"GOING", "INTERESTED", "ABSENT"}, example="GOING"),
     *             @OA\Property(property="carpool", type="boolean", example=true),
     *             @OA\Property(property="carpool_role", type="string", enum={"DRIVER", "PASSENGER"}, example="DRIVER"),
     *             @OA\Property(property="club_name", type="string", maxLength=45, nullable=true, example="Club Name"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Participation registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Your participation was successfully registered"),
     *             @OA\Property(
     *                 property="participation",
     *                 type="object",
     *                 example={
     *                     "id": 1,
     *                     "paid": false,
     *                     "present": false,
     *                     "reaction": "GOING",
     *                     "qr_code": "app_url/storage/qr-codes/0b38d49e-e6db-4846-ae0f-b2a43c533afb.svg",
     *                     "checkout_url": "https://www.mollie.com/payscreen/select-method/7UhSN1zuXS",
     *                     "carpool": true,
     *                     "carpool_role": "DRIVER",
     *                     "club_name": "Club Name",
     *                     "problem": null,
     *                     "user_id": 1,
     *                     "event_id": 1,
     *                     "badge_id": null
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *        response=403,
     *        description="Forbidden",
     *        @OA\JsonContent(
     *           @OA\Property(property="message", type="string", example="You are not allowed to access this resource.")
     *        )
     *      ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={
     *                 "reaction": {"The reaction field is required."},
     *                 "carpool": {"The carpool field is required."},
     *                 "carpool_role": {"The carpool role field is required when carpool is true."},
     *                 "club_name": {"The club name field is required when carpool is true."},
     *             })
     *         )
     *     ),
     * )
     */
    public function participate(Request $request, Event $event)
    {
        $request->validate([
            'reaction' => 'required|string|in:GOING,INTERESTED,ABSENT',
            'carpool' => 'required|boolean',
            'carpool_role' => 'required_if:carpool,true|string|in:DRIVER,PASSENGER',
            'club_name' => 'nullable|string|max:45'
        ]);

        $payment = null;
        if ($event->price !== "0.00") {
            $payment = Mollie::api()->payments->create([
                "amount" => [
                    "currency" => "EUR",
                    "value" => $event->price
                ],
                "description" => $event->title,
                "redirectUrl" => env('FRONTEND_BASE_URL') . "/" . $event->slug,
                "webhookUrl" => env('WEBHOOK_URL'),
                "metadata" => [
                    "event_id" => $event->id,
                    "user_id" => $request->user()->id,
                ]
            ]);
        }

        $qr_code_id = Str::uuid();
        $url = env('APP_URL') . "/api/events/{$event->id}/participate/{$qr_code_id}";
        $qr_code = QrCode::size(300);
        $qr_code->backgroundColor(255, 255, 255, 0);
        $qr_code = $qr_code->generate($url);
        Storage::put("qr-codes/{$qr_code_id}.svg", $qr_code);

        $participation = Participation::create([
            'paid' => false,
            'present' => false,
            'reaction' => $request->reaction,
            'qr_code' => Storage::url("qr-codes/{$qr_code_id}.svg"),
            'checkout_url' => $payment ? $payment->getCheckoutUrl() : null,
            'carpool' => $request->carpool,
            'carpool_role' => $request->carpool_role,
            'club_name' => $request->club_name,
            'problem' => null,
            'user_id' => $request->user()->id,
            'event_id' => $event->id,
            'badge_id' => null,
        ]);

        $routes = $event->routes;
        foreach ($routes as $route) {
            $checkpoints = $route->checkpoints;
            foreach ($checkpoints as $checkpoint) {
                Achievement::create([
                    'completed' => false,
                    'checkpoint_id' => $checkpoint->id,
                    'participation_id' => $participation->id
                ]);
            }
        }

        $participation->makeVisible(['checkout_url', 'qr_code']);

        return response([
            'message' => 'Your participation was successfully registered',
            'participation' => $participation
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/events/{event}/participate",
     *     summary="Update user participation for an event",
     *     tags={"Events"},
     *     security={{ "Token": {"USER"} }},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="ID of the event to update participation",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Updated Participation data",
     *         @OA\JsonContent(
     *             @OA\Property(property="reaction", type="string", enum={"GOING", "INTERESTED", "ABSENT"}, example="GOING"),
     *             @OA\Property(property="carpool", type="boolean", example=true),
     *             @OA\Property(property="carpool_role", type="string", enum={"DRIVER", "PASSENGER"}, example="DRIVER"),
     *             @OA\Property(property="club_name", type="string", maxLength=45, nullable=true, example="Club Name"),
     *             @OA\Property(property="problem", type="string", maxLength=255, nullable=true, example="Problem description"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Participation updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Your participation has been updated successfully."),
     *             @OA\Property(
     *                 property="participation",
     *                 type="object",
     *                 example={
     *                     "id": 1,
     *                     "paid": false,
     *                     "present": false,
     *                     "reaction": "GOING",
     *                     "qr_code": "app_url/storage/qr-codes/0b38d49e-e6db-4846-ae0f-b2a43c533afb.svg",
     *                     "checkout_url": "https://www.mollie.com/payscreen/select-method/7UhSN1zuXS",
     *                     "carpool": true,
     *                     "carpool_role": "DRIVER",
     *                     "club_name": "Club Name",
     *                     "problem": "Problem description",
     *                     "user_id": 1,
     *                     "event_id": 1,
     *                     "badge_id": null
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *        response=403,
     *        description="Forbidden",
     *        @OA\JsonContent(
     *           @OA\Property(property="message", type="string", example="You are not allowed to access this resource.")
     *        )
     *      ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(
     *           @OA\Property(property="message", type="string", example="Event not found")
     *         )
     *      ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={
     *                 "reaction": {"The reaction field is required."},
     *                 "carpool": {"The carpool field is required."},
     *                 "carpool_role": {"The carpool role field is required when carpool is true."},
     *                 "club_name": {"The club name field is required when carpool is true."},
     *                 "problem": {"The problem field is required when present is false."},
     *             })
     *         )
     *     ),
     * )
     */
    public function updateParticipation(Request $request, Event $event)
    {
        $request->validate([
            'reaction' => 'required|string|in:GOING,INTERESTED,ABSENT',
            'carpool' => 'required|boolean',
            'carpool_role' => 'required_if:carpool,true|string|in:DRIVER,PASSENGER',
            'club_name' => 'nullable|string|max:45',
            'problem' => 'nullable|string|max:255'
        ]);

        $participation = Participation::where('user_id', $request->user()->id)->where('event_id', $event->id)->first();

        $participation->update([
            'reaction' => $request->reaction,
            'carpool' => $request->carpool,
            'carpool_role' => $request->carpool_role,
            'club_name' => $request->club_name,
            'problem' => $request->problem
        ]);

        $participation->makeVisible(['checkout_url', 'qr_code']);

        return response([
            'message' => 'Your participation has been updated successfully.',
            'participation' => $participation
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/events/{event}/participate/{qrcode}",
     *     summary="Set a participant present",
     *     tags={"Events"},
     *     security={{ "Token": {"ORGANISER"} }},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="ID of the event",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *         name="qrcode",
     *         in="path",
     *         description="QR code of the participation",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Participant set present successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The participant has been set present successfully."),
     *             @OA\Property(
     *                 property="participation",
     *                 type="object",
     *                 example={
     *                     "id": 1,
     *                     "paid": false,
     *                     "present": true,
     *                     "reaction": "GOING",
     *                     "carpool": true,
     *                     "carpool_role": "DRIVER",
     *                     "club_name": "Club Name",
     *                     "problem": null,
     *                     "user_id": 1,
     *                     "event_id": 1,
     *                     "badge_id": null
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You are not allowed to access this resource.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The checkpoint or participation was not found.")
     *         )
     *     ),
     * )
     */
    public function setParticipantPresent(Request $request, Event $event, $qrcode)
    {
        $participation = Participation::where('qr_code', env('APP_URL') . "/storage/qr-codes/" . $qrcode . ".svg")->firstOrFail();

        $participation->update([
            'present' => true
        ]);

        return response([
            'message' => 'The participant has been set present successfully.',
            'participation' => $participation
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/events/{event}/achievements/{qrcode}",
     *     summary="Complete achievement for a checkpoint",
     *     tags={"Events"},
     *     security={{ "Token": {"USER"} }},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="ID of the event",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *         name="qrcode",
     *         in="path",
     *         description="QR code of the checkpoint",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Achievement completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The achievement has been updated successfully."),
     *             @OA\Property(
     *                 property="achievement",
     *                 type="object",
     *                 example={
     *                     "id": 1,
     *                     "completed": true,
     *                     "checkpoint_id": 1,
     *                     "participation_id": 1,
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You are not allowed to access this resource.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The checkpoint or participation was not found.")
     *         )
     *     ),
     * )
     */
    public function completeAchievement(Request $request, Event $event, $qrcode)
    {
        $checkpoint = Checkpoint::where('qr_code', env('APP_URL') . "/storage/qr-codes/" . $qrcode . ".svg")->firstOrFail();
        $participation = Participation::where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->first();

        $achievement = Achievement::where('checkpoint_id', $checkpoint->id)
            ->where('participation_id', $participation->id)
            ->first();

        $achievement->update([
            'completed' => true
        ]);

        return response([
            'message' => 'The achievement has been updated successfully.',
            'achievement' => $achievement
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/events/{event}",
     *     summary="Update an event",
     *     tags={"Events"},
     *     security={{ "Token": {"ORGANISER"} }},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="ID of the event to update",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Updated Event data",
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", maxLength=45, example=" Event Title"),
     *             @OA\Property(property="description", type="string", maxLength=255, example="Updated Event Description"),
     *             @OA\Property(property="start", type="string", format="date-time", example="2023-12-27T09:00:00"),
     *             @OA\Property(property="end", type="string", format="date-time", example="2023-12-27T15:00:00"),
     *             @OA\Property(property="price", type="numeric", example=50.00),
     *             @OA\Property(property="max_participant", type="integer", example=100),
     *             @OA\Property(property="city", type="string", maxLength=45, example="Updated City"),
     *             @OA\Property(property="zip", type="integer", example=1703),
     *             @OA\Property(property="street", type="string", maxLength=45, example="Updated Street"),
     *             @OA\Property(property="house_number", type="string", example="Updated House Number"),
     *             @OA\Property(property="visible", type="boolean", example=true),
     *             @OA\Property(property="event_image", type="string", format="uri", nullable=true, example="https://example.com/path/to/image.jpg"),
     *             @OA\Property(property="type", type="string", enum={"webinar", "clubevent", "general", "routebuddy"}, example="webinar"),
     *             @OA\Property(property="badge_name", type="string", maxLength=45, example="Updated Badge Name"),
     *             @OA\Property(property="badge_description", type="string", maxLength=255, example="Updated Badge Description"),
     *             @OA\Property(property="badge_image", type="string", format="uri", nullable=true, example="https://example.com/path/to/badge_image.jpg"),
     *             @OA\Property(property="routes", type="array", nullable=true, @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *             )),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The event has been updated successfully."),
     *             @OA\Property(
     *                 property="event",
     *                 type="object",
     *                 example={
     *                     "id": 1,
     *                     "title": "Updated Event Title",
     *                     "description": "Updated Event Description",
     *                     "start": "2023-12-27 09:00:00",
     *                      "end": "2023-12-27 15:00:00",
     *                      "price": "50.00",
     *                      "max_participant": 100,
     *                      "city": "City",
     *                      "zip": 1703,
     *                      "street": "streetname",
     *                      "house_number": "37",
     *                      "visible": 1,
     *                      "image": "app_url/storage/JCRhhv7hD35ARVWgKQGpI4O0ZkpnYJWywJbsPoFN.png",
     *                      "type": "GENERAL",
     *                      "organisation_id": 1,
     *                      "badge_id": 1,
     *                      "created_at": "2023-12-06T14:53:31.000000Z",
     *                      "updated_at": "2023-12-06T14:53:31.000000Z",
     *                      "slug": "test-event-1",
     *                      "status": "UPCOMING",
     *                      "going_count": 0,
     *                      "routes": {
     *                          "id": 1,
     *                          "routeyou_route_id": 1234567,
     *                          "event_id": 1,
     *                          "checkpoints": { }
     *                      },
     *                      "badge": {
     *                          "id": 1,
     *                          "name": "Badge Name",
     *                          "description": "Badge Description",
     *                          "image": "app_url/storage/JCRhhv7hD35ARVWgKQGpI4O0ZkpnYJWywJbsPoFN.png"
     *                      },
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *           response=403,
     *           description="Forbidden",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Forbidden.")
     *           )
     *      ),
     *      @OA\Response(
     *           response=404,
     *           description="Not Found",
     *           @OA\JsonContent(
     *               @OA\Property(property="message(1)", type="string", example="No query results for model Event."),
     *               @OA\Property(property="message(2)", type="string", example="The given address is not found."),
     *           )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={
     *                 "title": {"The title field is required."},
     *                  "start": {"The start field is required."},
     *                   "end": {"The end field is required."},
     *                   "price": {"The price field is required."},
     *                   "max_participant": {"The max participant field is required."},
     *                   "city": {"The city field is required."},
     *                   "zip": {"The zip field is required."},
     *                   "street": {"The street field is required."},
     *                   "house_number": {"The house number field is required."},
     *                   "visible": {"The visible field is required."},
     *                   "event_image": {"The event image field is required."},
     *                   "type": {"The type field is required."},
     *                   "badge_name": {"The badge name field is required."},
     *                   "badge_description": {"The badge description field is required."},
     *                   "badge_image": {"The badge image field is required."},
     *                   "routes": {"The routes field is required."},
     *                   "routes.0.id": {"The route field is required."},
     *             })
     *         )
     *     ),
     * )
     */
    public function updateEvent(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required|string|max:45',
            'description' => 'required|string|max:255',
            'start' => 'required|date|after:now',
            'end' => 'required|date|after:start',
            'price' => 'required|numeric',
            'max_participant' => 'required|numeric|min:1',
            'city' => 'required|string|max:45',
            'zip' => 'required|numeric',
            'street' => 'required|string|max:45',
            'house_number' => 'required|string',
            'visible' => 'required|boolean',
            'event_image' => 'nullable|mimes:png,jpg,jpeg',
            'type' => 'required|string|in:webinar,clubevent,general,routebuddy',
            'badge_name' => 'required|string|max:45',
            'badge_description' => 'required|string|max:255',
            'badge_image' => 'nullable|mimes:png,jpg,jpeg',
            'routes' => 'nullable|array',
            'routes.*.id' => 'required|integer|min:1'
        ]);

        $event_image_path = $event->image;
        if ($request->file('event_image')) {
            Storage::delete(basename($event->image));
            $event_image_path = Storage::url($request->file('event_image')->store('/'));
        }

        $badge_image_path = $event->badge->image;
        if ($request->file('badge_image')) {
            Storage::delete(basename($event->badge->image));
            $badge_image_path = Storage::url($request->file('badge_image')->store('/'));
        }

        $geocoder = new Geocoder(env('OPENCAGE_GEOCODE_API_KEY'));
        $address = $request->street . ' ' . $request->house_number . ', ' . $request->zip . ' ' . $request->city;
        $result = $geocoder->geocode($address);

        if (empty($result['results'])) {
            return response([
                'message' => 'The given address is not found.',
            ], 404);
        }

        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'start' => $request->start,
            'end' => $request->end,
            'price' => $request->price,
            'max_participant' => $request->max_participant,
            'city' => $request->city,
            'zip' => $request->zip,
            'street' => $request->street,
            'house_number' => $request->house_number,
            'visible' => $request->visible,
            'image' => $event_image_path,
            'type' => $request->type,
            'latitude' => $result['results'][0]['geometry']['lat'],
            'longitude' => $result['results'][0]['geometry']['lng'],
        ]);

        $badge = Badge::where('id', $event->badge_id)->first();
        $badge->update([
            'name' => $request->badge_name,
            'description' => $request->badge_description,
            'image' => $badge_image_path
        ]);

        if ($request->filled('routes')) {
            foreach ($request->routes as $route) {
                Route::firstOrCreate([
                    'routeyou_route_id' => $route['id'],
                    'event_id' => $event->id,
                ]);
            }
        }

        return response([
            'message' => 'The event has been updated successfully',
            'event' => $event->load(['routes.checkpoints.achievements', 'badge', 'participations'])
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/events/{event}",
     *     summary="Delete a event",
     *     tags={"Events"},
     *     security={{"Token": {"ORGANISER"} }},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         required=true,
     *         description="ID of the event will be deleted",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *       response=401,
     *       description="Unauthenticated",
     *       @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="Unauthenticated")
     *       )
     *     ),
     *     @OA\Response(
     *       response=403,
     *       description="Forbidden",
     *       @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="You are not allowed to access this resource.")
     *       )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event not found")
     *         )
     *     )
     * )
     */
    public function deleteEvent(Event $event)
    {
        Storage::delete(basename($event->badge->image));
        Storage::delete(basename($event->image));
        $event->badge->delete();
        $event->delete();
        return response([
            'message' => 'Deleted event successfully'
        ], 200);
    }
}
