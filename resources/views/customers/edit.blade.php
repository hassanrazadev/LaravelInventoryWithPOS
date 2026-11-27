@extends('layouts.master')

@section('title', 'Edit Customer')

@section('content')
    <div class="page-heading">
        <div>
            <h1 class="page-title">Edit Customer</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
                <li class="breadcrumb-item">Edit</li>
            </ol>
        </div>
    </div>

    @include('customers.form')
@endsection
