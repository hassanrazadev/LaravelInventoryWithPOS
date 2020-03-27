<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model{

    protected $fillable = [
        'customer_id', 'created_by', 'updated_by', 'order_no', 'total_items', 'sub_total', 'tax', 'discount', 'total'
    ];

    // ===================== ORM Definition START ===================== //

    /**
     * @return BelongsTo
     */
    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasOne
     */
    public function orderDetail(){
        return $this->hasOne(OrderDetail::class);
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
