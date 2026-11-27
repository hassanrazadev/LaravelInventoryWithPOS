@extends('layouts.master')

@section('title', 'Sales Returns')

@section('css')
    <link href="{{ asset('assets/css/datatables.min.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="page-heading ims-page-header">
        <div>
            <h1 class="page-title">Sales Returns</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item">Returns</li>
            </ol>
        </div>
        @can('create return')
            <a href="{{ route('returns.create') }}" class="btn btn-primary">
                <i class="fa fa-plus mr-1"></i> Record Return
            </a>
        @endcan
    </div>

    <div class="page-content fade-in-up">
        <div class="row mb-4">
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                <div class="ims-kpi-card">
                    <div class="ims-kpi-label">Total Returns</div>
                    <div class="ims-kpi-value">{{ $stats['total_returns'] }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                <div class="ims-kpi-card">
                    <div class="ims-kpi-label">Total Refunded</div>
                    <div class="ims-kpi-value text-danger">${{ number_format($stats['total_refunded'], 2) }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3 mb-sm-0">
                <div class="ims-kpi-card">
                    <div class="ims-kpi-label">Units Restocked</div>
                    <div class="ims-kpi-value text-success">{{ $stats['units_restocked'] }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="ims-kpi-card">
                    <div class="ims-kpi-label">Damaged / Not Restocked</div>
                    <div class="ims-kpi-value text-warning">{{ $stats['units_not_restocked'] }}</div>
                </div>
            </div>
        </div>

        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">Sales Return History</div>
            </div>
            <div class="ibox-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="returns-table" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Order #</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Refund</th>
                            <th>Restock</th>
                            <th>Recorded By</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($returns as $item)
                            <tr data-index="{{ $item->id }}">
                                <td>#{{ $item->id }}</td>
                                <td class="font-weight-semibold text-nowrap">
                                    @if($item->order)
                                        <a href="{{ route('orders.show', $item->order->id) }}">{{ $item->order->order_no }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="font-weight-medium">{{ $item->product ? $item->product->product_name : '-' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td class="font-weight-semibold text-danger text-nowrap">${{ number_format((float) $item->return_price, 2) }}</td>
                                <td>
                                    @if($item->restock)
                                        <span class="badge badge-soft-success">Yes</span>
                                    @else
                                        <span class="badge badge-soft-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $item->createdBy ? $item->createdBy->name : '-' }}</td>
                                <td class="text-nowrap">{{ $item->created_at->format('Y-m-d') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('returns.show', $item->id) }}" class="btn btn-info btn-xs m-r-5" data-toggle="tooltip" data-original-title="View"><i class="fa fa-eye font-14"></i></a>
                                    @can('update return')
                                        <a href="{{ route('returns.edit', $item->id) }}" class="btn btn-primary btn-xs m-r-5" data-toggle="tooltip" data-original-title="Edit"><i class="fa fa-pencil font-14"></i></a>
                                    @endcan
                                    @can('delete return')
                                        <a href="{{ route('returns.destroy', $item->id) }}" class="btn btn-danger delete-return btn-xs" data-toggle="tooltip" data-original-title="Delete"><i class="fa fa-trash font-14"></i></a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        let dataTable = $('#returns-table').DataTable({
            pageLength: 10,
            order: [[0, 'desc']]
        });

        $(document).on('click', '.delete-return', function (e) {
            e.preventDefault();
            if (confirm('Delete this return record? Any restocked quantity will be reversed from product inventory.')) {
                let btn = $(this);
                let row = btn.parents('tr');
                $.ajax({
                    url: btn.attr('href'),
                    type: 'DELETE',
                    headers: { Accept: 'application/json' },
                    success: function (response) {
                        let data = typeof response === 'string' ? JSON.parse(response) : response;
                        if (data.status) {
                            showToast('Success', data.message, 'success');
                            btn.tooltip('dispose');
                            $('.tooltip').remove();
                            dataTable.row(row).remove().draw(false);
                        } else {
                            showToast('Error', data.message, 'error');
                        }
                    }
                });
            }
        });
    </script>
@endsection
