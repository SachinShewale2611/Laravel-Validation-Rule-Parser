<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\UserStoreRequest;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return Inertia::render('Users/Create');
    }

    /**
     * Store a newly created user in storage.
     * Using FormRequest approach
     */
    public function store(UserStoreRequest $request)
    {
        // The validation rules will be automatically parsed from UserStoreRequest
        $validated = $request->validated();

        // Create user logic here

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    /**
     * Show the form for editing the user.
     * Using controller rules method approach
     */
    public function edit($id)
    {
        return Inertia::render('Users/Edit', [
            'user' => User::findOrFail($id)
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->updateRules($request));

        // Update user logic here

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    /**
     * Validation rules for update method
     * This method will be called by the middleware
     */
    public function updateRules(Request $request): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $request->route('user')],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required_with:password'],
            'age' => ['required', 'integer', 'min:18', 'max:100'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
            'avatar' => ['nullable', 'file', 'image', 'max:2048'],
            'role' => ['required', 'string', 'in:admin,user,moderator'],
            'birth_date' => ['required', 'date', 'before:today'],
            'terms' => ['required', 'boolean', 'accepted'],
        ];
    }

    /**
     * Alternative approach: using properties
     */
    public $createValidationRules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ];
}
