<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->string('payment_method', 30)->default('cash')->after('total');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('payment_method');
            $table->decimal('change_amount', 12, 2)->default(0)->after('paid_amount');
            $table->string('status', 20)->default('completed')->after('change_amount');
            $table->text('notes')->nullable()->after('status');

            $table->unique('order_no');
            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('sub_total', 12, 2)->change();
            $table->decimal('tax', 12, 2)->default(0)->change();
            $table->decimal('discount', 12, 2)->default(0)->change();
            $table->decimal('total', 12, 2)->change();
        });

        Schema::table('order_details', function (Blueprint $table): void {
            $table->decimal('unit_price', 12, 2)->default(0)->after('quantity');
            $table->decimal('sub_total', 12, 2)->default(0)->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table): void {
            $table->dropColumn(['unit_price', 'sub_total']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['updated_by']);
            $table->dropUnique(['order_no']);
            $table->dropColumn([
                'updated_by',
                'payment_method',
                'paid_amount',
                'change_amount',
                'status',
                'notes',
            ]);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('sub_total', 8, 2)->change();
            $table->decimal('tax', 8, 2)->default(0)->change();
            $table->decimal('discount', 8, 2)->default(0)->change();
            $table->decimal('total', 8, 2)->change();
        });
    }
};
