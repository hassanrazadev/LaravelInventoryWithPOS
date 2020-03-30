<?php

namespace App\Models;

use App\Utils\AppUtils;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'created_by', 'updated_by', 'product_name', 'product_code', 'quantity', 'sale_price', 'product_image', 'product_slug'
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    // ===================== ORM Definition START ===================== //

    /**
     * @return BelongsTo
     */
    public function category(){
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo
     */
    public function createdBy(){
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo
     */
    public function updatedBy(){
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return BelongsToMany
     */
    public function suppliers(){
        return $this->belongsToMany(Supplier::class);
    }

    /**
     * @return BelongsToMany
     */
    public function purchases(){
        return $this->belongsToMany(Purchase::class, 'purchase_details')->withPivot('quantity', 'unit_price', 'sub_total');
    }

    /**
     * @return HasMany
     */
    public function orderDetails(){
        return $this->hasMany(OrderDetail::class);
    }
    // ===================== ORM Definition END ===================== //

    /**
     * get specified product by id
     * @param int $id
     * @param bool $withTrashed
     * @return array
     */
    public function getProductById(int $id, $withTrashed = false) {
        $product = $this->newQuery()
            ->where('id', $id)
            ->when($withTrashed, function ($query){
                $query->withTrashed();
            })
            ->with([
                'category' => function ($query){
                    $query->withTrashed();
                }
            ])->firstOrFail()->format();

        $data = [];
        if ($product){
            $data['status'] = true;
            $data['product'] = $product;
        }else{
            $data['status'] = false;
            $data['product'] = [];
        }
        return $data;
    }

    /**
     * @param string $slug
     * @param bool $withTrashed
     * @return mixed
     */
    public function getProductBySlug(string $slug, $withTrashed = false) {
        $product = $this::where('product_slug', $slug)
            ->when($withTrashed, function ($query){
                $query->withTrashed();
            })
            ->with([
                'category' => function ($query){
                    $query->withTrashed();
                },
                'purchases'
            ])->firstOrFail()->format();

        $data['status'] = true;
        $data['product'] = $product;
        return $data;
    }
    /**
     * @param bool $withTrashed
     * @return Collection
     */
    public function getProducts($withTrashed = false) {
        return $this->when($withTrashed, function ($query){
                $query->withTrashed();
            })
            ->with('category')
            ->get()->map(function ($product){
                return $product->format();
            });
    }
    /**
     * format product object
     * @return array
     */
    public function format(){
        return [
            'id' => $this->id,
            'category' => $this->category->category_name,
            'category_id' => $this->category->id,
            'created_by' => $this->createdBy->name,
            'updated_by' => $this->updateddBy ? $this->updateddBy->name : '-',
            'product_name' => $this->product_name,
            'product_code' => $this->product_code,
            'product_slug' => $this->product_slug,
            'product_image' => asset('storage/'.$this->product_image),
            'quantity' => $this->quantity,
            'sale_price' => $this->sale_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
            'purchases' => $this->purchases->map(function ($purchase){
                return [
                    'purchase_id' => $purchase->id,
                    'supplier' => $purchase->supplier ? $purchase->supplier->name : '-',
                    'quantity' => $purchase->pivot->quantity,
                    'unit_price' => $purchase->pivot->unit_price,
                    'created_at' => $purchase->created_at,
                    'sub_total' => $purchase->pivot->sub_total,
                ];
            })
        ];
    }

    /**
     * store new product
     * @param array $fields
     * @return array
     */
    public function storeProduct(array $fields) {
        $productImage = $fields['product_image'];
        $productImage = AppUtils::base64ToImage($productImage);
        $imageName = time() . 'product.png';
        $fields['created_by'] = auth()->id();
        $fields['product_image'] = 'product/' . $imageName;
        $product = $this::create($fields);

        $data = [];
        if ($product) {
            $data['status'] = true;
            $data['message'] = "New product was added";
            Storage::disk()->put('product/' . $imageName, $productImage);
        } else {
            $data['status'] = false;
            $data['message'] = "There is problem adding new product";
        }

        return $data;
    }

    /**
     * @param array $fields
     * @param int $id
     * @return mixed
     */
    public function updateProduct(array $fields, int $id) {
        $product = $this::withTrashed()->findOrFail($id);
        if ($product){
            if ($fields['product_image']){
                $oldImage = $product->category_image;
                $categoryImage = $fields['product_image'];
                $categoryImage = AppUtils::base64ToImage($categoryImage);
                $imageName = time() . 'product.png';
                $fields['product_image'] = 'product/' . $imageName;
                Storage::disk()->put('product/' . $imageName, $categoryImage);
                unlink(storage_path('app/public/' . $oldImage));
            }else{
                unset($fields['product_image']);
            }

            $fields['updated_by'] = auth()->id();
            $product->update($fields);
            $data['status'] = true;
            $data['message'] = "Product was updated";
        }else{
            $data['status'] = false;
            $data['message'] = "There is problem updating product";
        }

        return $data;
    }

    /**
     * @param int $id
     * @return array
     */
    public function destroyProduct(int $id) {
        $product = $this::withTrashed()->where('id', $id)->firstOrFail();
        $data = [];
        if ($product){
            $oldImage = $product->product_image;
            $product->forceDelete();
            unlink(storage_path('app/public/' . $oldImage));
            $data['status'] = true;
            $data['message'] = "Product was deleted";
        }else{
            $data['status'] = false;
            $data['message'] = "There is problem deleting product";
        }

        return $data;
    }

    /**
     * @param $id
     * @return array
     */
    public function deleteOrRestore($id) {
        $data = [];
        $product = $this::withTrashed()->findOrFail($id);
        if ($product){
            if ($product->trashed()){
                $product->restore();
            }else{
                try {
                    $product->delete();
                } catch (Exception $e) {
                }
            }
            $data['status'] = true;
            $data['message'] = "Product status was updated";
        }else {
            $data['status'] = false;
            $data['message'] = "Product not found";
        }

        return $data;
    }


}
