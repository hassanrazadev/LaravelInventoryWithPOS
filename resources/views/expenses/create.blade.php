@extends('layouts.master')

@section('title', 'Add Expense')

@section('content')
    <div class="page-heading">
        <div>
            <h1 class="page-title">Add Expense</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
                <li class="breadcrumb-item">Create</li>
            </ol>
        </div>
    </div>

    @include('expenses.form')
@endsection
