@extends('layouts.master')

@section('title', 'Add Customer')

@section('content')
    <div class="page-heading">
        <div>
            <h1 class="page-title">Add Customer</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
                <li class="breadcrumb-item">Create</li>
            </ol>
        </div>
    </div>

    @include('customers.form')
@endsection
