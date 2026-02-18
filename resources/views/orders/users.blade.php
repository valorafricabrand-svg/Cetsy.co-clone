@extends('theme.'.theme().'.layouts.app')

@section('main')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="mx-auto w-full px-4 sm:px-6">
                <div class="grid grid-cols-12 gap-4 mb-2">
                    <div class="sm:col-span-6">
                        <h1>Users</h1>
                    </div>
                    <div class="sm:col-span-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Orders</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="mx-auto w-full px-4 sm:px-6">
                <div class="grid grid-cols-12 gap-4">
                    <div class="md:col-span-12">
                        <!-- Orders List -->
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm card-primary">
                            <div class="border-b border-slate-200 px-4 py-3">
                                <h3 class="text-lg font-semibold text-slate-900">Users List</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="p-4 sm:p-5">
                                <table class="min-w-full divide-y divide-slate-200 text-sm border border-slate-200">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer Name</th>
                                            <th>Order Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($users as $order)
                                            <tr>
                                             
                                                <td>{{ $order->id }}</td>
                                                <td>{{ $order->name }}</td>
                                                <td>
                                                    @if($order->created_at)
                                                        {{ $order->created_at->format('d/m/Y') }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('orders.show', $order->id) }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition bg-sky-500 text-white hover:bg-sky-400 px-3 py-1.5 text-xs">View</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No orders found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.card-body -->
                            <div class="border-t border-slate-200 px-4 py-3">
                                <!-- Pagination -->
                                {{$users->links("pagination::tailwind")}}
                            </div>
                        </div>
                        <!-- /.card -->
                    </div>
                    <!--/.col (left) -->
                </div>
                <!-- /.row -->
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
@endsection




