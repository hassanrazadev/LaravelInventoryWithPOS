<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private const BASE64_PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    private function createAdmin(): User
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_product_create_and_index_render_even_with_soft_deleted_category(): void
    {
        $user = $this->createAdmin();
        $category = Category::factory()->create(['created_by' => $user->id]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->get(route('products.create'))
            ->assertStatus(200)
            ->assertSee($category->category_name);

        $category->delete();

        $this->actingAs($user)->get(route('products.index'))
            ->assertStatus(200)
            ->assertSee($product->product_name);
    }

    public function test_product_store_show_edit_and_update(): void
    {
        Storage::fake('public');
        $user = $this->createAdmin();
        $category = Category::factory()->create(['created_by' => $user->id]);

        $storeResponse = $this->actingAs($user)->post(route('products.store'), [
            'category_id' => $category->id,
            'product_name' => 'Wireless Mouse',
            'product_code' => 'WM-10001',
            'product_slug' => 'wireless-mouse',
            'sale_price' => 25.50,
            'product_image' => self::BASE64_PNG,
        ]);

        $storeResponse->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'product_name' => 'Wireless Mouse',
            'product_code' => 'WM-10001',
            'product_slug' => 'wireless-mouse',
            'created_by' => $user->id,
        ]);

        $product = Product::where('product_slug', 'wireless-mouse')->firstOrFail();
        $oldImage = $product->product_image;
        Storage::disk('public')->assertExists($oldImage);

        $this->actingAs($user)->get(route('products.show', $product->product_slug))
            ->assertStatus(200)
            ->assertSee('Wireless Mouse');

        $this->actingAs($user)->get(route('products.edit', $product->id))
            ->assertStatus(200)
            ->assertSee('Wireless Mouse');

        $updateResponse = $this->actingAs($user)->put(route('products.update', $product->id), [
            'category_id' => $category->id,
            'product_name' => 'Gaming Wireless Mouse',
            'product_code' => 'WM-10002',
            'product_slug' => 'gaming-wireless-mouse',
            'sale_price' => 45.00,
            'product_image' => self::BASE64_PNG,
        ]);

        $updateResponse->assertRedirect(route('products.index'));
        $product->refresh();

        $this->assertSame('Gaming Wireless Mouse', $product->product_name);
        $this->assertSame($user->id, $product->updated_by);
        $this->assertNotSame($oldImage, $product->product_image);
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($product->product_image);
    }

    public function test_product_soft_delete_restore_and_force_destroy(): void
    {
        Storage::fake('public');
        $user = $this->createAdmin();
        $category = Category::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)->post(route('products.store'), [
            'category_id' => $category->id,
            'product_name' => 'Mechanical Keyboard',
            'product_code' => 'MK-20001',
            'product_slug' => 'mechanical-keyboard',
            'sale_price' => 99.00,
            'product_image' => self::BASE64_PNG,
        ]);

        $product = Product::where('product_slug', 'mechanical-keyboard')->firstOrFail();
        $imagePath = $product->product_image;

        $softDelete = $this->actingAs($user)->patch(route('products.delete', $product->id));
        $softDelete->assertStatus(200);
        $this->assertSoftDeleted('products', ['id' => $product->id]);

        $restore = $this->actingAs($user)->patch(route('products.delete', $product->id));
        $restore->assertStatus(200);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'deleted_at' => null]);

        $destroy = $this->actingAs($user)->delete(route('products.destroy', $product->id));
        $destroy->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('public')->assertMissing($imagePath);
    }
}
