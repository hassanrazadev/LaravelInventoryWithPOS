@extends('layouts.master')

@section('title', 'Manage Roles')

@section('css')
    <link href="{{ asset('assets/css/datatables.min.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="page-heading ims-page-header">
        <div>
            <h1 class="page-title">Manage Roles</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item">Roles</li>
            </ol>
        </div>
        @can('create role')
            <a href="{{ route('roles.create') }}" class="btn btn-primary">
                <i class="fa fa-plus mr-1"></i> Create Role
            </a>
        @endcan
    </div>

    <div class="page-content fade-in-up">
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">Roles</div>
            </div>
            <div class="ibox-body">
                <table class="table table-striped table-bordered table-hover" id="roles-table" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Role Name</th>
                        <th>Users</th>
                        <th>Permissions</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->id }}</td>
                            <td class="font-weight-medium text-capitalize">{{ $role->name }}</td>
                            <td>
                                <span class="badge badge-soft-secondary">{{ $role->users_count }} {{ \Illuminate\Support\Str::plural('user', $role->users_count) }}</span>
                            </td>
                            <td>
                                @if($role->name === 'admin')
                                    <span class="badge badge-soft-primary">All Permissions (Super Admin)</span>
                                @else
                                    <span class="badge badge-soft-success">{{ $role->permissions->count() }} assigned</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @can('update role')
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary btn-xs m-r-5" data-toggle="tooltip" data-original-title="Edit"><i class="fa fa-pencil font-14"></i></a>
                                @endcan
                                @can('delete role')
                                    @if($role->name !== 'admin')
                                        <a href="{{ route('roles.destroy', $role->id) }}" class="btn btn-danger delete-role btn-xs" data-toggle="tooltip" data-original-title="Delete"><i class="fa fa-trash font-14"></i></a>
                                    @endif
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
        let dataTable = $('#roles-table').DataTable({
            pageLength: 10
        });

        $(document).on('click', '.delete-role', function (e) {
            e.preventDefault();
            if (confirm('Do you really want to permanently delete this role?')) {
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
