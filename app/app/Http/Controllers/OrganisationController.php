<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/organisations",
     *     summary="Get all organisations",
     *     tags={"Organisations"},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved organisations",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The organisations are returned successfully."),
     *             @OA\Property(property="organisations", example="Array with the organisations will be returned here.")
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getOrganisations(Request $request)
    {
        return response([
            'message' => 'The organisations are returned successfully.',
            'organisations' => Organisation::with([
                'organisers:id,first_name,last_name,organisation_id',
                'followers:id,first_name,last_name'
            ])->get(),
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/organisations/{organisation}",
     *     summary="Get a specific organisation",
     *     tags={"Organisations"},
     *     @OA\Parameter(
     *         name="organisation",
     *         in="path",
     *         required=true,
     *         description="ID of the organisation",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved the organisation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The organisation has been returned successfully."),
     *             @OA\Property(property="organisation", type="object", example={"id": 1, "name": "Test Organisation", "description": "This is an example description", "organisers": "Array", "followers": "Array"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Organisation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\Models\Organisation]")
     *         )
     *     )
     * )
     *
     * @param Organisation $organisation
     * @return \Illuminate\Http\Response
     */
    public function getOrganisation(Organisation $organisation)
    {
        return response([
            'message' => 'The organisation has been returned successfully.',
            'organisation' => $organisation->load([
                'organisers:id,first_name,last_name,organisation_id',
                'followers:id,first_name,last_name'
            ]),
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/organisations",
     *     summary="Create a new organisation and link to user",
     *     tags={"Organisations"},
     *     security={{ "Token": {"USER"} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", example="New Organisation"),
     *                 @OA\Property(property="description", type="string", example="A brief description of the organisation")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Organisation created successfully and linked to the user",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The organisation has been created successfully and linked to your account."),
     *             @OA\Property(property="organisation", type="object", example={"id": 1, "name": "New Organisation", "description": "A brief description of the organisation"})
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
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"description": {"The description must be a string."}})
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function postOrganisation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:45',
            'description' => 'nullable|string|max:255',
        ]);

        $organisation = Organisation::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        $request->user()->update([
            'organisation_id' => $organisation->id,
        ]);

        return response([
            'message' => 'The organisation has been created successfully and linked to your account.',
            'organisation' => $organisation,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/organisations/{organisation}/follow",
     *     summary="Follow an organisation",
     *     tags={"Organisations"},
     *     security={{ "Token": {"USER"} }},
     *     @OA\Parameter(
     *         name="organisation",
     *         in="path",
     *         required=true,
     *         description="ID of the organisation to follow",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully followed the organisation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You have successfully followed the organisation."),
     *             @OA\Property(property="organisation", type="object", example={"id": 1, "name": "Test Organisation", "description": "This is an example description"})
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
     *         description="Organisation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\Models\Organisation]")
     *         )
     *     )
     * )
     *
     * @param int $organisation
     * @return \Illuminate\Http\Response
     */
    public function followOrganisation(Request $request, Organisation $organisation)
    {
        $organisation->followers()->attach($request->user()->id);

        return response([
            'message' => 'You have successfully followed the organisation.',
            'organisation' => $organisation,
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/organisations/{organisation}/follow",
     *     summary="Unfollow an organisation",
     *     tags={"Organisations"},
     *     security={{ "Token": {"USER"} }},
     *     @OA\Parameter(
     *         name="organisation",
     *         in="path",
     *         required=true,
     *         description="ID of the organisation to unfollow",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully unfollowed the organisation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You have successfully unfollowed the organisation.")
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
     *         description="Organisation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\Models\Organisation]")
     *         )
     *     )
     * )
     *
     * @param int $organisation
     * @return \Illuminate\Http\Response
     */
    public function unfollowOrganisation(Request $request, Organisation $organisation)
    {
        $organisation->followers()->detach($request->user()->id);

        return response([
            'message' => 'You have successfully unfollowed the organisation.',
        ], 200);
    }

    /**
 * @OA\Delete(
 *     path="/api/organisations/{organisation}",
 *     summary="Delete an organisation",
 *     tags={"Organisations"},
 *     security={{ "Token": {"ORGANISER"} }},
 *     @OA\Parameter(
 *         name="organisation",
 *         in="path",
 *         required=true,
 *         description="ID of the organisation to delete",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successfully deleted the organisation",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The organisation has been deleted successfully.")
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
 *         description="Organisation not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="No query results for model [App\Models\Organisation]")
 *         )
 *     )
 * )
 *
 * @param int $organisation
 * @return \Illuminate\Http\Response
 */
    public function deleteOrganisation(Organisation $organisation)
    {
        $organisation->organisers()->update([
            'organisation_id' => null,
        ]);
        $organisation->delete();
        return response([
            'message' => 'The organisation has been deleted successfully.',
        ], 200);
    }
}
