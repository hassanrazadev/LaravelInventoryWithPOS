<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_purchase_create_and_product_form_partial_render(): void
    {
        $user = $this->createAdmin();
        $category = Category::factory()->create(['created_by' => $user->id]);
        $product = Product::factory()->create(['category_id' => $category->id, 'created_by' => $user->id]);
        $supplier = Supplier::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)->get(route('purchases.index'))->assertStatus(200);

        $this->actingAs($user)->get(route('purchases.create'))
            ->assertStatus(200)
            ->assertSee($supplier->name)
            ->assertSee($product->product_name);

        $this->actingAs($user)->get(route('purchases.productForm', 2))
            ->assertStatus(200)
            ->assertSee('product_id[2]');
    }

    public function test_purchase_store_update_show_and_destroy_manage_stock(): void
    {
        $user = $this->createAdmin();
        $category = Category::factory()->create(['created_by' => $user->id]);
        $productA = Product::factory()->create([
            'category_id' => $category->id,
            'created_by' => $user->id,
            'quantity' => 5,
        ]);
        $productB = Product::factory()->create([
            'category_id' => $category->id,
            'created_by' => $user->id,
            'quantity' => 2,
        ]);
        $supplierA = Supplier::factory()->create(['created_by' => $user->id]);
        $supplierB = Supplier::factory()->create(['created_by' => $user->id]);

        // Store purchase
        $storeResponse = $this->actingAs($user)->post(route('purchases.store'), [
            'supplier_id' => $supplierA->id,
            'total' => 250,
            'product_id' => [$productA->id, $productB->id],
            'quantity' => [3, 4],
            'unit_price' => [50, 25],
            'sub_total' => [150, 100],
        ]);

        $storeResponse->assertRedirect(route('purchases.index'));

        $purchase = Purchase::firstOrFail();
        $this->assertSame($supplierA->id, (int) $purchase->supplier_id);
        $this->assertEquals(250, (float) $purchase->total);
        $this->assertSame(8, (int) $productA->fresh()->quantity);
        $this->assertSame(6, (int) $productB->fresh()->quantity);

        $this->assertDatabaseHas('purchase_details', [
            'purchase_id' => $purchase->id,
            'product_id' => $productA->id,
            'quantity' => 3,
            'unit_price' => 50,
            'sub_total' => 150,
        ]);

        // Show and Edit render
        $this->actingAs($user)->get(route('purchases.show', $purchase->id))
            ->assertStatus(200)
            ->assertSee($supplierA->name)
            ->assertSee($productA->product_name);

        $this->actingAs($user)->get(route('purchases.edit', $purchase->id))
            ->assertStatus(200)
            ->assertSee($supplierA->name);

        // Update purchase (reverse 3 of A and 4 of B, add 10 of A)
        $updateResponse = $this->actingAs($user)->put(route('purchases.update', $purchase->id), [
            'supplier_id' => $supplierB->id,
            'total' => 500,
            'product_id' => [$productA->id],
            'quantity' => [10],
            'unit_price' => [50],
            'sub_total' => [500],
        ]);

        $updateResponse->assertRedirect(route('purchases.index'));
        $purchase->refresh();

        $this->assertSame($supplierB->id, (int) $purchase->supplier_id);
        $this->assertEquals(500, (float) $purchase->total);
        $this->assertSame(15, (int) $productA->fresh()->quantity);
        $this->assertSame(2, (int) $productB->fresh()->quantity);

        // Destroy purchase reverses stock
        $destroyResponse = $this->actingAs($user)->delete(route('purchases.destroy', $purchase->id));
        $destroyResponse->assertStatus(200);
        $this->assertTrue(json_decode($destroyResponse->getContent(), true)['status']);
        $this->assertDatabaseMissing('purchases', ['id' => $purchase->id]);
        $this->assertSame(5, (int) $productA->fresh()->quantity);
    }

    public function test_purchase_validation_rejects_missing_products_gracefully(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)
            ->from(route('purchases.create'))
            ->post(route('purchases.store'), [
                'total' => 100,
            ]);

        $response->assertRedirect(route('purchases.create'));
        $response->assertSessionHasErrors(['product_id', 'quantity', 'unit_price', 'sub_total']);
    }
}
