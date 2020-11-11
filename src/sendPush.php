<?php

function sendPushTip($token,$reservation){
	$url = "https://fcm.googleapis.com/fcm/send";

	$serverKey = 'AAAAEAC26og:APA91bGgn-Hb9rv0p2nXcjXQIAim5-g38wwQN9mMZV1uwjQSLX0pAi-NGicj-YlE3nTOqZi4NYD-UwtctQNKsFKBSWDzlefmpDgvlK2BZi-wt5FODqrFA4VqrOJxPoqrb8fRpc5Dtn7F';

	$title = "Your service is done";
	$body = "Do you want to tip?";
	$data=array('opcion'=>'1','reservation'=>$reservation);
	$notification = array('title' =>$title , 'text' => $body, 'sound' => 'default', 'badge' => '1','color' => "#4aae4f");
	$arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high','show_in_foreground'=>true,'data'=>$data);
	$json = json_encode($arrayToSend);
	$headers = array();
	$headers[] = 'Content-Type: application/json';
	$headers[] = 'Authorization: key='. $serverKey;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
	//Send the request
	$response = curl_exec($ch);
	//Close request
	if ($response === FALSE) {
		die('FCM Send Error: ' . curl_error($ch));
	}
	curl_close($ch);
}

function sendPushTip2($token,$reservation){
	$url = "https://fcm.googleapis.com/fcm/send";

	//$serverKey = 'AAAAEAC26og:APA91bGgn-Hb9rv0p2nXcjXQIAim5-g38wwQN9mMZV1uwjQSLX0pAi-NGicj-YlE3nTOqZi4NYD-UwtctQNKsFKBSWDzlefmpDgvlK2BZi-wt5FODqrFA4VqrOJxPoqrb8fRpc5Dtn7F';
	$serverKey='AAAAGTeXHIc:APA91bGanMN9sB1aiqibKzZ7Q7k880sJfPLEXnEjlFCYxBJLChOWxBtvHbvqLIqIMtlEnLpEkdQsHoIO6xNcjIA9HV6yemaUcZVOQbfEwfIitPoMCPqZVfrFg7GlD3lkSB-RAPg_pZFD';
	$title = "Your service is done";
	$body = "Do you want to tip?";
	$data=array('opcion'=>'1','reservation'=>$reservation);
	$notification = array('title' =>$title , 'text' => $body, 'sound' => 'default', 'badge' => '1','color' => "#4aae4f");
	$arrayToSend = array('to' => $token, 'notification' => $notification,'priority'=>'high','show_in_foreground'=>true,'data'=>$data);
	$json = json_encode($arrayToSend);
	$headers = array();
	$headers[] = 'Content-Type: application/json';
	$headers[] = 'Authorization: key='. $serverKey;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);

	curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
	//Send the request
	$response = curl_exec($ch);
	//Close request
	if ($response === FALSE) {
		die('FCM Send Error: ' . curl_error($ch));
	}
	curl_close($ch);
}

function sendPushMsg($registrationId,$title,$body){
	$url = "https://fcm.googleapis.com/fcm/send";

	$serverKey = 'AAAAEAC26og:APA91bGgn-Hb9rv0p2nXcjXQIAim5-g38wwQN9mMZV1uwjQSLX0pAi-NGicj-YlE3nTOqZi4NYD-UwtctQNKsFKBSWDzlefmpDgvlK2BZi-wt5FODqrFA4VqrOJxPoqrb8fRpc5Dtn7F';
	
	$notification = array('title' =>$title , 'text' => $body, 'sound' => 'default', 'badge' => '1','color' => "#4aae4f");

	$arrayToSend = array('registration_ids' => $registrationId, 'notification' => $notification,'priority'=>'high');

	
	$json = json_encode($arrayToSend);
	$headers = array();
	$headers[] = 'Content-Type: application/json';
	$headers[] = 'Authorization: key='. $serverKey;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);

	curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
	//Send the request
	$response = curl_exec($ch);
	//Close request
	if ($response === FALSE) {
		die('FCM Send Error: ' . curl_error($ch));
	}
	curl_close($ch);
}

function sendPushAll(){
// API access key from Google FCM App Console
	define( 'API_ACCESS_KEY', 'AIzaSyDRmpAPYK_A-eWDrbzVIo-ztU6oh4EE9NA' );

// generated via the cordova phonegap-plugin-push using "senderID" (found in FCM App Console)
// this was generated from my phone and outputted via a console.log() in the function that calls the plugin
// my phone, using my FCM senderID, to generate the following registrationId 
	$singleID = 'fO9mKAcGT-g:APA91bGoGyPeH95ldiLr0ZMpH7p07GYMe-7pzeG4IVYMu22CNZrLzFj-5f50YLPCSqGA1nXfoiucarhqp_66KFoSNtNP2ccCXRRoTt7IItcdzoXxTghf1btkLac4oz-7FbEPUHu6_j-m'; 
	$registrationIDs = array(
    	 'eEvFbrtfRMA:APA91bFoT2XFPeM5bLQdsa8-HpVbOIllzgITD8gL9wohZBg9U.............mNYTUewd8pjBtoywd', 
    	 'eEvFbrtfRMA:APA91bFoT2XFPeM5bLQdsa8-HpVbOIllzgITD8gL9wohZBg9U.............mNYTUewd8pjBtoywd',
    	 'eEvFbrtfRMA:APA91bFoT2XFPeM5bLQdsa8-HpVbOIllzgITD8gL9wohZBg9U.............mNYTUewd8pjBtoywd'
	) ;

// prep the bundle
// to see all the options for FCM to/notification payload: 
// https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support 

// 'vibrate' available in GCM, but not in FCM
	$fcmMsg = array(
		'body' => 'here is a message. message',
		'title' => 'This is title #1',
		'sound' => "default",
        'color' => "#203E78" 
	);
// I haven't figured 'color' out yet.  
// On one phone 'color' was the background color behind the actual app icon.  (ie Samsung Galaxy S5)
// On another phone, it was the color of the app icon. (ie: LG K20 Plush)

// 'to' => $singleID ;  // expecting a single ID
// 'registration_ids' => $registrationIDs ;  // expects an array of ids
// 'priority' => 'high' ; // options are normal and high, if not set, defaults to high.
	$data=array(
		'opcion'=>'1'
	);
	$fcmFields = array(
		'to' => $singleID,
        'priority' => 'high',
		'notification' => $fcmMsg,
		'data'=>$data,
	);

	$headers = array(
		'Authorization: key=' . API_ACCESS_KEY,
		'Content-Type: application/json'
	);
 
	$ch = curl_init();
	curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
	curl_setopt( $ch,CURLOPT_POST, true );
	curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fcmFields ) );
	$result = curl_exec($ch );
	curl_close( $ch );
	echo $result . "\n\n";
}
?>