@extends('layouts.master')

@section('title', 'Manage Suppliers')

@section('css')
    <link href="{{ asset('assets/css/datatables.min.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="page-heading ims-page-header">
        <div>
            <h1 class="page-title">Manage Suppliers</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item">Suppliers</li>
            </ol>
        </div>
        @can('create supplier')
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                <i class="fa fa-plus mr-1"></i> Add Supplier
            </a>
        @endcan
    </div>

    <div class="page-content fade-in-up">
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">Suppliers</div>
            </div>
            <div class="ibox-body">
                <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" id="suppliers-table" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Purchases</th>
                        <th>Created By</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($suppliers as $item)
                        <tr data-index="{{ $item->id }}">
                            <td>{{ $item->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="ims-avatar ims-avatar-sm mr-2">{{ \App\Utils\AppUtils::initials($item->name) }}</span>
                                    <span class="font-weight-medium">{{ $item->name }}</span>
                                </div>
                            </td>
                            <td>{{ $item->phone }}</td>
                            <td>{{ $item->email ?: '-' }}</td>
                            <td>
                                <span class="badge badge-soft-secondary">{{ $item->purchases_count }} {{ \Illuminate\Support\Str::plural('purchase', $item->purchases_count) }}</span>
                            </td>
                            <td>{{ $item->createdBy ? $item->createdBy->name : '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('suppliers.show', $item->id) }}" class="btn btn-info btn-xs m-r-5" data-toggle="tooltip" data-original-title="View"><i class="fa fa-eye font-14"></i></a>
                                @can('update supplier')
                                    <a href="{{ route('suppliers.edit', $item->id) }}" class="btn btn-primary btn-xs m-r-5" data-toggle="tooltip" data-original-title="Edit"><i class="fa fa-pencil font-14"></i></a>
                                @endcan
                                @can('delete supplier')
                                    <a href="{{ route('suppliers.destroy', $item->id) }}" class="btn btn-danger delete-supplier btn-xs m-r-5" data-toggle="tooltip" data-original-title="Delete"><i class="fa fa-trash font-14"></i></a>
                                    @include('partials._status-switch', [
                                        'url' => route('suppliers.delete', $item->id),
                                        'checked' => $item->deleted_at === null,
                                        'class' => 'soft-delete-supplier',
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
        let dataTable = $('#suppliers-table').DataTable({
            pageLength: 10
        });

        $(document).on('change', '.soft-delete-supplier', function () {
            let checkbox = $(this);
            $.ajax({
                url: checkbox.attr('data-url'),
                type: 'PATCH',
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
                    showToast('Error', 'Unable to update supplier status.', 'error');
                }
            });
        });

        $(document).on('click', '.delete-supplier', function (e) {
            e.preventDefault();
            if (confirm('Do you really want to permanently delete this supplier?')) {
                let btn = $(this);
                let row = btn.parents('tr');
                let url = btn.attr('href');
                $.ajax({
                    url: url,
                    type: 'DELETE',
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
