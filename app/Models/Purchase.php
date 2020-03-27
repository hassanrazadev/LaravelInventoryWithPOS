<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model{

    protected $fillable = [
        'product_id', 'created_by', 'updated_by', 'supplier_id', 'unit_price', 'quantity', 'total'
    ];

    // ===================== ORM Definition START ===================== //

    /**
     * @return BelongsTo
     */
    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo
     */
    public function products(){
        return $this->belongsTo(Product::class);
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
}
