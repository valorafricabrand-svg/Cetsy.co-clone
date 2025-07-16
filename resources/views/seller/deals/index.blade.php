@extends('layouts.app')

@section('content')
<div class="content">
  <h1 class="mb-4">One‑Off Deals</h1>
  <a href="{{ route('seller.deals.create') }}" class="btn btn-primary mb-3">+ New Deal</a>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Name</th>
        <th>% Off</th>
        <th>Applies To</th>
        <th>Period</th>
      </tr>
    </thead>
    <tbody>
      @foreach($deals as $deal)
        <tr>
          <td>{{ $deal->name }}</td>
          <td>{{ $deal->discount_percent }}%</td>
          <td>{{ $deal->applies_to_all ? 'All Products' : $deal->products->count().' Selected' }}</td>
          <td>{{ $deal->starts_at->format('M d, Y H:i') }} → {{ $deal->ends_at->format('M d, Y H:i') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{ $deals->links() }}
</div>
@endsection
