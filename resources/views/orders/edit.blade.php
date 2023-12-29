@extends('layouts.master')

@section('title', 'Edit Category')

@section('css')
    <link href="{{asset('assets/css/select2.min.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('assets/css/cropper.css')}}" rel="stylesheet" type="text/css">
    <style>
        span.select2-container{
            width: 100% !important;
        }
    </style>
@endsection

@section('content')

    @include('partials._crop-image-modal')

    <div class="page-heading">
        <h1 class="page-title">Edi Category</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('dashboard')}}"><i class="fa fa-home font-20"></i></a>
            </li>
            <li class="breadcrumb-item">Edit Category</li>
        </ol>
    </div>

    @include('categories.form')
@endsection

@section('js')
    <script src="{{asset('assets/js/select2.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/js/jquery.validate.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/js/cropper.js')}}" type="text/javascript"></script>
    <script type="text/javascript">
        let rules = {
            category_name: {
                minlength: 3,
                required: !0
            },
            category_code: {
                minlength: 3,
                required: !0
            },
            category_slug: {
                minlength: 3,
                required: !0
            },
        }
    </script>
    <script src="{{asset('assets/js/image-cropper-modal.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/js/categories.js')}}" type="text/javascript"></script>
@endsection