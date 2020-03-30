<?php

namespace App\Models;

use App\Utils\AppUtils;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Category extends Model {

    use SoftDeletes;

    protected $fillable = [
        'created_by', 'updated_by', 'parent_id', 'category_name', 'category_code', 'category_image', 'category_slug'
    ];

    // ===================== ORM Definition START ===================== //

    /**
     * @return BelongsTo
     */
    public function parent() {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function children(){
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function products() {
        return $this->hasMany(Product::class);
    }

    /**
     * @return BelongsTo
     */
    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo
     */
    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ===================== ORM Definition END ===================== //

    /**
     * format object
     * @return array
     */
    public function format(){
        return [
            'id' => $this->id,
            'category_image' => asset('storage/'.$this->category_image),
            'category_name' => $this->category_name,
            'category_code' => $this->category_code,
            'category_slug' => $this->category_slug,
            'created_by' => $this->createdBy ? $this->createdBy->name : '-',
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : '-',
            'parent_category' => $this->parent ? $this->parent->category_name : '-',
            'parent_id' => $this->parent ? $this->parent->id : '',
            'created_at' => $this->created_at,
            'updated_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
            'products' => $this->products->map(function ($product){
                return [
                    'id' => $product->id,
                    'created_by' => $product->created_by,
                    'product_name' => $product->product_name,
                    'product_code' => $product->product_code,
                    'product_slug' => $product->product_slug,
                    'quantity' => $product->quantity,
                    'sale_price' => $product->sale_price,
                    'created_at' => $product->created_at,

                ];
            })
        ];
    }

    /**
     * Get all categories with parent and trashed
     *
     * @param null $exceptId
     * @param bool $withTrashed
     * @return Collection
     */
    public function getCategories($exceptId = null, $withTrashed = false) {
        $categories = $this->newQuery()
            ->when($withTrashed, function ($query){
                $query->withTrashed();
            })
            ->when($exceptId, function ($query) use ($exceptId){
                $query->where('id', '!=', $exceptId)->where(function ($q) use ($exceptId){
                    $q->where('parent_id', '!=', $exceptId)->orWhere('parent_id', null);
                });
            })
            ->when(is_null($exceptId), function ($query){
                $query->with([
                    'parent' => function ($parentQ) {
                        $parentQ->withTrashed();
                    }
                ]);
            })
            ->get()->map(function ($category){
                return $category->format();
            });
        return $categories;
    }

    /**
     * get specified category by id with parent and trashed
     *
     * @param $id
     * @param bool $withTrashed
     * @return array
     */
    public function getCategoryById($id, $withTrashed = false) {
        $category = $this->where('id', $id)
        ->when($withTrashed, function ($query) use ($id){
            $query->withTrashed()->where('id', $id)->with([
                'parent' => function ($query){
                    $query->withTrashed();
                }
            ]);
        })->first()->format();

        $data = [];
        if ($category){
            $data['status'] = true;
            $data['category'] = $category;
        }else{
            $data['status'] = false;
            $data['category'] = [];
        }
        return $data;
    }

    /**
     * @param string $slug
     * @param bool $withTrashed
     * @return array
     */
    public function getCategoryBySlug(string $slug, bool $withTrashed = false) {
        $category = $this->where('category_slug', $slug)
            ->when($withTrashed, function ($query){
                $query->withTrashed();
            })
            ->with([
                'parent' => function ($query){
                    $query->withTrashed();
                }
            ])
            ->first()->format();
        $data = [];
        if ($category){
            $data['status'] = true;
            $data['category'] = $category;
        }else{
            $data['status'] = false;
            $data['category'] = [];
        }
        return $data;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function storeCategory(array $fields) {
        $categoryImage = $fields['category_image'];
        $categoryImage = AppUtils::base64ToImage($categoryImage);
        $imageName = time() . 'category.png';
        $fields['created_by'] = auth()->id();
        $fields['category_image'] = 'category/' . $imageName;
        $category = $this::create($fields);

        $data = [];
        if ($category) {
            $data['status'] = true;
            $data['message'] = "New category was added";
            Storage::disk()->put('category/' . $imageName, $categoryImage);
        } else {
            $data['status'] = false;
            $data['message'] = "There is problem adding new category";
        }

        return $data;
    }

    /**
     * @param array $fields
     * @param int $id
     * @return mixed
     */
    public function updateCategory(array $fields, int $id) {
        $category = $this::withTrashed()->findOrFail($id);
        if ($category){
            if ($fields['category_image']){
                $oldImage = $category->category_image;
                $categoryImage = $fields['category_image'];
                $categoryImage = AppUtils::base64ToImage($categoryImage);
                $imageName = time() . 'category.png';
                $fields['category_image'] = 'category/' . $imageName;
                Storage::disk()->put('category/' . $imageName, $categoryImage);
                unlink(storage_path('app/public/' . $oldImage));
            }else{
                unset($fields['category_image']);
            }

            $fields['updated_by'] = auth()->id();
            $category->update($fields);
            $data['status'] = true;
            $data['message'] = "Category was updated";
        }else{
            $data['status'] = false;
            $data['message'] = "There is problem updating category";
        }

        return $data;
    }

    /**
     * @param int $id
     * @return array
     */
    public function destroyCategory(int $id) {
        $category = $this::withTrashed()->where('id', $id)->firstOrFail();
        $data = [];
        if ($category){
            $oldImage = $category->category_image;
            $category->forceDelete();
            unlink(storage_path('app/public/' . $oldImage));
            $data['status'] = true;
            $data['message'] = "Category was deleted";
        }else{
            $data['status'] = false;
            $data['message'] = "There is problem deleting category";
        }

        return $data;
    }

    /**
     * @param $id
     * @return array
     */
    public function deleteOrRestore($id) {
        $data = [];
        $category = $this::withTrashed()->findOrFail($id);
        if ($category){
            if ($category->trashed()){
                $category->restore();
            }else{
                try {
                    $category->delete();
                } catch (\Exception $e) {
                }
            }
            $data['status'] = true;
            $data['message'] = "Category status was updated";
        }

        return $data;
    }
}
