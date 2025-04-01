<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $users = [];

        if ($user->role_id == '1') {

            $users = User::where('role_id', '=', '2')->orderBy('created_at', 'desc')->get();
        } elseif ($user->role_id == '2') {
            $users = User::where('role_id', '=', '0')->orderBy('created_at', 'desc')->get();
        }

        return response()->json([
            'user' => $user,
            'users' => $users
        ]);
    }

    public function getUserByPlate($license_plate)
    {
        $user = User::where('license_plate', $license_plate)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['phone_number' => $user->phone_number]);
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
