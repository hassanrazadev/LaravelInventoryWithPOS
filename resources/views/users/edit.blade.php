@extends('layouts.master')

@section('title', 'Edit User')

@section('css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1 class="page-title">Edit User</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                <li class="breadcrumb-item">Edit</li>
            </ol>
        </div>
    </div>

    @include('users.form')
@endsection

@section('js')
    <script src="{{ asset('assets/js/select2.min.js') }}" type="text/javascript"></script>
    <script>
        $('.select2_roles').select2({
            placeholder: 'Select one or more roles',
            width: '100%'
        });
    </script>
@endsection
