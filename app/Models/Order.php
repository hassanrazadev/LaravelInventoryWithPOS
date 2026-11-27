<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'card' => 'Card',
        'bank_transfer' => 'Bank Transfer',
        'other' => 'Other',
    ];

    protected $fillable = [
        'customer_id',
        'created_by',
        'updated_by',
        'order_no',
        'total_items',
        'sub_total',
        'tax',
        'discount',
        'total',
        'payment_method',
        'paid_amount',
        'change_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_items' => 'integer',
            'sub_total' => 'decimal:2',
            'tax' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
        ];
    }

    // ===================== ORM Definition START ===================== //

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    // ===================== ORM Definition END ===================== //

    /**
     * Generate a unique invoice/order number for POS checkouts.
     */
    public static function generateOrderNumber(): string
    {
        do {
            $candidate = 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
        } while (self::withTrashed()->where('order_no', $candidate)->exists());

        return $candidate;
    }

    public function paymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? ucfirst((string) $this->payment_method);
    }
}
