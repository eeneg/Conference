<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\PollSetActiveEvent;
use App\Events\PollVoteSubmittedEvent;
use App\Events\PollConcludedEvent;
use App\Models\Poll;
use App\Models\Conference;
use App\Models\PollVote;
use App\Models\UserRole;
use App\Models\Role;

class PollController extends Controller
{
    public function index(Request $request){
        return Poll::where('conference_id', $request->id)->get();
    }

    public function store(Request $request){
        $request->validate([
            'title' => 'required',
            'type' => 'required',
            'details' => 'required',
        ]);

        Conference::find($request->conf_id)->poll()->create([
            'title' => $request->title,
            'type' => $request->type,
            'details' => $request->details
        ]);
    }

    public function update(Request $request, String $poll_id){
        $request->validate([
            'title' => 'required',
            'type' => 'required',
            'details' => 'required',
        ]);

        Poll::find($poll_id)->update([
            'title' => $request->title,
            'type' => $request->type,
            'details' => $request->details
        ]);
    }

    public function getUserPoll($poll_id, $user_id){
        return PollVote::where('user_id', $user_id)->where('poll_id', $poll_id)->select('vote', 'note')->first();
    }

    public function getPoll($id){
        return Poll::find($id)->only('id', 'title', 'details', 'type');
    }

    public function submitPollResponse(Request $request){
        $request->validate([
            'poll_id' => 'required',
            'user_id' => 'required',
            'vote'  => 'required'
        ],[
            'vote.required' => 'Field Required'
        ]);

        PollVote::updateOrCreate([
            'poll_id'   => $request->poll_id,
            'user_id'   => $request->user_id,
        ],[
            'poll_id'   => $request->poll_id,
            'user_id'   => $request->user_id,
            'vote'      => $request->vote,
            'note'      => $request->note
        ]);

        $count = $this->countPollVotes($request->poll_id);

        broadcast(new PollVoteSubmittedEvent($count));

    }

    public function endPoll(Request $request){

        $count = $this->countPollVotes($request->poll_id);
        $res = '';

        if($count['tr'] > $count['fa']){
            $res = 'Approved';
        }else if($count['tr'] < $count['fa']){
            $res = 'Dissaproved';
        }else if($count['tr'] == $count['fa']){
            $res = 'Impasse';
        }

        if($count['tr'] == 0 && $count['fa'] == 0){
            abort(403, 'Cant conclude empty poll');
        }

        $poll = Poll::find($request->poll_id)->update([
            'agree_count' => $count['tr'],
            'disagree_count' => $count['fa'],
            'result' => $res,
            'concluded' => true
        ]);

        broadcast(new PollConcludedEvent(Poll::find($request->poll_id)));
    }

    public function countPollVotes($poll_id){

        $tr = PollVote::where('poll_id', $poll_id)->where('vote', true)->count();
        $fa = PollVote::where('poll_id', $poll_id)->where('vote', false)->count();

        $u = UserRole::where('role_id', Role::where('title', 'board member')->first()->id)->count();

        return ['tr' => $tr, 'fa' => $fa, 'u' => $u];
    }

    public function setPollActive(Request $request){
        $request->validate([
            'id' => 'required|exists:polls,id'
        ]);

        $poll = Poll::find($request->id)->update(['active' => $request->value]);

        broadcast(new PollSetActiveEvent($request->id, $request->initiatorID, $request->value));
    }

    public function getIndividualVotes($poll_id){
        $poll_yes = PollVote::where('poll_id', $poll_id)
            ->where('vote', true)
            ->leftJoin('users', 'users.id', '=', 'poll_votes.user_id')
            ->select('users.name')
            ->get();

        $poll_no = PollVote::where('poll_id', $poll_id)
            ->where('vote', false)
            ->leftJoin('users', 'users.id', '=', 'poll_votes.user_id')
            ->select('users.name', 'poll_votes.note')
            ->get();

        return [$poll_yes, $poll_no];
    }

    public function destroy(Request $request){

        $request->validate([
            'password' => ['required', 'current-password'],
        ]);

        $p = PollVote::where('poll_id', $request->id)->delete();
        $d = Poll::find($request->id)->delete();
    }
}
