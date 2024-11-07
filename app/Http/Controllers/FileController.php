<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Storage;
use App\Models\Category;
use App\Models\File;
use App\Models\Attachment;
use App\Models\FileVersionControl;
use App\Services\FileContentService;
use App\Services\FileUploadedService;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage as FileStorage;
use Illuminate\Support\Facades\Redirect;

class FileController extends Controller
{

    public function __construct(
        private FileContentService $fileContentService,
        private FileUploadedService $fileUploadedService,
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Files/Index', [
            'storage' => Storage::all(),
            'category' => Category::where("type", "1")->get(),
            'duplicates' => [],
            'error_message' => ''
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf',
            'title' => 'required:|unique:files',
            'storage_id' => 'required',
            'category_id' => 'required|array',
            'category_id.*' => 'array',
            'category_id.*.id' => 'required|string|uuid|exists:categories,id',
            'date' => 'required',
            'details' => 'required',
        ],[
            'storage_id.required' => 'Storage Field in required',
            'category_id.required' => 'Category Field in required',
        ]);

        $tr = DB::Transaction(function($e) use ($request) {
            $file = File::create([
                'title'         => $request->title,
                'file_name'     => $request->file->getClientOriginalName(),
                'hash_name'     => $request->file->hashName(),
                'storage_id'    => $request->storage_id,
                'details'       => $request->details,
                'date'          => $request->date,
            ]);
            return $file;
        });

        FileStorage::disk('local')->putFileAs('public/Temp_File_Storage/', $request->file, $request->file->hashName());
        $this->attachCategory($tr->id, $request->category_id);
        $this->fileUploadedService->handle($tr->id);
        $this->fileContentService->handle($tr->id);
    }

    public function attachCategory($id, $categories){
        $file = File::find($id);

        $file->category()->attach(collect($categories)->map->id);

        $file->load('category')->searchable();
    }

    public function renameFile(Request $request, $id){

        $request->validate([
            'file_name' => 'required'
        ]);

        $file = File::find($id);

        $file->update(['file_name' => $request->file_name]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return Inertia::render('Files/Index', [
            'storage' => Storage::all(),
            'category' => Category::where("type", "1")->get(),
            'file' => File::with(['category'])->find($id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required:|unique:files,title,'.$id,
            'storage_id' => 'required',
            'category_id' => 'required|array',
            'category_id.*' => 'array',
            'category_id.*.id' => 'required|string|uuid|exists:categories,id',
            'date' => 'required',
            'details' => 'required',
        ],[
            'storage_id.required' => 'Storage Field in required',
            'category_id.required' => 'Category Field in required',
        ]);

        $file = File::find($id);

        $file->category()->syncWithPivotValues(collect($request->category_id)->map(function($e){return $e['id'];}), ['active' => true]);

        $file->update([
            'title' => $request->title,
            'storage_id' => $request->storage_id,
            'details' => $request->details,
            'date' => $request->date
        ]);
    }

    public function setFileForReview(Request $request, String $id){
        $file = File::find($id);

        $file->update(['for_review' => $request->status]);

        return redirect(route('file.index'));
    }

    public function checkFile(String $id) {
        $fv = FileVersionControl::where('control_id', FileVersionControl::where('file_id', $id)->first()->control_id)->get()->count();
        $at = Attachment::where('file_id', $id)->get()->count();

        return $fv == 1 && $at == 0;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $f = File::find($id);

        if(!$request->safe){
            $request->validate([
                'password' => ['required', 'current-password'],
            ]);
        }

        $fv = FileVersionControl::where('file_id', $id)->first();

        if(FileVersionControl::where('control_id', $fv->control_id)->get()->count() > 1 && $f->latest == true){
            $files = FileVersionControl::where('control_id', $fv->control_id)->get('file_id');
            $files = File::whereIn('id', $files)->get()->map(fn($e) => 'file_uploads/'.$e->hash_name);
            $delete = FileStorage::delete($files->toArray());
        }else{
            $fv->delete();
            FileStorage::delete('file_uploads/'.$f->hash_name);
        }

        $f->category()->detach();

        $f->pdfContent()->delete();

        $f->delete();

        return Redirect::route('file.index');
    }
}
