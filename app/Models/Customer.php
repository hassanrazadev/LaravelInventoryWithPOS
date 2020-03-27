<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model{


    protected $fillable = [
        'name', 'email', 'phone', 'address', 'image', 'created_by', 'updated_by'
    ];

    // ===================== ORM Definition START ===================== //

    /**
     * @return HasMany
     */
    public function orders(){
        return $this->hasMany(Order::class);
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
