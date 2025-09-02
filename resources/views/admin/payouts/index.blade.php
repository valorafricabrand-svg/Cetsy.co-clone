{{-- resources/views/admin/payouts/index.blade.php --}}
@extends('layouts.app')
@section('title','Payout Requests')

@section('content')
<div class="content py-4">
    <div class="container-xxl">

        <h2 class="mb-4">Payout Requests</h2>

        {{-- simple status filter --}}
        <form class="mb-3" method="GET">
            <div class="input-group w-auto">
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach(['pending','approved','sent','rejected','failed','paid'] as $s)
                        <option value="{{ $s }}" {{ request('status')===$s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-outline-secondary">Filter</button>
            </div>
        </form>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Seller</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Method</th>
                            <th>Requested</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payouts as $p)
                            <tr>
                                <td>{{ $p->id }}</td>
                                <td>{{ $p->wallet->user->name ?? $p->user_id }}</td>
                                <td>{{ get_currency() }} {{ number_format($p->amount,2) }}</td>
                                <td>
                                  @php
                                    $badge = 'secondary';
                                    if ($p->status === 'pending') $badge='warning';
                                    elseif ($p->status === 'approved') $badge='info';
                                    elseif ($p->status === 'sent') $badge='primary';
                                    elseif ($p->status === 'paid') $badge='success';
                                    elseif ($p->status === 'rejected' || $p->status === 'failed') $badge='danger';
                                  @endphp
                                  <span class="badge text-bg-{{ $badge }} text-uppercase">{{ $p->status }}</span>
                                </td>
                                <td>{{ optional(optional($p->paymentMethod)->paymentType)->name ?? '—' }}</td>
                                <td>{{ $p->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.payouts.show',$p) }}"
                                       class="btn btn-outline-secondary btn-sm">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    No payout requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $payouts->links() }}</div>
        </div>

    </div>
</div>
@endsection
