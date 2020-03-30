<div class="page-content">
    <div class="ibox">
        <div class="ibox-head">
            <div class="ibox-title">{{isset($purchase) ? 'Edit' : 'Create'}} Purchase</div>
        </div>
        <div class="ibox-body">
            <form action="{{isset($purchase) ? route('purchases.update', $purchase['id']) : route('purchases.store')}}" method="post" id="purchaseForm">
                @csrf
                @if(isset($purchase))
                    @method('PUT')
                @endif
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group row">
                            <label for="supplier_id" class="col-sm-3 col-form-label">Supplier:</label>
                            <div class="col-sm-9">
                                <select class="form-control select2_select select2-hidden-accessible" name="supplier_id" id="supplier_id" data-placeholder="Select supplier">
                                    <option></option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{$supplier->id}}" {{isset($purchase) ? ($purchase['supplier_id'] == $supplier->id ? 'selected' : '') : (old('supplier_id') == $supplier->id ? 'selected' : '')}}>{{$supplier->name}}</option>
                                    @endforeach
                                </select>
                                @include('partials._error', ['field' => 'supplier_id'])
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group required row">
                            <label for="total" class="col-sm-3 col-form-label">Total Price:</label>
                            <div class="col-sm-9">
                                <input class="form-control {{\App\Utils\AppUtils::inputFieldError($errors, 'total')}}" name="total" value="{{isset($purchase) ? $purchase['total'] : old('total')}}" id="total" type="number" placeholder="Total Price">
                                @include('partials._error', ['field' => 'total'])
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <a href="#" class="btn btn-success add-product m-r-5" data-url="{{route('purchases.productForm')}}" data-toggle="tooltip" data-original-title="Add Product"><i class="fa fa-plus font-14"></i></a>
                    </div>
                    <!-- product rows start -->
                    <div class="col-12 border-bottom-0 product-rows-start border"></div>
                    @if(old('product_id'))
                        @foreach(old('product_id') as $key => $value)
                            @include('purchases.product-form', ['index' => $key])
                        @endforeach
                    @elseif(isset($purchase))
                        @foreach($purchase['products'] as $key => $value)
                            @include('purchases.product-form', ['index' => $key, 'prod' => $value])
                        @endforeach
                     @else
                        @include('purchases.product-form', ['index' => 0])
                    @endif
                    <div class="col-12 border-bottom-0 product-rows-end border"></div>
                    <!-- product rows end -->
                    <div class="col-5 mt-3"></div>
                    <div class="col-md-5 mt-3">
                        <div class="form-group mb-0 row">
                            <label class="col-sm-3 col-form-label"></label>
                            <div class="col-sm-9">
                                <button type="submit" class="btn btn-primary btn-block">{{isset($purchase) ? 'Update' : 'Create'}} Purchase</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>