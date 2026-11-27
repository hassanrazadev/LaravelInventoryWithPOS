@extends('layouts.master')

@section('title', $expense->expense_name)

@section('content')
    <div class="page-heading ims-page-header">
        <div>
            <h1 class="page-title">Expense Details</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
                <li class="breadcrumb-item">{{ $expense->expense_name }}</li>
            </ol>
        </div>
        @can('update expense')
            <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-primary">
                <i class="fa fa-pencil mr-1"></i> Edit Expense
            </a>
        @endcan
    </div>

    <div class="page-content fade-in-up">
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">{{ $expense->expense_name }}</div>
                <div>
                    @if($expense->trashed())
                        <span class="badge badge-soft-danger">Inactive</span>
                    @else
                        <span class="badge badge-soft-success">Active</span>
                    @endif
                </div>
            </div>
            <div class="ibox-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Category</div>
                        <div>{{ $expense->category ?: '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Amount</div>
                        <div class="font-weight-bold text-danger font-16">${{ number_format((float) $expense->expense_amount, 2) }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Expense Date</div>
                        <div>{{ $expense->expense_date ? $expense->expense_date->format('Y-m-d') : $expense->created_at->format('Y-m-d') }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Recorded By</div>
                        <div>{{ $expense->createdBy ? $expense->createdBy->name : '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Updated By</div>
                        <div>{{ $expense->updatedBy ? $expense->updatedBy->name : '-' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Created At</div>
                        <div>{{ $expense->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small font-weight-bold text-uppercase">Notes</div>
                        <div>{{ $expense->notes ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
