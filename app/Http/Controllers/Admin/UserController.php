<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class UserController extends Controller
{
    /**
     * Display a paginated listing of the users.
     */
    public function index(Request $request)
    {
        $status     = $request->query('status'); // active|inactive|all
        $kycStatus  = $request->query('kyc_status');
        $q          = trim((string) $request->query('q'));
        // role selector: 'seller' (default) or 'buyer'
        $role       = $request->query('role');
        if (!$role) {
            $role = request()->routeIs('admin.buyers.*') ? 'buyer' : 'seller';
        }

        $users = User::query()
            ->where('user_type', $role)
            ->with(['shop:id,user_id,name', 'kyc:id,user_id,status'])
            ->when($status === 'active', fn($q) => $q->where('is_active', 1))
            ->when($status === 'inactive', fn($q) => $q->where('is_active', 0))
            ->when($kycStatus, function ($query) use ($kycStatus) {
                $query->whereHas('kyc', fn($k) => $k->where('status', $kycStatus));
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%$q%")
                        ->orWhere('email', 'like', "%$q%")
                        ->orWhereHas('shop', fn($s) => $s->where('name', 'like', "%$q%"));
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Lightweight wallet balances (completed only)
        // Attach computed balance to each user (avoid N+1 heavy queries on huge datasets)
        if ($users->count()) {
            $userIds = $users->pluck('id');
            $balances = \App\Models\Wallet::selectRaw('user_id, COALESCE(SUM(credit - debit),0) as bal')
                ->whereIn('user_id', $userIds)
                ->where('status', 'completed')
                ->groupBy('user_id')
                ->pluck('bal', 'user_id');
            $users->getCollection()->transform(function ($u) use ($balances) {
                $u->wallet_balance = (float) ($balances[$u->id] ?? 0);
                return $u;
            });
        }

        return view('admin.users.index', compact('users', 'status', 'kycStatus', 'q', 'role'));
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
     * Resend verification email to user (admin action).
     */
    public function resendVerification(User $user)
    {
        if (method_exists($user, 'hasVerifiedEmail') && $user->hasVerifiedEmail()) {
            return back()->with('success', 'Email is already verified.');
        }

        // Fire default Registered event and explicitly send notification if available
        event(new Registered($user));
        if (method_exists($user, 'sendEmailVerificationNotification')) {
            $user->sendEmailVerificationNotification();
        }

        return back()->with('success', 'Verification email resent successfully.');
    }

    /**
     * Mark email as verified (admin action).
     */
    public function markEmailVerified(User $user)
    {
        if (method_exists($user, 'hasVerifiedEmail') && $user->hasVerifiedEmail()) {
            return back()->with('success', 'Email already verified.');
        }

        $user->forceFill(['email_verified_at' => now()])->save();
        return back()->with('success', 'Email marked as verified.');
    }

    /**
     * Mark email as unverified (admin action).
     */
    public function markEmailUnverified(User $user)
    {
        if (!method_exists($user, 'hasVerifiedEmail') || !$user->hasVerifiedEmail()) {
            // If method not present, just proceed to null; else message when already unverified
            if (method_exists($user, 'hasVerifiedEmail')) {
                return back()->with('success', 'Email already unverified.');
            }
        }

        $user->forceFill(['email_verified_at' => null])->save();
        return back()->with('success', 'Email marked as unverified.');
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
