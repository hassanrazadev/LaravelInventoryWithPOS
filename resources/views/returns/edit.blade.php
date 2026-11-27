@extends('layouts.master')

@section('title', 'Edit Sales Return')

@section('content')
    <div class="page-heading">
        <div>
            <h1 class="page-title">Edit Sales Return</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('returns.index') }}">Returns</a></li>
                <li class="breadcrumb-item">Edit #{{ $saleReturn->id }}</li>
            </ol>
        </div>
    </div>

    <div class="page-content fade-in-up">
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">Update Return Details</div>
            </div>
            <div class="ibox-body">
                <div class="row mb-4 p-3 rounded bg-light border">
                    <div class="col-md-3">
                        <div class="text-muted small text-uppercase font-weight-bold">Order</div>
                        <div class="font-weight-semibold">{{ $saleReturn->order ? $saleReturn->order->order_no : '-' }}</div>
                    </div>
                    <div class="col-md-5">
                        <div class="text-muted small text-uppercase font-weight-bold">Product</div>
                        <div class="font-weight-medium">{{ $saleReturn->product ? $saleReturn->product->product_name : '-' }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted small text-uppercase font-weight-bold">Quantity</div>
                        <div>{{ $saleReturn->quantity }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted small text-uppercase font-weight-bold">Restocked</div>
                        <div>{{ $saleReturn->restock ? 'Yes' : 'No' }}</div>
                    </div>
                </div>

                <form action="{{ route('returns.update', $saleReturn->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group required">
                                <label for="return_price">Refund Amount ($)</label>
                                <input type="number" step="0.01" min="0" id="return_price" name="return_price" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'return_price') }}" value="{{ old('return_price', $saleReturn->return_price) }}" required>
                                @include('partials._error', ['field' => 'return_price'])
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="reason">Return Reason / Notes</label>
                                <textarea id="reason" name="reason" rows="3" maxlength="500" class="form-control {{ \App\Utils\AppUtils::inputFieldError($errors, 'reason') }}" placeholder="Return reason or inspection note">{{ old('reason', $saleReturn->reason) }}</textarea>
                                @include('partials._error', ['field' => 'reason'])
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 d-flex justify-content-end">
                        <a href="{{ route('returns.index') }}" class="btn btn-light mr-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Return</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
