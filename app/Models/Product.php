<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model{

    protected $fillable = [
        'category_id', 'created_by', 'updated_by', 'product_name', 'product_code', 'quantity', 'sale_price', 'product_image', 'product_slug'
    ];

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
     * @return BelongsTo
     */
    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return HasMany
     */
    public function orderDetails(){
        return $this->hasMany(OrderDetail::class);
    }

    // ===================== ORM Definition END ===================== //
}
