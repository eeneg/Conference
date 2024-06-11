<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Reference;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class ReferenceController extends Controller
{

    public function index(Request $request){
        return Inertia::render('Reference/Index',[
            'refsearch' => $request->search,
            'refcategory' => $request->category_id,
            'reference' => Reference::orderBy('date')->paginate(20),
            'refrenceCategory' => Category::where('type', '2')->get(),
        ]);
    }

    public function searchReference(Request $request){
        return  Reference::search($request->search)->query(function(Builder $q) use ($request) {
                    $q->when($request->category_id, function($q) use ($request){
                            $q->where('category_id', $request->category_id);
                        });
                    })
                ->paginate(20);
    }

    public function store(Request $request){

        $request->validate(
            [
                'file' => 'required|mimes:pdf',
                'category_id' => 'required',
                'title' => 'required|unique:references,title',
                'date' => 'required',
                'details' => 'required'
            ],
            [
                'category_id.required' => 'Category Field required'
            ]
        );

        if($request->hasFile('file')){
            Category::find($request->category_id)->reference()->create([
                'title'         => $request->title,
                'date'          => $request->date,
                'details'       => $request->details,
                'file_name'     => str_replace(' ','_',$request->file->getClientOriginalName()),
                'path'          => '/Reference_Files/' . $request->category_id . '/' . str_replace(' ','_',$request->file->getClientOriginalName()),
            ]);

            Storage::putFileAs('public/Reference_Files/' . $request->category_id . '/', $request->file, str_replace(' ','_',$request->file->getClientOriginalName()));
        }

    }

    public function update(Request $request, string $id){
        $request->validate(
            [
                'title' => 'required|unique:references,title,'.$id,
                'category_id' => 'required',
                'date' => 'required',
                'details' => 'required'
            ],
            [
                'category_id.required' => 'Category Field required'
            ]
        );

        $ref = Reference::find($id);

        if($request->hasFile('file')){
            $ref->update([
                'title'         => $request->title,
                'category_id'   => $request->category_id,
                'date'          => $request->date,
                'details'       => $request->details,
                'file_name'     => str_replace(' ','_',$request->file->getClientOriginalName()),
                'path'          => '/Reference_Files/' . $request->category_id . '/' . str_replace(' ','_',$request->file->getClientOriginalName()),
            ]);
            Storage::delete('public/Reference_Files/' . $request->category_id . '/' . str_replace(' ','_',$request->file->getClientOriginalName()));
            Storage::putFileAs('public/Reference_Files/' . $request->category_id . '/', $request->file, str_replace(' ','_',$request->file->getClientOriginalName()));
        }else{
            $ref->update([
                'title'         => $request->title,
                'category_id'   => $request->category_id,
                'date'          => $request->date,
                'details'       => $request->details,
            ]);
        }
    }

    public function destroy(Request $request, String $id){
        $request->validate([
            'password' => ['required', 'current-password'],
        ]);

        $ref = Reference::find($id);

        Storage::delete('public/Reference_Files/' . $ref->category_id . '/' . $ref->file_name);

        $ref->delete();
    }
}
