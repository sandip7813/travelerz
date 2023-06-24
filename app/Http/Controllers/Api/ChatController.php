<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;

use App\Http\Requests\GetChatRequest;
use App\Http\Requests\StoreChatRequest;

use App\Models\Chat;
use App\Models\Move;
use App\Models\ChatMessage;
use App\Models\User;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Auth;

class ChatController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api');
        $this->user = auth('api')->user();
    }

    /**
     * Gets chats
     *
     * @param GetChatRequest $request
     * @return JsonResponse
     */
    public function chatRoom(Request $request): JsonResponse
    {
        $move_uuid = $request->move_uuid ?? null;

        if( is_null($move_uuid) ){
            return response()->json(['success' => false, 'message' => 'Invalid request!'], 400);
        }

        $chats = Chat::whereHas('participants')
                    ->with('participants')
                    ->where('move_uuid', $move_uuid)
                    ->first();
        
        return response()->json([
            'success' => true,
            'room' => $chats
        ], 200);
    }

    /**
     * Stores a new chat
     *
     * @param StoreChatRequest $request
     * @return JsonResponse
     */
    public function store(StoreChatRequest $request) : JsonResponse
    {
        $data = $this->prepareStoreData($request);
        if($data['userId'] === $data['otherUserId']){
            return $this->error('You can not create a chat with your own');
        }

        $previousChat = $this->getPreviousChat($data['otherUserId']);

        if($previousChat === null){

            $chat = Chat::create($data['data']);
            $chat->participants()->createMany([
                [
                    'user_id'=>$data['userId']
                ],
                [
                    'user_id'=>$data['otherUserId']
                ]
            ]);

            $chat->refresh()->load('lastMessage.user','participants.user');
            return $this->success($chat);
        }

        return $this->success($previousChat->load('lastMessage.user','participants.user'));
    }

    /**
     * Check if user and other user has previous chat or not
     *
     * @param int $otherUserId
     * @return mixed
     */
    private function getPreviousChat(int $otherUserId) : mixed {

        $userId = $this->user->id;

        return Chat::where('is_private',1)
            ->whereHas('participants', function ($query) use ($userId){
                $query->where('user_id',$userId);
            })
            ->whereHas('participants', function ($query) use ($otherUserId){
                $query->where('user_id',$otherUserId);
            })
            ->first();
    }


    /**
     * Prepares data for store a chat
     *
     * @param StoreChatRequest $request
     * @return array
     */
    private function prepareStoreData(StoreChatRequest $request) : array
    {
        $data = $request->validated();
        $otherUserId = (int)$data['user_id'];
        unset($data['user_id']);
        $data['created_by'] = auth()->user()->id;

        return [
            'otherUserId' => $otherUserId,
            'userId' => auth()->user()->id,
            'data' => $data,
        ];
    }


    /**
     * Gets a single chat
     *
     * @param Chat $chat
     * @return JsonResponse
     */
    public function show(Chat $chat): JsonResponse
    {
        $chat->load('lastMessage.user', 'participants.user');
        return $this->success($chat);
    }


    public function showMessages(Request $request): JsonResponse
    {
        $roomId = $request->room_id ?? null;

        $chat = Chat::find($roomId);

        if( !isset($chat->id) ){
            return response()->json(['success' => false, 'message' => 'No chat room found!'], 400);
        }

        if( !$chat->participants->contains($this->user->id) ){
            return response()->json(['success' => false, 'message' => 'You are not a member of this chat room!'], 400);
        }

        $messages = ChatMessage::where('chat_id', $roomId)
                                ->with('user')
                                ->latest('created_at')
                                ->orderBy('updated_at', 'DESC')
                                ->paginate(25);
        
        return response()->json($messages, 200);
    }


}