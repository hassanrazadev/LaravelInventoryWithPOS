<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Purchase extends Model{

    use SoftDeletes;

    protected $fillable = [
        'created_by', 'updated_by', 'supplier_id', 'total'
    ];

    // ===================== ORM Definition START ===================== //

    /**
     * @return BelongsTo
     */
    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsToMany
     */
    public function products(){
        return $this->belongsToMany(Product::class, 'purchase_details')->withPivot('quantity', 'unit_price', 'sub_total');
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
    // ===================== ORM Definition END ===================== //


    /**
     * @return array
     */
    public function format(){
        return [
            'id' => $this->id,
            'supplier' => $this->supplier ? $this->supplier->name : '-',
            'created_by' => $this->createdBy->name,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : '-',
            'no_of_products' => count($this->products),
            'total_quantity' => $this->products->sum('pivot.quantity'),
            'total' => $this->total,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'products' => $this->products->map(function ($product){
                return [
                    'product_id' => $product->pivot->product_id,
                    'product_name' => $product->product_name,
                    'product_code' => $product->product_code,
                    'unit_price' => $product->pivot->unit_price,
                    'sale_price' => $product->sale_price,
                    'quantity' => $product->pivot->quantity,
                    'sub_total' => $product->pivot->sub_total,
                ];
            }),
        ];
    }

    /**
     * @param bool $withTrashed
     * @return Collection
     */
    public function getPurchases($withTrashed = false){
        $purchases = $this->newQuery()
            ->when($withTrashed, function ($query){
                $query->withTrashed();
            })
            ->with(['products' => function($p){
                $p->withTrashed();
            }, 'supplier' => function($s){
                $s->withTrashed();
            }, 'createdBy' => function($c){
                $c->withTrashed();
            }])->get()->map(function ($purchase){
                return $purchase->format();
            });
        return $purchases;
    }

    /**
     * @param $id
     * @param bool $withTrashed
     * @return array
     */
    public function getPurchaseById($id, $withTrashed = false){
        $data = [];
        $purchase = $this->newQuery()
            ->where('id', $id)
            ->when($withTrashed, function ($query){
                $query->withTrashed();
            })
            ->with(['products' => function($p){
                $p->withTrashed();
            }, 'supplier' => function($s){
                $s->withTrashed();
            }, 'createdBy' => function($c){
                $c->withTrashed();
            }])->firstOrFail()->format();
        if ($purchase){
            $data['status'] = true;
            $data['purchase'] = $purchase;
        }else{
            $data['status'] = false;
            $data['purchase'] = [];
        }
        return $data;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function storePurchase(array $fields) {
        $data = [];
        $fields['created_by'] = auth()->id();
        $purchase = $this::create($fields);
        if ($purchase){
            $totalPrice = 0;
            $data['status'] = true;
            $data['message'] = "New purchase was added";
            for ($i = 0; $i < count($fields['product_id']); $i ++){
                $quantity = $fields['quantity'][$i];
                $unitPrice = $fields['unit_price'][$i];
                $subTotal = $quantity * $unitPrice;
                $totalPrice += $subTotal;
                // increment product quantity
                Product::findOrFail($fields['product_id'][$i])->increment('quantity', $quantity);
                // add product purchase detail in purchase_details table
                $purchase->products()->attach($fields['product_id'][$i], [
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'sub_total' => $subTotal
                ]);
            }
            $purchase->total = $totalPrice;
            $purchase->save();
        }else{
            $data['status'] = false;
            $data['message'] = "There is problem adding purchase";
        }
        return $data;
    }

    /**
     * @param array $fields
     * @param int $id
     * @return mixed
     */
    public function updatePurchase(array $fields, int $id) {
        $purchase = $this::findOrFail($id);

        foreach ($purchase->products()->get() as $product){
            $product->decrement('quantity', $product->pivot->quantity);
        }
        // detach all products against this purchase
        $purchase->products()->detach();
        // attach products with purchase again
        $totalPrice = 0;
        for ($i = 0; $i < count($fields['product_id']); $i ++){
            $quantity = $fields['quantity'][$i];
            $unitPrice = $fields['unit_price'][$i];
            $subTotal = $quantity * $unitPrice;
            $totalPrice += $subTotal;
            // increment product quantity
            Product::findOrFail($fields['product_id'][$i])->increment('quantity', $quantity);
            // add product purchase detail in purchase_details table
            $purchase->products()->attach($fields['product_id'][$i], [
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'sub_total' => $subTotal
            ]);
        }
        $fields['total'] = $totalPrice;
        $fields['updated_by'] = auth()->id();
        $purchase->update($fields);

        $data['status'] = true;
        $data['message'] = "Purchase was updated";

        return $data;
    }

    /**
     * @param int $id
     * @return array
     */
    public function destroyPurchase(int $id) {
        $purchase = $this::withTrashed()->findOrFail($id);
        foreach ($purchase->products()->get() as $product){
            $product->decrement('quantity', $product->pivot->quantity);
        }
        $data = [];
        if ($purchase->forceDelete()){
            $data['status'] = true;
            $data['message'] = "Purchase was deleted";
        }else{
            foreach ($purchase->products()->get() as $product){
                $product->increment('quantity', $product->pivot->quantity);
            }
            $data['status'] = false;
            $data['message'] = "There is problem deleting purchase";
        }

        return $data;
    }

    /**
     * @param $id
     * @return array
     */
    public function deleteOrRestore($id) {
        $data = [];
        $purchase = $this::withTrashed()->findOrFail($id);
        if ($purchase){
            if ($purchase->trashed()){
                $purchase->restore();
            }else{
                try {
                    $purchase->delete();
                } catch (Exception $e) {
                }
            }
            $data['status'] = true;
            $data['message'] = "Purchase status was updated";
        }else {
            $data['status'] = false;
            $data['message'] = "Purchase not found";
        }

        return $data;
    }
}
