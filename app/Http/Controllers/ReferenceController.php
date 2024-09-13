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
                ->paginate(15);
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
                'file_name'     => $request->file->getClientOriginalName(),
                'hash_name'     => $request->file->hashName()
            ]);

            Storage::putFileAs('reference_files/', $request->file, $request->file->hashName());
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
            Storage::delete('reference_files/'.$ref->hash_name);
            $ref->update([
                'title'         => $request->title,
                'category_id'   => $request->category_id,
                'date'          => $request->date,
                'details'       => $request->details,
                'file_name'     => $request->file->getClientOriginalName(),
                'hash_name'     => $request->file->hashName()
            ]);
            Storage::putFileAs('reference_files/', $request->file, $request->file->hashName());
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

        Storage::delete('reference_files/'.$ref->hash_name);

        $ref->delete();
    }
}
