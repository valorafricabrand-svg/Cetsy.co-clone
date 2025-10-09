{{-- resources/views/admin/users/show.blade.php --}}
@extends('layouts.app')

@section('header')
    <h2 class="h4 mb-0">{{ __('User Details') }}</h2>
@endsection

@section('content')
<div class="content">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h5 mb-0">User Information</h3>
        <div class="d-flex align-items-center gap-2">
            @if((method_exists($user,'isSeller') && $user->isSeller()) || (method_exists($user,'isBuyer') && $user->isBuyer()))
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#topupWalletModal">
                    <i class="fas fa-wallet me-1"></i> Top Up Wallet
                </button>
            @endif
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                <i class="fas fa-pencil-alt me-1"></i> Edit User
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Users
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Basic Information -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>Basic Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Name:</div>
                        <div class="col-sm-8">{{ $user->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Email:</div>
                        <div class="col-sm-8">
                            <a href="mailto:{{ $user->email }}" class="text-decoration-none">{{ $user->email }}</a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Phone:</div>
                        <div class="col-sm-8">
                            @if($user->phone)
                                <a href="tel:{{ $user->phone }}" class="text-decoration-none">{{ $user->phone }}</a>
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Country:</div>
                        <div class="col-sm-8">
                            @if($user->country)
                                {{ $user->country->name }}
                            @else
                                <span class="text-muted">Not specified</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">User Type:</div>
                        <div class="col-sm-8">
                            <span class="badge bg-primary">{{ ucfirst($user->user_type) }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Status:</div>
                        <div class="col-sm-8">
                            @if($user->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Joined:</div>
                        <div class="col-sm-8">{{ $user->created_at->format('M d, Y \a\t h:i A') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Last Updated:</div>
                        <div class="col-sm-8">{{ $user->updated_at->format('M d, Y \a\t h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Actions -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>Account Actions
                    </h5>
                </div>
                <div class="card-body">
                    @if(method_exists($user,'isSeller') && $user->isSeller())
                        @if(!$user->is_active)
                            <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="mb-3">
                                @csrf
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approve this seller account?')">
                                    <i class="fas fa-check me-2"></i>Approve Account
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.users.deactivate', $user) }}" method="POST" class="mb-3">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Deactivate this seller account?')">
                                    <i class="fas fa-ban me-2"></i>Deactivate Account
                                </button>
                            </form>
                        @endif
                    @endif

                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash me-2"></i>Delete Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="row">
        <!-- KYC Information -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-id-card me-2"></i>KYC Information
                    </h5>
                </div>
                <div class="card-body">
                    @if($user->kyc)
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">KYC Status:</div>
                            <div class="col-sm-8">
                                @if($user->kyc->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($user->kyc->status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">ID Type:</div>
                            <div class="col-sm-8">{{ ucfirst($user->kyc->id_type) }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">ID Number:</div>
                            <div class="col-sm-8">{{ $user->kyc->id_number }}</div>
                        </div>
                        @if($user->kyc->admin_notes)
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Admin Notes:</div>
                                <div class="col-sm-8">{{ $user->kyc->admin_notes }}</div>
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">No KYC information available.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Subscription Information -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-credit-card me-2"></i>Subscription Information
                    </h5>
                </div>
                <div class="card-body">
                    @if($user->subscription)
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Status:</div>
                            <div class="col-sm-8">
                                @if($user->subscription->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Plan:</div>
                            <div class="col-sm-8">{{ $user->subscription->plan_name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Start Date:</div>
                            <div class="col-sm-8">{{ $user->subscription->start_date ? $user->subscription->start_date->format('M d, Y') : 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">End Date:</div>
                            <div class="col-sm-8">{{ $user->subscription->end_date ? $user->subscription->end_date->format('M d, Y') : 'N/A' }}</div>
                        </div>
                    @else
                        <p class="text-muted mb-0">No subscription information available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Account Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <h4 class="text-primary">{{ $user->orders->count() }}</h4>
                                <p class="text-muted mb-0">Total Orders</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <h4 class="text-success">{{ $user->orders->where('status', 'completed')->count() }}</h4>
                                <p class="text-muted mb-0">Completed Orders</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <h4 class="text-warning">{{ $user->orders->where('status', 'pending')->count() }}</h4>
                                <p class="text-muted mb-0">Pending Orders</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <h4 class="text-info">{{ $user->wishlistItems->count() }}</h4>
                                <p class="text-muted mb-0">Wishlist Items</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if((method_exists($user,'isSeller') && $user->isSeller()) || (method_exists($user,'isBuyer') && $user->isBuyer()))
    <!-- Top Up Wallet Modal -->
    <div class="modal fade" id="topupWalletModal" tabindex="-1" aria-labelledby="topupWalletModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="topupWalletModalLabel">Top Up Wallet</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="POST" action="{{ route('admin.wallets.store') }}">
            @csrf
            <input type="hidden" name="seller" value="{{ $user->id }}">
            <div class="modal-body">
              <div class="mb-2 small text-muted">
                Account: <strong>{{ $user->name }}</strong> (ID: {{ $user->id }}, {{ $user->email }})
              </div>
              <div class="mb-3">
                <label class="form-label">Current Wallet Balance</label>
                <input type="text" class="form-control" value="{{ get_currency() }} {{ number_format(wallet('completed', $user->id), 2) }}" disabled>
              </div>
              <div class="mb-3">
                <label class="form-label">Amount (USD)</label>
                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="e.g. 50.00" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description (optional)</label>
                <input type="text" name="description" class="form-control" value="Admin top-up" maxlength="1000">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success">Top Up</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    @endif
</div>
@endsection
