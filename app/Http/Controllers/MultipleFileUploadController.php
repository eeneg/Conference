<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Storage as StorageModel;
use App\Services\MultipleFileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class MultipleFileUploadController extends Controller
{

    public function __construct(
        private MultipleFileUploadService $multipleFileUploadService,
    ){}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

        $files = Arr::map($request->file('files'), fn($e) => ['file_name' => $e->getClientOriginalName(), 'hash_name' => $e->hashName()]);

        $create = StorageModel::find($request->input('storage_id'))->files()->createMany($files);

        foreach($request->file('files') as $file){
            Storage::disk('local')->putFileAs('public/Temp_File_Storage/', $file, $file->hashName());
        }

        $this->multipleFileUploadService->handle($create, $request->input('category_id'));
    }

    public function checkDuplicateFileName(Request $request){

        $request->validate([
            'files_names' => 'required|array',
            'files_names.*' => 'required|string',
            'storage_id' => 'required',
        ]);

        $files = File::whereIn('file_name', $request->input('files_names'))->get('file_name');

        return $files;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
