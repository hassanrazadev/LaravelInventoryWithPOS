<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFormRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller {

    /**
     * @var Category
     */
    private $category;
    /**
     * @var Product
     */
    private $product;

    /**
     * ProductController constructor.
     * @param Category $category
     * @param Product $product
     */
    public function __construct(Category $category, Product $product) {
        $this->category = $category;
        $this->product = $product;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $products = $this->product->getProducts(true);
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $categories = $this->category->getCategories();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage.
     *
     * @param ProductFormRequest $request
     * @return Response
     */
    public function store(ProductFormRequest $request) {
        $fields = $request->validated();
        $data = $this->product->storeProduct($fields);
        return redirect()->route('products.index')->with($data);
    }

    /**
     * Display the specified product.
     *
     * @param string $slug
     * @return Response
     */
    public function show($slug) {
        $data = $this->product->getProductBySlug($slug, true);
        return view('products.show', $data);
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param int $id
     * @return Response
     */
    public function edit($id) {
        $data = $this->product->getProductById($id);
        $data['categories'] = $this->category->getCategories();
        return view('products.edit', $data);
    }

    /**
     * Update the specified product in storage.
     *
     * @param ProductFormRequest $request
     * @param int $id
     * @return Response
     */
    public function update(ProductFormRequest $request, $id) {
        $fields = $request->validated();
        $data = $this->product->updateProduct($fields, $id);
        return redirect()->route('products.index')->with($data);
    }

    /**
     * Remove the specified product from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id) {
        $data = $this->product->destroyProduct($id);
        return json_encode($data);
    }

    public function deleteOrRestore($id){
        $data = $this->product->deleteOrRestore($id);

        return json_encode($data);
    }
}
