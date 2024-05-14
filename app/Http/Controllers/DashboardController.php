<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\File;
use App\Models\User;
use App\Models\Conference;
use App\Models\Reference;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request){
        return Inertia::render('Dashboard',[
            'user_count' => User::all()->count(),
            'file_count' => File::all()->count(),
            'reference_count' => Reference::all()->count(),
            'conference_count' => Conference::where('status', 'finished')->get()->count(),
            'sesssions_today' => Conference::whereDate('date', Carbon::now()->format('Y-m-d'))->where('status', 'pending')->get(),
            'files_review' => File::where('for_review', true)->get(),
            'monthly_sessions' =>  Conference::where('status', 'completed')->whereYear('date', $request->year ?? Carbon::now()->format('Y'))->get()->groupBy(function($item, int $key) {
                return Carbon::parse($item->date)->format('M');
            })->map(function($e){
                return count($e);
            }),
            'year' => $request->year
        ]);
    }
}
