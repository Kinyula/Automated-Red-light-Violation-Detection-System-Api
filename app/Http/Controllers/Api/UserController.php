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
        // Validate incoming request data
        // Retrieve the role_id from the request
        $roleId = $request->input('role_id');

        // Validation rules
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|string|email|lowercase|unique:users',
            'password' => 'required|string|confirmed',
            'license_plate' => 'required|string|max:20', // Base validation
        ];

        // Conditionally apply 'unique' rule for license_plate if role_id is 0
        if ($roleId === 0) {
            $rules['license_plate'] .= '|unique:users,license_plate';
        }

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create the user
        $user = User::create([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'phone_number' => $request->input('phone_number'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'license_plate' => $request->input('license_plate'),
            'role_id' => $roleId,
            'position' => $request->position,
            'department' => $request->department
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'license_plate' => $request->license_plate,
            'role_id' => $request->role_id,
            'position' => $request->position,
            'department' => $request->department
        ]);


        return response()->json([
            'message' => 'User registered successfully!',
            'user' => $user
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


        $validated = $request->validate([
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'phone_number' => 'string|max:20',
            'license_plate' => 'string|max:20|unique:users,license_plate,' . $id,
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully!',
            'user' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully!']);
    }
}
