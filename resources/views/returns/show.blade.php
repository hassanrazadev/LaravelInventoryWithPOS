@extends('layouts.master')

@section('title', 'Return #' . $saleReturn->id)

@section('content')
    <div class="page-heading ims-page-header">
        <div>
            <h1 class="page-title">Return #{{ $saleReturn->id }}</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('returns.index') }}">Returns</a></li>
                <li class="breadcrumb-item">#{{ $saleReturn->id }}</li>
            </ol>
        </div>
        @can('update return')
            <a href="{{ route('returns.edit', $saleReturn->id) }}" class="btn btn-primary">
                <i class="fa fa-pencil mr-1"></i> Edit Return
            </a>
        @endcan
    </div>

    <div class="page-content fade-in-up">
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">Sales Return Details</div>
                <div>
                    @if($saleReturn->restock)
                        <span class="badge badge-soft-success">Restocked</span>
                    @else
                        <span class="badge badge-soft-secondary">Not Restocked</span>
                    @endif
                </div>
            </div>
            <div class="ibox-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Order Number</div>
                        <div class="font-weight-medium">
                            @if($saleReturn->order)
                                <a href="{{ route('orders.show', $saleReturn->order->id) }}">{{ $saleReturn->order->order_no }}</a>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Customer</div>
                        <div>{{ $saleReturn->order && $saleReturn->order->customer ? $saleReturn->order->customer->name : 'Walk-in / Guest' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Product</div>
                        <div class="font-weight-medium">{{ $saleReturn->product ? $saleReturn->product->product_name : '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Quantity Returned</div>
                        <div>{{ $saleReturn->quantity }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Refund Amount</div>
                        <div class="font-weight-bold text-danger font-16">${{ number_format((float) $saleReturn->return_price, 2) }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Recorded By</div>
                        <div>{{ $saleReturn->createdBy ? $saleReturn->createdBy->name : '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Updated By</div>
                        <div>{{ $saleReturn->updatedBy ? $saleReturn->updatedBy->name : '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Recorded At</div>
                        <div>{{ $saleReturn->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small font-weight-bold text-uppercase">Reason</div>
                        <div>{{ $saleReturn->reason ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
