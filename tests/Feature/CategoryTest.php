<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoryTest extends TestCase
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

    public function test_category_pages_render(): void
    {
        $user = $this->createAdmin();
        $category = Category::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)->get(route('categories.index'))->assertStatus(200)->assertSee($category->category_name);
        $this->actingAs($user)->get(route('categories.create'))->assertStatus(200);
        $this->actingAs($user)->get(route('categories.show', $category->category_slug))->assertStatus(200)->assertSee($category->category_name);
        $this->actingAs($user)->get(route('categories.edit', $category->id))->assertStatus(200)->assertSee($category->category_name);
    }

    public function test_category_store_persists_record_and_image(): void
    {
        Storage::fake('public');
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'category_name' => 'Electronics',
            'category_code' => 'ELEC-001',
            'category_slug' => 'electronics',
            'category_image' => self::BASE64_PNG,
            'parent_id' => null,
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'category_name' => 'Electronics',
            'category_code' => 'ELEC-001',
            'category_slug' => 'electronics',
            'created_by' => $user->id,
        ]);

        $category = Category::where('category_slug', 'electronics')->firstOrFail();
        Storage::disk('public')->assertExists($category->category_image);
    }

    public function test_category_update_modifies_fields_and_replaces_image(): void
    {
        Storage::fake('public');
        $user = $this->createAdmin();

        $this->actingAs($user)->post(route('categories.store'), [
            'category_name' => 'Accessories',
            'category_code' => 'ACC-001',
            'category_slug' => 'accessories',
            'category_image' => self::BASE64_PNG,
        ]);

        $category = Category::where('category_slug', 'accessories')->firstOrFail();
        $oldImage = $category->category_image;

        $response = $this->actingAs($user)->put(route('categories.update', $category->id), [
            'category_name' => 'PC Accessories',
            'category_code' => 'ACC-002',
            'category_slug' => 'pc-accessories',
            'category_image' => self::BASE64_PNG,
            'parent_id' => null,
        ]);

        $response->assertRedirect(route('categories.index'));
        $category->refresh();

        $this->assertSame('PC Accessories', $category->category_name);
        $this->assertSame('ACC-002', $category->category_code);
        $this->assertSame($user->id, $category->updated_by);
        $this->assertNotSame($oldImage, $category->category_image);
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($category->category_image);
    }

    public function test_category_soft_delete_and_restore(): void
    {
        $user = $this->createAdmin();
        $category = Category::factory()->create(['created_by' => $user->id]);

        $softDeleteResponse = $this->actingAs($user)->patch(route('categories.delete', $category->id));
        $softDeleteResponse->assertStatus(200);
        $this->assertTrue(json_decode($softDeleteResponse->getContent(), true)['status']);
        $this->assertSoftDeleted('categories', ['id' => $category->id]);

        $restoreResponse = $this->actingAs($user)->patch(route('categories.delete', $category->id));
        $restoreResponse->assertStatus(200);
        $this->assertTrue(json_decode($restoreResponse->getContent(), true)['status']);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
    }

    public function test_category_force_destroy_deletes_record_and_image(): void
    {
        Storage::fake('public');
        $user = $this->createAdmin();

        $this->actingAs($user)->post(route('categories.store'), [
            'category_name' => 'Disposable',
            'category_code' => 'DISP-001',
            'category_slug' => 'disposable',
            'category_image' => self::BASE64_PNG,
        ]);

        $category = Category::where('category_slug', 'disposable')->firstOrFail();
        $imagePath = $category->category_image;
        Storage::disk('public')->assertExists($imagePath);

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category->id));
        $response->assertStatus(200);
        $this->assertTrue(json_decode($response->getContent(), true)['status']);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        Storage::disk('public')->assertMissing($imagePath);
    }
}
