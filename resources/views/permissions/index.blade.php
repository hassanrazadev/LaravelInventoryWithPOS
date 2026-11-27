@extends('layouts.master')

@section('title', 'Manage Permissions')

@section('css')
    <link href="{{ asset('assets/css/datatables.min.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="page-heading ims-page-header">
        <div>
            <h1 class="page-title">Manage Permissions</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item">Permissions</li>
            </ol>
        </div>
        @can('create permission')
            <a href="{{ route('permissions.create') }}" class="btn btn-primary">
                <i class="fa fa-plus mr-1"></i> Create Permission
            </a>
        @endcan
    </div>

    <div class="page-content fade-in-up">
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">Permissions</div>
            </div>
            <div class="ibox-body">
                <table class="table table-striped table-bordered table-hover" id="permissions-table" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Permission Name</th>
                        <th>Guard</th>
                        <th>Assigned Roles</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($permissions as $permission)
                        <tr>
                            <td>{{ $permission->id }}</td>
                            <td class="font-weight-medium">{{ $permission->name }}</td>
                            <td><span class="badge badge-soft-secondary">{{ $permission->guard_name }}</span></td>
                            <td><span class="badge badge-soft-primary">{{ $permission->roles_count }} {{ \Illuminate\Support\Str::plural('role', $permission->roles_count) }}</span></td>
                            <td class="text-center">
                                @can('update permission')
                                    <a href="{{ route('permissions.edit', $permission->id) }}" class="btn btn-primary btn-xs m-r-5" data-toggle="tooltip" data-original-title="Edit"><i class="fa fa-pencil font-14"></i></a>
                                @endcan
                                @can('delete permission')
                                    <a href="{{ route('permissions.destroy', $permission->id) }}" class="btn btn-danger delete-permission btn-xs" data-toggle="tooltip" data-original-title="Delete"><i class="fa fa-trash font-14"></i></a>
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
        let dataTable = $('#permissions-table').DataTable({
            pageLength: 10
        });

        $(document).on('click', '.delete-permission', function (e) {
            e.preventDefault();
            if (confirm('Do you really want to permanently delete this permission?')) {
                let row = $(this).parents('tr');
                let url = $(this).attr('href');
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    success: function (response) {
                        let data = typeof response === 'string' ? JSON.parse(response) : response;
                        if (data.status) {
                            showToast('Success', data.message, 'success');
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
