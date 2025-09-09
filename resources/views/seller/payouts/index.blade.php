@extends('layouts.app')
@section('title','Payout Requests')

@section('content')
<div class="content py-4">
  <div class="container-xxl">

      <h3 class="mb-4">Payout Requests</h3>

      <div class="alert alert-info mb-4">
          Available balance: {{ shop_currency() }} {{ number_format($balance,2) }}
      </div>

      <div class="card shadow-sm border-0">
          <div class="table-responsive">
              <table class="table table-striped table-hover mb-0 align-middle">
                  <thead class="table-light">
                      <tr>
                          <th>#</th>
                          <th>Amount</th>
                          <th>Status</th>
                          <th>Requested&nbsp;On</th>
                      </tr>
                  </thead>
                  <tbody>
                      @forelse($requests as $req)
                          <tr>
                              <td>{{ $req->id }}</td>
                              <td>{{ shop_currency() }} {{ number_format($req->amount,2) }}</td>
                              <td class="text-capitalize">{{ $req->status }}</td>
                              <td>{{ $req->created_at->format('d M Y') }}</td>
                          </tr>
                      @empty
                          <tr>
                              <td colspan="4" class="text-center py-4">
                                  No payout requests yet.
                              </td>
                          </tr>
                      @endforelse
                  </tbody>
              </table>
          </div>
          <div class="card-footer">
              {{ $requests->links() }}
          </div>
      </div>

  </div>
</div>
@endsection
