<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthenticationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="password123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful authentication",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The user has been authenticated successfully."),
     *             @OA\Property(property="token", type="string", example="your_access_token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The provided credentials do not match our records.")
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return response([
                'message' => 'The user has been authenticated successfully.',
                'token' => $request->user()->createToken('RouteYouEvents')->plainTextToken
            ], 200);
        }
        return response(['message' => 'The provided credentials do not match our records.'], 401);
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="User registration",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="password123"),
     *                 @OA\Property(property="phone_number", type="string", example="+32123456789"),
     *                 @OA\Property(property="city", type="string", example="City"),
     *                 @OA\Property(property="zip", type="integer", example=12345),
     *                 @OA\Property(property="street", type="string", example="Street"),
     *                 @OA\Property(property="house_number", type="string", example="123"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered and authenticated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The user has been registered and authenticated successfully."),
     *             @OA\Property(property="user", type="object", example={"id": 1, "first_name": "John", "last_name": "Doe", "full_name": "John Doe", "email": "user@example.com", "phone_number": "+32123456789", "city": "City", "zip": 12345, "street": "Street", "house_number": "123", "organisation_id": null, "updated_at": "2021-05-04T12:00:00.000000Z", "created_at": "2021-05-04T12:00:00.000000Z"}),
     *             @OA\Property(property="token", type="string", example="your_access_token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"email": {"The email has already been taken."}})
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:45',
            'last_name' => 'required|string|max:45',
            'email' => 'required|string|email:rfc,dns|max:45|unique:users,email',
            'password' => ['required', 'string', 'max:45', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()],
            'phone_number' => 'required|phone:INTERNATIONAL,BE',
            'city' => 'required|string|max:45',
            'zip' => 'required|numeric',
            'street' => 'required|string|max:45',
            'house_number' => 'required|string|max:10',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'city' => $request->city,
            'zip' => $request->zip,
            'street' => $request->street,
            'house_number' => $request->house_number,
            'organisation_id' => null
        ]);

        return response([
            'message' => 'The user has been registered and authenticated successfully.',
            'user' => $user,
            'token' => $user->createToken('RouteYouEvents')->plainTextToken
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="User logout",
     *     tags={"Authentication"},
     *     security={{ "Token": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The user has been logged out successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response(['message' => 'The user has been logged out successfully'], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Get authenticated user",
     *     tags={"Authentication"},
     *     security={{ "Token": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved authenticated user",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The user has been returned successfully"),
     *             @OA\Property(property="user", type="object", example={"id": 1, "first_name": "John", "last_name": "Doe", "full_name": "John Doe", "email": "user@example.com", "phone_number": "+32123456789", "city": "City", "zip": 12345, "street": "Street", "house_number": "123", "organisation_id": null, "updated_at": "2021-05-04T12:00:00.000000Z", "created_at": "2021-05-04T12:00:00.000000Z"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getUser(Request $request)
    {
        return response([
            'message' => 'The user has been returned successfully',
            'user' => $request->user()
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/user",
     *     summary="Delete authenticated user",
     *     tags={"Authentication"},
     *     security={{ "Token": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully deleted authenticated user",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The user has been deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function deleteUser(Request $request)
    {
        $request->user()->tokens()->delete();
        $request->user()->delete();

        return response([
            'message' => 'The user has been deleted successfully'
        ], 200);
    }
}
