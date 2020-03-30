<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseFormRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PurchaseController extends Controller {

    /**
     * @var Purchase
     */
    private $purchase;
    /**
     * @var Product
     */
    private $product;

    /**
     * PurchaseController constructor.
     * @param Purchase $purchase
     * @param Product $product
     */
    public function __construct(Purchase $purchase, Product $product) {
        $this->purchase = $purchase;
        $this->product = $product;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $purchases = $this->purchase->getPurchases(true);
        return view('purchases.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $suppliers = Supplier::get();
        $products = $this->product->getProducts();
        return view('purchases.create', compact('suppliers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PurchaseFormRequest $request
     * @return void
     */
    public function store(PurchaseFormRequest $request) {
        $fields = $request->validated();
        $data = $this->purchase->storePurchase($fields);
        return redirect()->route('purchases.index')->with($data);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id) {
        $data = $this->purchase->getPurchaseById($id);
        return view('purchases.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function edit($id) {
        $data = $this->purchase->getPurchaseById($id, true);
        $data['suppliers'] = Supplier::get();
        $data['products'] = $this->product->getProducts();
        return view('purchases.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param PurchaseFormRequest $request
     * @param int $id
     * @return Response
     */
    public function update(PurchaseFormRequest $request, $id) {
        $fields = $request->validated();
        $data = $this->purchase->updatePurchase($fields, $id);
        return redirect()->route('purchases.index')->with( $data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id) {
        $data = $this->purchase->destroyPurchase($id);
        return json_encode($data);
    }

    /**
     * @param $id
     * @return false|string
     */
    public function deleteOrRestore($id){
        $data = $this->purchase->deleteOrRestore($id);

        return json_encode($data);
    }

    /**
     * return product form to purchases form
     * @param $index
     * @return Factory|View
     */
    public function productForm($index){
        $products = $this->product->getProducts();
        return view('purchases.product-form', [
            'products' => $products,
            'index' => $index
        ]);
    }
}
