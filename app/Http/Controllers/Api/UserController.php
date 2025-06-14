<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    // Remove the auth check completely
    $user = User::first(); // Or any other default user if needed

    // If you still want to track activity for authenticated users
    if (auth()->check()) {
        $user = auth()->user();
        $user->update([
            'is_verified' => true,
            'online_status' => 'online',
            'last_activity' => now()
        ]);
    }


    $users = [];
    $onlineUsersCount = User::where('role_id', '0')->where('online_status', 'online')->count();
    $offlineUsersCount = User::where('role_id', '0')->where('online_status', 'offline')->count();

    if ($user && $user->role_id == '1') {
        $users = User::where('role_id', '2')
            ->orderBy('created_at', 'desc')
            ->get();
    } elseif ($user && $user->role_id == '2') {
        $users = User::where('role_id', '0')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    return response()->json([
        'user' => $user,
        'users' => $users,
        'online' => $onlineUsersCount,
        'offline' => $offlineUsersCount,
    ]);
}
    public function statusShow()
    {
        $userLikeStatusCount = Status::where('like', '=', 1)->count();
        $userDislikeStatusCount = Status::where('dislike', '=', 1)->count();
        $userStatus = Status::where('user_id', Auth::user()->id)->first();
        if (!$userStatus) {
            return response()->json(['message' => 'Status not found'], 404);
        }

        return response()->json([
            'like' => $userStatus->like,
            'dislike' => $userStatus->dislike,
            'userLikeStatusCount' => $userLikeStatusCount,
            'userDislikeStatusCount' => $userDislikeStatusCount,
        ]);
    }


    public function getUserByPlate($license_plate)
    {

        $normalized_plate = strtoupper(str_replace(' ', '', $license_plate));

        $user = User::whereRaw("REPLACE(UPPER(license_plate), ' ', '') = ?", [$normalized_plate])->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['id' => $user->id, 'license_plate' => $user->license_plate]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $users = User::where('last_name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhere('phone_number', 'LIKE', "%{$query}%")
            ->get();

        return response()->json($users);
    }

    public function status(Request $request, $id)
    {

        $status = Status::where('user_id', $id)->firstOrFail();

        // Atomic update
        $status->update([
            'like' => $request->input('like', $status->like),
            'dislike' => $request->input('dislike', $status->dislike),
        ]);

        return response()->json([
            'message' => 'Status updated successfully',
            'status' => $status->fresh()
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:10',
            'last_name' => 'required|string|max:10',
            'phone_number' => ['required', 'regex:/^(06|07|065|066|067|068|069)\d{8}$/'],
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'license_plate' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {

                    if ($value !== 'none' && \App\Models\User::where('license_plate', $value)->exists()) {
                        return $fail('The license plate has already been taken.');
                    }
                },
            ],
        ]);

        $user = User::create([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'phone_number' => $request->input('phone_number'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'license_plate' => $request->input('license_plate'),
            'role_id' => $request->input('role_id'),
            'position' => $request->input('position'),
            'department' => $request->input('department')
        ]);

        $status = Status::create([
            'user_id' => $user->id,
            'like' => false,
            'dislike' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->update([
            'phone_number' => $request->input('phone_number')
        ]);
        return response()->json([
            'message' => 'User updated successfully!',
            'user' => $user
        ]);
    }

    public function generalUpdate(Request $request, $id)
    {

        $user = User::find($id);


        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->update([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'license_plate' => $request->input('license_plate')
        ]);

        return response()->json(['message' => 'User updated successfully.', 'user' => $user], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $id)
    {
        $id->delete();
        return response()->json(['message' => 'User deleted successfully!']);
    }
}
