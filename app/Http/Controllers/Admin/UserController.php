<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a paginated listing of the users.
     */
    public function index()
    {
        $users = User::paginate(15);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Validate and store a newly created user.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required','string','max:255'],
            'email'                 => ['required','string','email','max:255','unique:users'],
            'user_type'             => ['required','in:admin,seller,buyer'],
            'password'              => ['required','string','min:8','confirmed'],
        ]);

        // Hash the password
        $data['password'] = Hash::make($data['password']);

        // Create the user
        User::create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'New user created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Validate and update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'                  => ['required','string','max:255'],
            'email'                 => ['required','string','email','max:255',"unique:users,email,{$user->id}"],
            'user_type'             => ['required','in:admin,seller,buyer'],
            'password'              => ['nullable','string','min:8','confirmed'],
        ]);

        // If password is provided, hash it; otherwise remove it so it's not overwritten
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Update user
        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
