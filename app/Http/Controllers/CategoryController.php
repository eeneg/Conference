<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Category;
use App\Models\File;
use App\Models\FileCategory;
use App\Models\Reference;

class CategoryController extends Controller
{

    public function index(){
        return Inertia::render('Category/Index',[
            'category' => Category::paginate(5),
        ]);
    }

    public function store(Request $request){

        $request->validate([
            'title' => 'required|unique:categories,title',
            'type' => 'required'
        ]);

        Category::create($request->all());

    }

    public function update(Request $request){

        $request->validate([
            'title' => 'required|unique:categories,title,'.$request->id,
            'type' => 'required'
        ]);

        $category = Category::find($request->id);

        $category->update($request->all());

    }

    public function checkCategoryRelation($id){

        $files = FileCategory::where('category_id', $id)->count() == 0;

        $ref = Reference::where('category_id', $id)->count() == 0;

        return $files && $ref;

    }

    public function destroy(Request $request, $id){

        $request->validate([
            'password' => ['required', 'current-password'],
        ]);

        $category = Category::find($id);

        $category->delete();

    }
}
