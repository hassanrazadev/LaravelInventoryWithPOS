@extends('layouts.master')

@section('title', $category['category_name'])

@section('css')
    <link href="{{asset('assets/css/datatables.min.css')}}" rel="stylesheet" />
@endsection

@section('content')
    <div class="page-heading">
        <h1 class="page-title">Category Detail</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('dashboard')}}"><i class="fa fa-home font-20"></i></a>
            </li>
            <li class="breadcrumb-item">Category Detail</li>
        </ol>
    </div>

    <div class="page-content fade-in-up">
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">Category</div>
            </div>
            <div class="ibox-body">
                <div class="row">
                    <div class="col-md-2">
                        <img src="{{$category['category_image']}}" class="img-fluid" alt="category">
                    </div>
                    <div class="col-md-10">
                        <div class="row">
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Category Name:</div>
                                <div class="col-md-7">{{$category['category_name']}}</div>
                            </div>
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Parent Category:</div>
                                <div class="col-md-7">{{$category['parent_category']}}</div>
                            </div>
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Category Code:</div>
                                <div class="col-md-7">{{$category['category_code']}}</div>
                            </div>
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Category Slug:</div>
                                <div class="col-md-7">{{$category['category_slug']}}</div>
                            </div>
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Created By:</div>
                                <div class="col-md-7">{{$category['created_by']}}</div>
                            </div>
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Updated By:</div>
                                <div class="col-md-7">{{$category['updated_by']}}</div>
                            </div>
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Created At:</div>
                                <div class="col-md-7">{{$category['created_at']}}</div>
                            </div>
                            <div class="col-md-6 row mb-3">
                                <div class="col-5 font-weight-bold">Updated At:</div>
                                <div class="col-md-7">{{$category['updated_at']}}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <h6 class="m-b-20 m-t-20"><i class="fa fa-cubes"></i>Products</h6>
                <table id="products-table" class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Product Code</th>
                        <th>Sale Price</th>
                        <th>Quantity</th>
                        <th>Created At</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($category['products'] as $product)
                        <tr>
                            <td>{{$product['id']}}</td>
                            <td>{{$product['product_name']}}</td>
                            <td>{{$product['product_code']}}</td>
                            <td>{{$product['sale_price']}}</td>
                            <td>{{$product['quantity']}}</td>
                            <td>{{$product['created_at']}}</td>
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
        let dataTable = $('#products-table').DataTable({
            pageLength: 10,
        });
    </script>
@endsection