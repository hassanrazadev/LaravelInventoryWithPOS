@extends('layouts.master')

@section('title', 'Create Order / POS')

@section('body-classes', ' sidebar-mini')

@section('css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" type="text/css"/>
    <style>
        span.select2-container {
            width: 100% !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-heading ims-page-header">
        <div>
            <h1 class="page-title">Point of Sale</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fa fa-home font-18"></i></a></li>
                <li class="breadcrumb-item">Create Order / POS</li>
            </ol>
        </div>
        <span class="badge badge-soft-warning">POS Checkout Preview (Coming in Milestone 2)</span>
    </div>

    <div class="page-content fade-in-up">
        <div class="row">
            <div class="col-lg-5 col-md-6 col-12">
                <div class="ibox">
                    <div class="ibox-head">
                        <div class="ibox-title">Current Cart</div>
                    </div>
                    <div class="ibox-body">
                        <div class="form-group">
                            <div class="input-group">
                                <select class="form-control select2_customer" name="customer_id" id="customer_id">
                                    <option></option>
                                    <option>Walk-in Customer</option>
                                    <option>Ayesha Khan</option>
                                    <option>Usman Ali</option>
                                </select>
                                <div class="input-group-append ml-2">
                                    <button class="btn btn-primary" type="button" title="Add customer"><i class="fa fa-plus-circle"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="text" placeholder="Search product by name, code or barcode" class="form-control" id="search_product">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
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
                                    <td>Sample Item</td>
                                    <td>400</td>
                                    <td>2</td>
                                    <td>800</td>
                                    <td><a href="javascript:;" class="text-danger"><i class="fa fa-trash"></i></a></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 col-md-6 col-12">
                <div class="ibox">
                    <div class="ibox-head">
                        <div class="ibox-title">Product Catalog</div>
                    </div>
                    <div class="ibox-body text-center py-5 text-muted">
                        <i class="fa fa-shopping-basket fa-3x mb-3 text-primary"></i>
                        <p class="mb-0">Live product grid, barcode search, and atomic checkout will be wired in Milestone 2.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/js/select2.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        $('.select2_customer').select2({
            placeholder: 'Select customer',
            allowClear: true
        });
    </script>
@endsection
