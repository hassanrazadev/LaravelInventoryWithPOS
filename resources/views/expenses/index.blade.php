@extends('layouts.master')

@section('title', 'Manage Expenses')

@section('css')
    <link href="{{ asset('assets/css/datatables.min.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="page-heading ims-page-header">
        <div>
            <h1 class="page-title">Manage Expenses</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item">Expenses</li>
            </ol>
        </div>
        @can('create expense')
            <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                <i class="fa fa-plus mr-1"></i> Add Expense
            </a>
        @endcan
    </div>

    <div class="page-content fade-in-up">
        <div class="row mb-4">
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                <div class="ims-kpi-card">
                    <div class="ims-kpi-label">Total Active Expenses</div>
                    <div class="ims-kpi-value text-danger">${{ number_format($stats['total_expenses'], 2) }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                <div class="ims-kpi-card">
                    <div class="ims-kpi-label">This Month</div>
                    <div class="ims-kpi-value text-warning">${{ number_format($stats['month_expenses'], 2) }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3 mb-sm-0">
                <div class="ims-kpi-card">
                    <div class="ims-kpi-label">Active Entries</div>
                    <div class="ims-kpi-value">{{ $stats['active_count'] }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="ims-kpi-card">
                    <div class="ims-kpi-label">Average Expense</div>
                    <div class="ims-kpi-value text-primary">${{ number_format($stats['average_expense'], 2) }}</div>
                </div>
            </div>
        </div>

        <div class="ibox">
            <div class="ibox-head flex-wrap" style="gap: 8px;">
                <div class="ibox-title">Expenses</div>
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                    <a href="{{ route('expenses.index') }}" class="btn btn-xs {{ $categoryFilter === null ? 'btn-primary' : 'btn-light border' }}">All</a>
                    @foreach($categories as $category)
                        <a href="{{ route('expenses.index', ['category' => $category]) }}" class="btn btn-xs {{ $categoryFilter === $category ? 'btn-primary' : 'btn-light border' }}">{{ $category }}</a>
                    @endforeach
                </div>
            </div>
            <div class="ibox-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="expenses-table" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Recorded By</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($expenses as $item)
                            <tr data-index="{{ $item->id }}">
                                <td>{{ $item->id }}</td>
                                <td class="font-weight-medium">{{ $item->expense_name }}</td>
                                <td>
                                    @if($item->category)
                                        <span class="badge badge-soft-secondary">{{ $item->category }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="font-weight-semibold text-danger">${{ number_format((float) $item->expense_amount, 2) }}</td>
                                <td>{{ $item->expense_date ? $item->expense_date->format('Y-m-d') : $item->created_at->format('Y-m-d') }}</td>
                                <td>{{ $item->createdBy ? $item->createdBy->name : '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('expenses.show', $item->id) }}" class="btn btn-info btn-xs m-r-5" data-toggle="tooltip" data-original-title="View"><i class="fa fa-eye font-14"></i></a>
                                    @can('update expense')
                                        <a href="{{ route('expenses.edit', $item->id) }}" class="btn btn-primary btn-xs m-r-5" data-toggle="tooltip" data-original-title="Edit"><i class="fa fa-pencil font-14"></i></a>
                                    @endcan
                                    @can('delete expense')
                                        <a href="{{ route('expenses.destroy', $item->id) }}" class="btn btn-danger delete-expense btn-xs m-r-5" data-toggle="tooltip" data-original-title="Delete"><i class="fa fa-trash font-14"></i></a>
                                        @include('partials._status-switch', [
                                            'url' => route('expenses.delete', $item->id),
                                            'checked' => $item->deleted_at === null,
                                            'class' => 'soft-delete-expense',
                                        ])
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
        let dataTable = $('#expenses-table').DataTable({
            pageLength: 10,
            order: [[0, 'desc']]
        });

        $(document).on('change', '.soft-delete-expense', function () {
            let checkbox = $(this);
            $.ajax({
                url: checkbox.attr('data-url'),
                type: 'PATCH',
                headers: { Accept: 'application/json' },
                success: function (response) {
                    let data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.status) {
                        showToast('Success', data.message, 'success');
                    } else {
                        checkbox.prop('checked', !checkbox.prop('checked'));
                        showToast('Error', data.message, 'error');
                    }
                },
                error: function () {
                    checkbox.prop('checked', !checkbox.prop('checked'));
                    showToast('Error', 'Unable to update expense status.', 'error');
                }
            });
        });

        $(document).on('click', '.delete-expense', function (e) {
            e.preventDefault();
            if (confirm('Do you really want to permanently delete this expense?')) {
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
