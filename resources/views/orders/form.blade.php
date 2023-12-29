<div class="page-content">
    <div class="ibox">
        <div class="ibox-head">
            <div class="ibox-title">{{isset($category) ? 'Edit' : 'Create'}} Category</div>
        </div>
        <div class="ibox-body">
            <div class="row">
                <div class="col-md-8">
                    <form action="{{isset($category) ? route('categories.update', $category['id']) : route('categories.store')}}" method="post" novalidate="novalidate" id="categoryForm" enctype="multipart/form-data">
                        @csrf
                        @if(isset($category))
                            @method('PUT')
                        @endif
                        <div class="form-group required row">
                            <label for="category_name" class="col-sm-3 col-form-label">Category Name:</label>
                            <div class="col-sm-9">
                                <input class="form-control {{\App\Utils\AppUtils::inputFieldError($errors, 'category_name')}}" name="category_name" value="{{isset($category) ? $category['category_name'] : old('category_name')}}" id="category_name" type="text" placeholder="Category Name">
                                @include('partials._error', ['field' => 'category_name'])
                            </div>
                        </div>
                        <div class="form-group required row">
                            <label for="category_code" class="col-sm-3 col-form-label">Category Code:</label>
                            <div class="col-sm-9">
                                <input class="form-control {{\App\Utils\AppUtils::inputFieldError($errors, 'category_code')}}" name="category_code" value="{{isset($category) ? $category['category_code'] : old('category_code')}}" id="category_code" type="text" placeholder="Category Code">
                                @include('partials._error', ['field' => 'category_code'])
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="parent_id" class="col-sm-3 col-form-label">Parent Category:</label>
                            <div class="col-sm-9">
                                <select class="form-control select2_select select2-hidden-accessible" name="parent_id" id="parent_id">
                                    <option></option>
                                    @foreach($categories as $cat)
                                        <option value="{{$cat['id']}}" {{isset($category) ? ($category['parent_id'] == $cat['id'] ? 'selected' : '') : (old('parent_id') == $cat['id'] ? 'selected' : '')}}>{{$cat['category_name']}}</option>
                                    @endforeach
                                </select>
                                @include('partials._error', ['field' => 'parent_id'])
                            </div>
                        </div>
                        <div class="form-group required row">
                            <label for="category_slug" class="col-sm-3 col-form-label">Category Slug:</label>
                            <div class="col-sm-9">
                                <input class="form-control {{\App\Utils\AppUtils::inputFieldError($errors, 'category_slug')}}" name="category_slug" value="{{isset($category) ? $category['category_slug'] : old('category_slug')}}" id="category_slug" type="text" placeholder="Category Slug">
                                @include('partials._error', ['field' => 'category_slug'])
                            </div>
                        </div>
                        <div class="form-group required row">
                            <label for="category_image_picker" class="col-sm-3 col-form-label">Category Image:</label>
                            <div class="col-sm-9">
                                <input class="form-control {{\App\Utils\AppUtils::inputFieldError($errors, 'category_image')}}"  id="category_image_picker" type="file" accept=".png, .jpg">
                                <input type="text" style="position: absolute; z-index: -1" name="category_image" value="" id="category_image">
                                @include('partials._error', ['field' => 'category_image'])
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label"></label>
                            <div class="col-sm-9">
                                <button type="submit" class="btn btn-primary btn-block">{{isset($category) ? 'Update' : 'Create'}} Category</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-4">
                    <img src="{{isset($category) ? $category['category_image'] : asset('assets/img/image.png')}}" class="img-fluid" id="categoryImagePreview" alt="category">
                </div>
            </div>
        </div>
    </div>
</div>