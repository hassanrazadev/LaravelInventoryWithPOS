<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table): void {
            $table->string('category', 50)->nullable()->after('expense_name');
            $table->date('expense_date')->nullable()->after('expense_amount');
            $table->text('notes')->nullable()->after('expense_date');
        });

        Schema::table('expenses', function (Blueprint $table): void {
            $table->decimal('expense_amount', 12, 2)->change();
        });

        Schema::table('returns', function (Blueprint $table): void {
            $table->unsignedBigInteger('created_by')->nullable()->after('order_id');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->text('reason')->nullable()->after('return_price');
            $table->boolean('restock')->default(true)->after('reason');
            $table->softDeletes();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->nullOnDelete();
            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->nullOnDelete();
        });

        Schema::table('returns', function (Blueprint $table): void {
            $table->decimal('return_price', 12, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table): void {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'created_by',
                'updated_by',
                'reason',
                'restock',
            ]);
        });

        Schema::table('returns', function (Blueprint $table): void {
            $table->decimal('return_price', 8, 2)->change();
        });

        Schema::table('expenses', function (Blueprint $table): void {
            $table->dropColumn([
                'category',
                'expense_date',
                'notes',
            ]);
        });

        Schema::table('expenses', function (Blueprint $table): void {
            $table->decimal('expense_amount', 8, 2)->change();
        });
    }
};
