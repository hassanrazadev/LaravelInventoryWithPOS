<div class="col-12 product-row border border-top-0 border-left-0 border-right-0 pt-3" data-index="{{$index}}">
    <div class="row">
        <div class="col-md-5">
            <div class="form-group required row {{\App\Utils\AppUtils::formGroupError($errors, 'product_id.'.$index)}}">
                <label for="product_id_{{$index}}" class="col-sm-3 col-form-label">Product:</label>
                <div class="col-sm-9">
                    <select class="form-control unique select2_select select2-hidden-accessible product_id" name="product_id[{{$index}}]" id="product_id_{{$index}}" data-placeholder="Select Product">
                        <option></option>
                        @foreach($products as $product)
                            <option value="{{$product['id']}}" {{isset($prod) ? ($prod['product_id'] == $product['id'] ? 'selected' : '') : (old('product_id.'.$index) == $product['id'] ? 'selected' : '')}}>{{$product['product_name']}}</option>
                        @endforeach
                    </select>
                    @include('partials._error', ['field' => 'product_id.'.$index])
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="form-group required row {{\App\Utils\AppUtils::formGroupError($errors, 'quantity.'.$index)}}">
                <label for="quantity_{{$index}}" class="col-sm-3 col-form-label">Quantity:</label>
                <div class="col-sm-9">
                    <input class="form-control {{\App\Utils\AppUtils::inputFieldError($errors, 'quantity.'.$index)}} quantity" name="quantity[{{$index}}]" value="{{isset($prod) ? $prod['quantity'] : old('quantity.'.$index)}}" id="quantity_{{$index}}" type="number" placeholder="Quantity">
                    @include('partials._error', ['field' => 'quantity.'.$index])
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <a href="#" class="btn btn-danger remove-product mr-3" data-index="{{$index}}" data-toggle="tooltip" data-original-title="Remove Product"><i class="fa fa-minus font-14"></i></a>
        </div>
        <div class="col-md-5">
            <div class="form-group required row {{\App\Utils\AppUtils::formGroupError($errors, 'unit_price.'.$index)}}">
                <label for="unit_price_{{$index}}" class="col-sm-3 col-form-label">Unit Price:</label>
                <div class="col-sm-9">
                    <input class="form-control {{\App\Utils\AppUtils::inputFieldError($errors, 'unit_price.'.$index)}} unit_price" name="unit_price[{{$index}}]" value="{{isset($prod) ? $prod['unit_price'] : old('unit_price.'.$index)}}" id="unit_price_{{$index}}" type="number" placeholder="Unit Price">
                    @include('partials._error', ['field' => 'unit_price.'.$index])
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="form-group required row {{\App\Utils\AppUtils::formGroupError($errors, 'sub_total.'.$index)}}">
                <label for="sub_total_{{$index}}" class="col-sm-3 col-form-label">Sub Total:</label>
                <div class="col-sm-9">
                    <input class="form-control {{\App\Utils\AppUtils::inputFieldError($errors, 'sub_total.'.$index)}} sub_total" name="sub_total[{{$index}}]" value="{{isset($prod) ? $prod['sub_total'] : old('sub_total.'.$index)}}" id="sub_total_{{$index}}" type="number" placeholder="Sub Total">
                    @include('partials._error', ['field' => 'sub_total.'.$index])
                </div>
            </div>
        </div>
    </div>
</div>