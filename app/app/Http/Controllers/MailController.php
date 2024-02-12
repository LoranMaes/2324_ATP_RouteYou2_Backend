<?php

namespace App\Http\Controllers;

use App\Mail\InviteUser;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/events/{event}/invite",
     *     summary="Invite users to an event",
     *     tags={"Mail"},
     *     security={{ "Token": {"ORGANISER"} }},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="ID of the event to invite users to",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\RequestBody(
     *     required=true,
     *     description="List of emails to invite",
     *     @OA\JsonContent(
     *         required={"followers"},
     *         @OA\Property(property="followers", type="boolean", example="true"),
     *         @OA\Property(property="emails", type="array", nullable=true,
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="email", type="string", example="test1@example.com"),
     *             ),
     *             example={
     *                 {"email": "test1@example.com"},
     *                 {"email": "test2@example.com"},
     *                 {"email": "test3@example.com"}
     *             }
     *           ),
     *        )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event updated successfully",
     *         @OA\JsonContent(
     *            @OA\Property(property="message", type="string", example="The invitations have been sent successfully.")
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
     *     @OA\Response(
     *        response=404,
     *        description="Event not found",
     *        @OA\JsonContent(
     *           @OA\Property(property="message", type="string", example="Event not found.")
     *        )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={
     *                 "followers": { "The followers field is required." },
     *                 "emails": { "The emails must be an array." }
     *             }),
     *         )
     *     ),
     * )
     */
    public function inviteUsers(Request $request, Event $event)
    {
        $request->validate([
            'emails' => 'nullable|array',
            'emails.*.email' => 'required|string|email:rfc,dns',
            'followers' => 'required|boolean'
        ]);


        if ($request->followers) {
            $followers = $request->user()->organisation->followers()->get();
            foreach($followers as $follower) {
                Mail::to($follower->email)->send(new InviteUser($event, $follower));
            }
        }

        if ($request->filled('emails')) {
            foreach($request->emails as $email) {
                Mail::to($email['email'])->send(new InviteUser($event));
            }
        }

        return response(['message' => 'The invitations have been sent successfully.'], 200);
    }
}
