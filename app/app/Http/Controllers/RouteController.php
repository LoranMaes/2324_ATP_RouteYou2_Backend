<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Checkpoint;
use App\Models\Participation;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RouteController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/routes/{route}/checkpoints",
     *     summary="Create a checkpoint and add it to a route",
     *     tags={"Routes"},
     *     security={{"Token": {"ORGANISER"} }},
     *     @OA\Parameter(
     *         name="route",
     *         in="path",
     *         required=true,
     *         description="ID of the route to which the checkpoint will be added",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="longitude", type="number", format="double", minimum="-180", maximum="180", example="45.1234"),
     *                 @OA\Property(property="latitude", type="number", format="double", minimum="-90", maximum="90", example="-75.6789"),
     *                 @OA\Property(property="coin", type="number", maximum="1000", example="500")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Checkpoint created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Checkpoint created"),
     *             @OA\Property(
     *                 property="checkpoint",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example="1"),
     *                 @OA\Property(property="longitude", type="number", format="double", example="45.1234"),
     *                 @OA\Property(property="latitude", type="number", format="double", example="-75.6789"),
     *                 @OA\Property(property="coin", type="number", example="500"),
     *                 @OA\Property(property="qr_code", type="string", example="app_url/storage/qr-codes/9dca6322-1f2d-4777-a352-7310fcd2ff10.svg"),
     *                 @OA\Property(property="route_id", type="integer", example="1"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *        response=401,
     *        description="Unauthenticated",
     *        @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="Unauthenticated")
     *        )
     *     ),
     *     @OA\Response(
     *       response=403,
     *       description="Forbidden",
     *       @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="You are not allowed to access this resource.")
     *       )
     *     ),
     *     @OA\Response(
     *        response=404,
     *        description="Route not found",
     *        @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="Route not found")
     *        )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="longitude", type="array", @OA\Items(type="string", example="The longitude field is required.")),
     *                 @OA\Property(property="latitude", type="array", @OA\Items(type="string", example="The latitude field is required.")),
     *                 @OA\Property(property="coin", type="array", @OA\Items(type="string", example="The coin field is required.")
     *             )
     *         )
     *   )
     *  )
     * )
     */
    public function postCheckpoint(Request $request, Route $route)
    {
        $request->validate([
            'longitude' => 'required|numeric|between:-180,180',
            'latitude' => 'required|numeric|between:-90,90',
            'coin' => 'required|numeric|max:1000',
        ]);

        $checkpoint = Checkpoint::firstOrCreate([
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'coin' => $request->coin,
            'route_id' => $route->id
        ]);
        if ($checkpoint->qr_code === null) {
            $qr_code_id = Str::uuid();
            $url = env('APP_URL') . "/api/events/{$route->event->id}/achievements/{$qr_code_id}";

            $qr_code = QrCode::size(300);
            $qr_code->backgroundColor(255, 255, 255, 0);
            $qr_code = $qr_code->generate($url);

            Storage::put("qr-codes/{$qr_code_id}.svg", $qr_code);
            $checkpoint->update([
                'qr_code' => Storage::url("qr-codes/{$qr_code_id}.svg")
            ]);
        }

        $checkpoint->makeVisible('qr_code');

        $participations = $checkpoint->route->event->participations()->get();

        foreach ($participations as $participation) {
            Achievement::firstOrCreate([
                'completed' => false,
                'checkpoint_id' => $checkpoint->id,
                'participation_id' => $participation->id,
            ]);
        }

        return response([
            'message' => 'Checkpoint created',
            'checkpoint' => $checkpoint
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/routes/{route}/checkpoints/{checkpoint}",
     *     summary="Delete a checkpoint from a route",
     *     tags={"Routes"},
     *     security={{"Token": {"ORGANISER"} }},
     *     @OA\Parameter(
     *         name="route",
     *         in="path",
     *         required=true,
     *         description="ID of the route from which the checkpoint will be deleted",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="checkpoint",
     *         in="path",
     *         required=true,
     *         description="ID of the checkpoint to be deleted",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Checkpoint deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Checkpoint deleted successfully")
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
     *         description="Checkpoint or route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Checkpoint or route not found")
     *         )
     *     )
     * )
     */
    public function deleteCheckpoint(Route $route, Checkpoint $checkpoint)
    {
        Storage::delete("qr-codes/" . basename($checkpoint->qr_code));
        $checkpoint->delete();
        return response([
            'message' => 'Checkpoint deleted successfully',
        ], 200);
    }
}
