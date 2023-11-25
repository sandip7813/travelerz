<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api');
        $this->user = auth('api')->user();
    }

    public function getUserNotifications(Request $request){
        $readable_type = $request->readable_type ?? null;
        $page_limit = $request->page_limit ?? null;

        $getUser = $this->user;

        if ($readable_type == 'unread') {
            $notifications = $getUser->unreadNotifications;
        } elseif ($readable_type == 'read') {
            $notifications = $getUser->readNotifications;
        } else {
            $notifications = $getUser->notifications;
        }

        if ($page_limit != null) {
            //return $notifications->take($page_limit);
            //return $notifications->orderBy('updated_at', 'DESC')->take($page_limit)->get();
            return $notifications->take($page_limit)->sortBy('updated_at', SORT_REGULAR, true);
        } else {
            //return $notifications;
            //return $notifications->orderBy('updated_at', 'DESC')->get();
            return $notifications->sortBy('updated_at', SORT_REGULAR, true);
        }
    }

    public function markNotificationsAsRead(Request $request)
    {
        $requestNotificationId = $request->notification_id ?? null;

        if( is_null($requestNotificationId) ){
            return response()->json(['success' => false, 'message' => 'Invalid request!'], 400);
        }

        $getUser = $this->user;

        $notification = $getUser->notifications()->where('id', $requestNotificationId)->first();
        $notification_id = $notification->id ?? null;

        if( is_null($notification_id) ){
            return response()->json(['success' => false, 'message' => 'No notification found!'], 400);
        }

        $notification->markAsRead();

        $notification_data = $notification->data;
        $notification_data['id'] = $requestNotificationId;

        return response()->json([
            'success' => true,
            'notification' => $notification_data
        ], 200);
    }
}
