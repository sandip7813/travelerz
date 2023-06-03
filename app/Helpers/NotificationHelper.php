<?php
namespace App\Helpers;

use App\Models\PushNotification;

class MoveHelper
{
    public function sendNotification(){
        $comment = new PushNotification();
        $comment->title = $req->input('title');
        $comment->body = $req->input('body');
        $comment->img = $req->input('img');
        $comment->save();

        $url = config('services.firebase.url');
        $dataArr = [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'id' => $req->id,
            'status' => 'done'
        ];
        $notification = [
            'title' => $req->title,
            'text' => $req->body,
            'image'=> $req->img,
            'sound' => 'default',
            'badge' => '1'
        ];
        $arrayToSend = [
            'to' => '/topics/all',
            'notification' => $notification,
            'data' => $dataArr,
            'priority' => 'high'
        ]; 
        $fields = json_encode ($arrayToSend);
        $firebaseServerKey = config('services.firebase.server_key');
        $headers = [
            'Authorization: key=' . $firebaseServerKey,
            'Content-Type: application/json'
        ];
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
        $result = curl_exec ( $ch );
        //var_dump($result);
        curl_close ( $ch );
    }
}