@extends('layouts.master')

@section('title', $product['product_name'])

@section('css')
    <link href="{{asset('assets/css/datatables.min.css')}}" rel="stylesheet" />
@endsection

@section('content')
    <div class="page-heading">
        <h1 class="page-title">Product Detail</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('dashboard')}}"><i class="fa fa-home font-20"></i></a>
            </li>
            <li class="breadcrumb-item">Product Detail</li>
        </ol>
    </div>

    <div class="page-content fade-in-up">
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">Product</div>
            </div>
            <div class="ibox-body">
                <div class="row">
                    <div class="col-md-2">
                        <img src="{{$product['product_image']}}" class="img-fluid" alt="product">
                    </div>
                    <div class="col-md-10">
                        <div class="row">
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Product Name:</div>
                                <div class="col-md-7">{{$product['product_name']}}</div>
                            </div>
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Category:</div>
                                <div class="col-md-7">{{$product['category']}}</div>
                            </div>
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Product Code:</div>
                                <div class="col-md-7">{{$product['product_code']}}</div>
                            </div>
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Product Slug:</div>
                                <div class="col-md-7">{{$product['product_slug']}}</div>
                            </div>
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Sale Price:</div>
                                <div class="col-md-7">{{$product['sale_price']}}</div>
                            </div>
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Quantity:</div>
                                <div class="col-md-7">{{$product['quantity']}}</div>
                            </div>
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Created By:</div>
                                <div class="col-md-7">{{$product['created_by']}}</div>
                            </div>
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Updated By:</div>
                                <div class="col-md-7">{{$product['updated_by']}}</div>
                            </div>
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Created At:</div>
                                <div class="col-md-7">{{$product['created_at']}}</div>
                            </div>
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Updated At:</div>
                                <div class="col-md-7">{{$product['updated_at']}}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="m-b-20 m-t-20"><i class="fa fa-briefcase"></i> Purchase History</h6>
                <table id="purchases-table" class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Supplier</th>
                        <th>Quantity</th>
                        <th>Purchase Price</th>
                        <th>Total</th>
                        <th>Created At</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($product['purchases'] as $purchase)
                        <tr>
                            <td>{{$purchase['purchase_id']}}</td>
                            <td>{{$purchase['supplier']}}</td>
                            <td>{{$purchase['quantity']}}</td>
                            <td>{{$purchase['unit_price']}}</td>
                            <td>{{$purchase['sub_total']}}</td>
                            <td>{{$purchase['created_at']}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script src="{{asset('assets/js/datatables.min.js')}}" type="text/javascript"></script>
    <script type="text/javascript">
        let dataTable = $('#purchases-table').DataTable({
            pageLength: 10,
            responsive: true,
        });
    </script>
@endsection