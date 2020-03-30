<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryFormRequest;
use App\Models\Category;
use Illuminate\Http\Response;

class CategoryController extends Controller{

    private $category;

    /**
     * CategoryController constructor.
     * @param Category $category
     */
    public function __construct(Category $category){
        $this->category = $category;
    }


    /**
     * Display a listing of the category.
     *
     * @return Response
     */
    public function index(){
        $categories = $this->category->getCategories(null, true);

        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     *
     * @return Response
     */
    public function create(){
        $data['categories'] = $this->category->getCategories();

        return view('categories.create', $data);
    }

    /**
     * Store a newly created category in storage.
     *
     * @param CategoryFormRequest $request
     * @return Response
     */
    public function store(CategoryFormRequest $request){
        $fields = $request->validated();
        $data = $this->category->storeCategory($fields);

        if ($data['status']){
            return redirect()->route('categories.index')->with($data);
        }

        return redirect()->back()->withInput();
    }

    /**
     * Display the specified resource.
     *
     * @param string $slug
     * @return Response
     */
    public function show($slug) {
        $data = $this->category->getCategoryBySlug($slug, true);
        return view('categories.show', $data);
    }

    /**
     * Show the form for editing the specified category.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id){
        $data = $this->category->getCategoryById($id, true);

        if ($data['status']){
            $data['categories'] = $this->category->getCategories($id, true);
            return view('categories.edit', $data);
        }

        return redirect()->back();
    }

    /**
     * Update the specified category in storage.
     *
     * @param CategoryFormRequest $request
     * @param int $id
     * @return Response
     */
    public function update(CategoryFormRequest $request, $id){
        $fields = $request->validated();
        $data = $this->category->updateCategory($fields, $id);

        if ($data['status']){
            return redirect()->route('categories.index')->with($data);
        }
        return redirect()->back()->withInput();
    }

    /**
     * Remove the specified category from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id){
        $data = $this->category->destroyCategory($id);

        return json_encode($data);
    }

    /**
     * soft delete of restore specified category
     *
     * @param $id
     * @return false|string
     */
    public function deleteOrRestore($id){
        $data = $this->category->deleteOrRestore($id);

        return json_encode($data);
    }
}
