@extends('layouts.master')

@section('title', 'Create Order / POS')

@section('body-classes', ' sidebar-mini')

@section('css')
    <link href="{{asset('assets/css/select2.min.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('assets/css/cropper.css')}}" rel="stylesheet" type="text/css">
    <style>
        span.select2-container {
            width: 100% !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-content fade-in-up">
        <div class="row">
            <div class="col-lg-4 col-md-5 col-12">
                <div class="ibox">
                    <div class="ibox-body">
                        <div class="form-group">
                            <div class="input-group">
                                <select class="form-control select2_select select2-hidden-accessible" name="customer_id"
                                        id="customer_id">
                                    <option></option>
                                    <option>Usman</option>
                                    <option>Hamza</option>
                                </select>
                                <div class="input-group-btn">
                                    <button class="btn btn-primary" type="button"><i class="fa fa-plus-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="text" placeholder="Search product by name, code or barcode"
                                   class="form-control" id="search_product">
                        </div>
                        <table class="table table-responsive table-striped">
                            <thead class="bg-secondary text-white">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>HP USB (200)</td>
                                <td>400</td>
                                <td>2</td>
                                <td>800</td>
                                <td><a href="#"><i class="fa fa-trash"></i></a></td>
                            </tr>
                            <tr>
                                <td>HP USB (200)</td>
                                <td>400</td>
                                <td>2</td>
                                <td>800</td>
                                <td><a href="#"><i class="fa fa-trash"></i></a></td>
                            </tr>
                            <tr>
                                <td>HP USB (200)</td>
                                <td>400</td>
                                <td>2</td>
                                <td>800</td>
                                <td><a href="#"><i class="fa fa-trash"></i></a></td>
                            </tr>
                            <tr>
                                <td>HP USB (200)</td>
                                <td>400</td>
                                <td>2</td>
                                <td>800</td>
                                <td><a href="#"><i class="fa fa-trash"></i></a></td>
                            </tr>
                            <tr>
                                <td>HP USB (200)</td>
                                <td>400</td>
                                <td>2</td>
                                <td>800</td>
                                <td><a href="#"><i class="fa fa-trash"></i></a></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-md-7 col-12"></div>
        </div>
    </div>
    {{--    @include('categories.form')--}}
@endsection

@section('js')
    <script src="{{asset('assets/js/select2.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/js/jquery.validate.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/js/categories.js')}}" type="text/javascript"></script>
@endsection