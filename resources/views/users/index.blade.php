@extends('layouts.master')

@section('title', 'Manage Users')

@section('css')
    <link href="{{ asset('assets/css/datatables.min.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="page-heading ims-page-header">
        <div>
            <h1 class="page-title">Manage Users</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a>
                </li>
                <li class="breadcrumb-item">Users</li>
            </ol>
        </div>
        @can('create user')
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fa fa-plus mr-1"></i> Add User
            </a>
        @endcan
    </div>

    <div class="page-content fade-in-up">
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">Users</div>
            </div>
            <div class="ibox-body">
                <table class="table table-striped table-bordered table-hover" id="users-table" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Verified</th>
                        <th>Created At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $item)
                        <tr data-index="{{ $item->id }}">
                            <td>{{ $item->id }}</td>
                            <td class="font-weight-medium">{{ $item->name }}</td>
                            <td>{{ $item->email }}</td>
                            <td>
                                @foreach($item->roles as $role)
                                    <span class="badge badge-soft-primary text-capitalize">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if($item->email_verified_at)
                                    <span class="badge badge-soft-success">Verified</span>
                                @else
                                    <span class="badge badge-soft-warning">Unverified</span>
                                @endif
                            </td>
                            <td>{{ $item->created_at }}</td>
                            <td class="text-center">
                                <a href="{{ route('users.show', $item->id) }}" class="btn btn-info btn-xs m-r-5" data-toggle="tooltip" data-original-title="View"><i class="fa fa-eye font-14"></i></a>
                                @can('update user')
                                    <a href="{{ route('users.edit', $item->id) }}" class="btn btn-primary btn-xs m-r-5" data-toggle="tooltip" data-original-title="Edit"><i class="fa fa-pencil font-14"></i></a>
                                @endcan
                                @can('delete user')
                                    @if($item->id !== auth()->id())
                                        <a href="{{ route('users.destroy', $item->id) }}" class="btn btn-danger delete-user btn-xs m-r-5" data-toggle="tooltip" data-original-title="Delete"><i class="fa fa-trash font-14"></i></a>
                                        <div class="switch_box">
                                            <div class="input_wrapper">
                                                <input type="checkbox" data-url="{{ route('users.delete', $item->id) }}" {{ $item->deleted_at === null ? 'checked' : '' }} class="switch_4 soft-delete-user">
                                                <svg class="is_checked" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 426.67 426.67">
                                                    <path d="M153.504 366.84c-8.657 0-17.323-3.303-23.927-9.912L9.914 237.265c-13.218-13.218-13.218-34.645 0-47.863 13.218-13.218 34.645-13.218 47.863 0l95.727 95.727 215.39-215.387c13.218-13.214 34.65-13.218 47.86 0 13.22 13.218 13.22 34.65 0 47.863L177.435 356.928c-6.61 6.605-15.27 9.91-23.932 9.91z"/>
                                                </svg>
                                                <svg class="is_unchecked" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 212.982 212.982">
                                                    <path d="M131.804 106.49l75.936-75.935c6.99-6.99 6.99-18.323 0-25.312-6.99-6.99-18.322-6.99-25.312 0L106.49 81.18 30.555 5.242c-6.99-6.99-18.322-6.99-25.312 0-6.99 6.99-6.99 18.323 0 25.312L81.18 106.49 5.24 182.427c-6.99 6.99-6.99 18.323 0 25.312 6.99 6.99 18.322 6.99 25.312 0L106.49 131.8l75.938 75.937c6.99 6.99 18.322 6.99 25.312 0 6.99-6.99 6.99-18.323 0-25.313l-75.936-75.936z" fill-rule="evenodd" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </div>
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
        let dataTable = $('#users-table').DataTable({
            pageLength: 10
        });

        $(document).on('change', '.soft-delete-user', function () {
            let checkbox = $(this);
            let url = checkbox.attr('data-url');

            $.ajax({
                url: url,
                type: 'PATCH',
                success: function (response) {
                    let data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.status) {
                        showToast('Success', data.message, 'success');
                    } else {
                        checkbox.prop('checked', !checkbox.prop('checked'));
                        showToast('Error', data.message, 'error');
                    }
                }
            });
        });

        $(document).on('click', '.delete-user', function (e) {
            e.preventDefault();
            if (confirm('Do you really want to permanently delete this user?')) {
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
