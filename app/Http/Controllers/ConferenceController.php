<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Conference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Throwable;

class ConferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Inertia::render('Conferences/Index', [
            'search' => $request->search,
            'upcoming' => Conference::pending()->paginate(5),
            'finished' => Conference::search($request->search)
                ->query(fn ($q) => $q->completed())
                ->paginate(5),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Conferences/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'date' => 'required|date',
            'agenda' => 'required',
            'status' => 'required'
        ]);


        try{

            $conf = Conference::create([
                'title' => $request->title,
                'agenda' => $request->agenda,
                'date' => $request->date,
                'status' => $request->status
            ]);

        }catch(Throwable $e){

            report($e);

            return $e->getMessage();

        }

        $attachments = Conference::fileHandle($request->attachments, $request->title, $conf->id);

        $attachments = Attachment::create([
            'conference_id' => $conf->id,
            'files' => $attachments
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Conference $conference)
    {
        return Inertia::render('Conferences/Show', [
            'conference' => $conference
        ]);
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
