@extends('layouts.master')

@section('title', $supplier->name)

@section('css')
    <link href="{{ asset('assets/css/datatables.min.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="page-heading ims-page-header">
        <div>
            <h1 class="page-title">Supplier Details</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">Suppliers</a></li>
                <li class="breadcrumb-item">{{ $supplier->name }}</li>
            </ol>
        </div>
        @can('update supplier')
            <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-primary">
                <i class="fa fa-pencil mr-1"></i> Edit Supplier
            </a>
        @endcan
    </div>

    <div class="page-content fade-in-up">
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title d-flex align-items-center">
                    <span class="ims-avatar ims-avatar-sm mr-2">{{ \App\Utils\AppUtils::initials($supplier->name) }}</span>
                    <span>{{ $supplier->name }}</span>
                </div>
                <div>
                    @if($supplier->trashed())
                        <span class="badge badge-soft-danger">Inactive</span>
                    @else
                        <span class="badge badge-soft-success">Active</span>
                    @endif
                </div>
            </div>
            <div class="ibox-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Phone</div>
                        <div>{{ $supplier->phone }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Email</div>
                        <div>{{ $supplier->email ?: '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Total Purchases</div>
                        <div class="font-weight-bold text-primary">{{ number_format($totalSpent, 2) }} ({{ $supplier->purchases->count() }} records)</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Created By</div>
                        <div>{{ $supplier->createdBy ? $supplier->createdBy->name : '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Updated By</div>
                        <div>{{ $supplier->updatedBy ? $supplier->updatedBy->name : '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Address</div>
                        <div>{{ $supplier->address ?: '-' }}</div>
                    </div>
                </div>

                <h6 class="m-b-20 m-t-20"><i class="fa fa-briefcase mr-2"></i>Purchase History</h6>
                <table class="table table-striped table-bordered table-hover" id="supplier-purchases-table" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Total</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($supplier->purchases as $purchase)
                        <tr>
                            <td>#{{ $purchase->id }}</td>
                            <td>{{ number_format((float) $purchase->total, 2) }}</td>
                            <td>{{ $purchase->createdBy ? $purchase->createdBy->name : '-' }}</td>
                            <td>{{ $purchase->created_at }}</td>
                            <td class="text-center">
                                @can('read purchase')
                                    <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-info btn-xs" data-toggle="tooltip" data-original-title="View"><i class="fa fa-eye font-14"></i></a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        $('#supplier-purchases-table').DataTable({
            pageLength: 10
        });
    </script>
@endsection
