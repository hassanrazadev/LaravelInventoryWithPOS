@extends('layouts.master')

@section('title', 'Add Supplier')

@section('content')
    <div class="page-heading">
        <div>
            <h1 class="page-title">Add Supplier</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">Suppliers</a></li>
                <li class="breadcrumb-item">Create</li>
            </ol>
        </div>
    </div>

    @include('suppliers.form')
@endsection
