@extends('layouts.master')

@section('title', 'Create Product')

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
        <h1 class="page-title">Create Product</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('dashboard')}}"><i class="fa fa-home font-20"></i></a>
            </li>
            <li class="breadcrumb-item">Create Product</li>
        </ol>
    </div>

    @include('products.form')
@endsection

@section('js')
    <script src="{{asset('assets/js/select2.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/js/jquery.validate.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/js/cropper.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/js/image-cropper-modal.js')}}" type="text/javascript"></script>
    <script type="text/javascript">
        let rules = {
            product_name: {
                minlength: 5,
                required: !0
            },
            product_code: {
                minlength: 5,
                required: !0
            },
            category_id: {
                required: !0
            },
            product_slug: {
                minlength: 5,
                required: !0
            },
            sale_price: {
                min: 1,
                required: !0
            },
            product_image: {
                minlength: 10,
                required: !0
            },
        };
    </script>
    <script src="{{asset('assets/js/products.js')}}" type="text/javascript"></script>
@endsection