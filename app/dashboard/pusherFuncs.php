<?php
session_start();
if (!isset($_SESSION['username'])){
	exit();
}
$app_id = '272574'; // Add your app id here
$key = '2b54502c7478473bc9f7';  // Add your key here
$secret = '0d6e6540f7cde54c42c6';  // Add your secret here

include_once 'Pusher.php';
// Send message to trigger push
if(isset($_POST['action']) && $_POST['action'] == "requestNoteBadge"){
	include ("notificationlist.php");
	$message = $notificationsrow;
	$message = "{$_SESSION['username']}|{$message}";
	$pusher = new Pusher($key, $secret, $app_id);
 
	$pusher->trigger('notifications', 'notifications', array('message' => $message));
	//echo $message;
	exit();
}
else if (isset($_POST["action"]) && $_POST["action"] == "requestNoteList "){
	
}
// Authenticate Channel Users
if(isset($_POST['socket_id']) && isset($_POST['channel_name'])){
	$socket_id = $_POST['socket_id'];
	$channel_name = $_POST['channel_name'];
 
	$pusher = new Pusher($key, $secret, $app_id);
 
	$presence_data = array('notifications' => $_SESSION['username']);
 
	echo $pusher->presence_auth($channel_name, $socket_id, $presence_data);
	exit();
}
?>