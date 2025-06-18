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
        $users = User::where('user_type', 'seller')->paginate(15);
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

    /**
     * Approve/activate the specified user.
     */
    public function approve(User $user)
    {
        $user->update(['is_active' => 1]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Seller Account approved successfully.');
    }

    /**
     * Deactivate the specified user.
     */
    public function deactivate(User $user)
    {
        $user->update(['is_active' => 0]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Seller Account deactivated successfully.');
    }

    /**
     * Login as the specified seller (impersonation).
     */
    public function loginAs($userId)
    {
        // Find the user
        $user = User::findOrFail($userId);
        
        // Check if user is a seller
        if ($user->user_type !== 'seller') {
            return redirect()
                ->route('admin.products.index')
                ->with('error', 'Can only login as sellers.');
        }

        // Store admin session data for return
        session([
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name,
            'impersonating' => true
        ]);

        // Login as the seller
        auth()->login($user);

        return redirect()
            ->route('seller.dashboard')
            ->with('success', "You are now logged in as {$user->name}. Use the 'Return to Admin' button to go back.");
    }

    /**
     * Return from seller impersonation to admin session.
     */
    public function returnFromImpersonation()
    {
        // Check if admin is impersonating
        if (!session('impersonating')) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'No impersonation session found.');
        }

        // Get admin user
        $adminUser = User::find(session('admin_id'));
        
        if (!$adminUser) {
            return redirect()
                ->route('login')
                ->with('error', 'Admin session expired. Please login again.');
        }

        // Clear impersonation session data
        session()->forget(['admin_id', 'admin_name', 'impersonating']);

        // Login as admin
        auth()->login($adminUser);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Successfully returned to admin dashboard.');
    }
}
