<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model{

    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'image', 'address', 'created_by', 'updated_by'
    ];

    // ===================== ORM Definition START ===================== //

    /**
     * @return HasMany
     */
    public function products(){
        return $this->hasMany(Product::class);
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
