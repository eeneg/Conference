<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Events\MessageSentEvent;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Models\UserChat;

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

        $chats = Chat::with(['latestMessage' => fn ($q) => $q->with(['user', 'recipient'])])
            ->whereHas('userChats', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->where('private', true)
            ->paginate(10);

        // dd($chats->toArray());

        return $chats;

    }

    public function show($id)
    {

        $message = Chat::with(['messages' => function($query){
            $query->orderBy('created_at', 'desc');
            }])
            ->whereHas('userChats', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->whereHas('userChats', function ($query) use ($id) {
                $query->where('user_id', $id);
            })
            ->paginate(10);

        return $message;

    }

    public function store(Request $request){

        $chat = Chat::with('messages')
            ->whereHas('userChats', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->whereHas('userChats', function ($query) use ($request) {
                $query->where('user_id', $request->recipient_id);
            })
            ->first();

        if(!$chat){
            $chat = Chat::create([
                'private' => true
            ]);

            $chat->userChats()->create([
                'user_id' => auth()->id()
            ]);

            $chat->userChats()->create([
                'user_id' => $request->recipient_id
            ]);
        }

        $message = $chat->messages()->create([
            'sender_id' => auth()->id(),
            'message' => $request->message,
        ]);


        broadcast(new MessageSentEvent(auth()->user()->id, $request->recipient_id, $message))->toOthers();

    }

    public function setMessageStatus(Request $request){
    }

    public function newMessageCount(Request $request){
    }
}
