{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.app')

@section('header')
    <h2 class="h4 mb-0">{{ __('Manage Users') }}</h2>
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
        <h3 class="h5 mb-0">All {{ ($role ?? 'seller') === 'buyer' ? 'Buyers' : 'Sellers' }}</h3>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New User
        </a>
    </div>

    <form method="GET" class="row g-3 mb-3">
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active" {{ request('status')==='active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status')==='inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        @if(($role ?? 'seller') === 'seller')
        <div class="col-md-2">
            <label class="form-label">KYC Status</label>
            <select name="kyc_status" class="form-select">
                <option value="">Any</option>
                @foreach(['pending','approved','rejected'] as $s)
                    <option value="{{ $s }}" {{ request('kyc_status')===$s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Name, email, or shop name">
        </div>
        <div class="col-md-2">
            <label class="form-label">Platform</label>
            <select name="platform" class="form-select">
                <option value="">Any</option>
                <option value="web" {{ request('platform')==='web' ? 'selected' : '' }}>Web</option>
                <option value="app" {{ request('platform')==='app' ? 'selected' : '' }}>App</option>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-outline-secondary">Filter</button>
        </div>
    </form>

    @if($users->count())
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        @if(($role ?? 'seller') === 'seller')
                          <th scope="col">Shop</th>
                          <th scope="col">KYC</th>
                        @endif
                        <th scope="col">Last Platform</th>
                        <th scope="col">Last Seen</th>
                        <th scope="col">Hits (App/Web)</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end">Wallet</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <th scope="row">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                            </th>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            @if(($role ?? 'seller') === 'seller')
                              <td>{{ optional($user->shop)->name ?? '-' }}</td>
                              <td>
                                  @php($kyc = optional($user->kyc)->status)
                                  @if($kyc==='approved')
                                    <span class="badge bg-success">Approved</span>
                                  @elseif($kyc==='pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                  @elseif($kyc==='rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                  @else
                                    <span class="badge bg-secondary">N/A</span>
                                  @endif
                              </td>
                            @endif
                            <td>
                                @php($platformLabel = optional($user->platformStat)->last_platform)
                                @if($platformLabel === 'app')
                                    <span class="badge bg-primary">App</span>
                                @elseif($platformLabel === 'web')
                                    <span class="badge bg-info text-dark">Web</span>
                                @else
                                    <span class="badge bg-secondary">Unknown</span>
                                @endif
                            </td>
                            <td>{{ optional(optional($user->platformStat)->last_seen_at)->diffForHumans() ?? '-' }}</td>
                            <td>{{ (int) optional($user->platformStat)->app_hits }}/{{ (int) optional($user->platformStat)->web_hits }}</td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="text-end">{{ get_currency() }} {{ number_format((float)($user->wallet_balance ?? 0),2) }}</td>
                            <td class="text-end">
                                @if(method_exists($user,'isSeller') && $user->isSeller())
                                  <a href="{{ route('admin.sellers.login-as', $user->id) }}" 
                                                 class="btn btn-sm btn-outline-success me-2"
                                                 onclick="return confirm('Are you sure you want to login as this seller?')">
                                                  <i class="fas fa-user-secret me-1"></i> Login as Seller
                                              </a>
                                @endif
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                @if(!$user->is_active)
                                    <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="d-inline-block me-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this Seller Account?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.users.deactivate', $user) }}" method="POST" class="d-inline-block me-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Deactivate this Seller Account?')">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="alert alert-info">
            No users found. <a href="{{ route('admin.users.create') }}" class="alert-link">Create one</a>.
        </div>
    @endif
</div>
@endsection
