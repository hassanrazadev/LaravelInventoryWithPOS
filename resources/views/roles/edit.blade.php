@extends('layouts.master')

@section('title', 'Edit Role')

@section('content')
    <div class="page-heading">
        <div>
            <h1 class="page-title">Edit Role</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                <li class="breadcrumb-item">Edit</li>
            </ol>
        </div>
    </div>

    @include('roles.form')
@endsection

@section('js')
    <script>
        function syncPermissionSelectAllState() {
            $('.toggle-group-permissions').each(function () {
                let group = $(this).data('group');
                let total = $('.permission-checkbox[data-group="' + group + '"]').length;
                let checked = $('.permission-checkbox[data-group="' + group + '"]:checked').length;
                $(this).prop('checked', total > 0 && total === checked);
            });

            let allTotal = $('.permission-checkbox').length;
            let allChecked = $('.permission-checkbox:checked').length;
            $('#toggle-all-permissions').prop('checked', allTotal > 0 && allTotal === allChecked);
        }

        $(document).on('change', '#toggle-all-permissions', function () {
            $('.permission-checkbox').prop('checked', $(this).prop('checked'));
            syncPermissionSelectAllState();
        });

        $(document).on('change', '.toggle-group-permissions', function () {
            let group = $(this).data('group');
            $('.permission-checkbox[data-group="' + group + '"]').prop('checked', $(this).prop('checked'));
            syncPermissionSelectAllState();
        });

        $(document).on('change', '.permission-checkbox', syncPermissionSelectAllState);

        $(function () {
            syncPermissionSelectAllState();
        });
    </script>
@endsection
