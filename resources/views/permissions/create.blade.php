@extends('layouts.master')

@section('title', 'Create Permission')

@section('content')
    <div class="page-heading">
        <div>
            <h1 class="page-title">Create Permission</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('permissions.index') }}">Permissions</a></li>
                <li class="breadcrumb-item">Create</li>
            </ol>
        </div>
    </div>

    @include('permissions.form')
@endsection
