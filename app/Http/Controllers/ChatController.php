<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Database\Eloquent\Builder as Builder1;
use Illuminate\Support\Facades\DB;
use App\Events\MessageSentEvent;
use Illuminate\Database\Query\JoinClause;
use App\Models\Message;
use App\Models\User;
use App\Models\Chat;

class ChatController extends Controller
{
    public function index()
    {

    }

    public function getUsersToChat(Request $request){

        $users = User::search($request->search)
            ->query(fn (Builder $query) => $query
                ->where('id', '!=', auth()->user()->id)
                ->without('roles')
            )
            ->orderBy('name')
            ->paginate(15);

        return $users;
    }

    public function userChatList(){

        // $users = User::where('users.id', '!=', auth()->user()->id)
        //     ->without('roles')
        //     ->select('users.id', 'users.name', 'messages.sender_id', 'messages.recipient_id', 'messages.message', 'messages.read', 'messages.created_at')
        //     ->leftJoin('messages', function(JoinClause $join){
        //         $join->on('users.id', '=','messages.sender_id')
        //             ->orOn('users.id', '=', 'messages.recipient_id');
        //     })
        //     ->where(function($query){
        //         $query->where('messages.sender_id', auth()->user()->id)
        //             ->orWhere('messages.recipient_id' , auth()->user()->id);
        //     })
        //     ->groupBy('users.id')
        //     ->orderByDesc('messages.created_at')
        //     ->paginate(10);

        // $chatRecords = User::where('users.id', '!=', auth()->user()->id)
        //     ->without('roles')
        //     ->leftJoin('messages', function (JoinClause $join) {
        //         $join->on('users.id', '=', 'messages.sender_id')
        //             ->orOn('users.id', '=', 'messages.recipient_id');
        //     })
        //     ->where(function (Builder $query) {
        //         $query->where('messages.sender_id', auth()->user()->id)
        //             ->orWhere('messages.recipient_id', auth()->user()->id);
        //             // ->first();
        //     })
        //     ->selectRaw('users.id, users.name, MAX(messages.created_at) as latest_message_time, messages.message, messages.read')
        //     ->groupBy('users.id', 'users.name')
        //     ->orderBy('latest_message_time')
        //     ->paginate(10);

        // $chatRecords = User::paginate(10);

        // dd($chatRecords->toArray());

        $chatRecords = User::where('users.id', '!=', auth()->user()->id)
            ->without('roles')
            ->leftJoin('messages', function (JoinClause $join) {
                $join->on('users.id', '=', 'messages.sender_id')
                    ->orOn('users.id', '=', 'messages.recipient_id');
            })
            ->where(function (Builder $query) {
                $query->where('messages.sender_id', auth()->user()->id)
                    ->orWhere('messages.recipient_id', auth()->user()->id);
            })
            ->selectRaw('users.id, users.name, MAX(messages.created_at) as latest_message_time, messages.message, messages.read')
            ->groupBy('users.id', 'users.name')
            ->orderBy('latest_message_time')
            ->paginate(10);

        return $chatRecords;

        return $users;

    }

    public function show($id)
    {
        return Message::where('sender_id', auth()->user()->id)
            ->where(function($query) use ($id){
                $query->where('recipient_id', $id);
            })
            ->orWhere(function($query) use ($id){
                $query->where('sender_id', $id)
                ->where('recipient_id', auth()->user()->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function store(Request $request){

        $message = User::find(auth()->user()->id)->messages()->create([
            'recipient_id' => $request->recipient_id,
            'message' => $request->message
        ]);

        $check_chat_record = Chat::where('participant1_id', auth()->user()->id)
            ->where('participant2_id', $request->recipient_id)
            ->orWhere(function($query) use ($request){
                $query->where('participant1_id', $request->recipient_id)
                    ->where('participant2_id', auth()->user()->id);
            })
            ->first();

        if($check_chat_record){
            $check_chat_record->update([
                'latest_message_id' => $message->id
            ]);
        }else{
            $chats = Chat::create([
                'participant1_id' => auth()->user()->id,
                'participant2_id' => $request->recipient_id,
                'latest_message_id' => $message->id
            ]);
        }

        broadcast(new MessageSentEvent(auth()->user()->id, $request->recipient_id, $message))->toOthers();

    }

    public function setMessageStatus(Request $request){
        return Message::where('sender_id', $request->id)->where('recipient_id', auth()->user()->id)->where('read', false)->update(['read' => true]);
    }

    public function newMessageCount(Request $request){
        return Message::where('recipient_id', auth()->user()->id)->where('read', false)->count();
    }
}
