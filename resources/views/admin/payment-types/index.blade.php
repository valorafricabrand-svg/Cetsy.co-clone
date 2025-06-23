@extends('layouts.app')

@section('title', 'Payment Types')

@section('content')
<div class="content">
    <div class="container-xxl">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Payment Types</h2>
            <a href="{{ route('admin.payment-types.create') }}" class="btn btn-outline-success">
                <i class="fas fa-plus me-2"></i>Create Payment Type
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Image</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentTypes as $paymentType)
                            <tr>
                                <td>{{ $paymentType->id }}</td>
                                <td>{{ $paymentType->name }}</td>
                                <td>
                                    <img src="{{ asset('storage/' . $paymentType->image) }}" alt="{{ $paymentType->name }}" class="img-fluid" style="width: 50px; height: 50px;">
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.payment-types.show', $paymentType->id) }}" class="btn btn-outline-secondary btn-sm">
                                        View
                                    </a>
                                    <a href="{{ route('admin.payment-types.edit', $paymentType->id) }}" class="btn btn-outline-primary btn-sm">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.payment-types.destroy', $paymentType->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this payment type?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    No payments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $paymentTypes->links() }}</div>
        </div>

    </div>
</div>
@endsection 