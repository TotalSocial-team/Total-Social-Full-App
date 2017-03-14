<?php
include_once("php_includes/check_login_status.php");
// If the page requestor is not logged in, usher them away
if($user_ok != true || $log_username == ""){
	header("location: /v2/app/dashboard/account-access");
    exit();
}
$notification_list = "";
$sql = "SELECT * FROM notifications WHERE username = '$log_username' AND did_read = '0' ORDER BY date_time DESC";
$query = mysqli_query($db_conx, $sql);
$numrows = mysqli_num_rows($query);
if($numrows < 1){
   /* $notifcation_list = '<li class="has-action-left has-action-right"> <a href="#" class="visible" data-message-id="1">
              <div class="list-action-left"> <img src="../../assets/globals/img/faces/1.jpg" class="face-radius" alt=""> </div>
              <div class="list-content"> <span class="title">Pari Subramanium</span> <span class="caption">Collaboratively administrate empowered markets via plug-and-play networks. Dynamically procrastinate B2C users after installed base benefits.</span> </div>
              <div class="list-action-right"> <span class="top">15 min</span> <i class="ion-android-done bottom"></i> </div>
              </a> </li>'; */
	$notification_list = '<li class="has-action-left has-action-right has-long-story"> 
              <div class="list-content"> <span class="caption">No new notifications or friend requests</span></div></li>';
	
	/*
	<li class="has-action-left has-action-right has-long-story"> <a href="#" class="hidden"><i class="ion-android-delete"></i></a> <a href="#" class="visible">
              <div class="list-action-left"> <i class="ion-email icon text-indigo"></i> </div>
              <div class="list-content"> <span class="caption">Collaboratively administrate empowered markets via plug-and-play networks. Dynamically procrastinate B2C users after installed base benefits.</span> </div>
              <div class="list-action-right"> <span class="top">2 hr</span> <i class="ion-record text-green bottom"></i> </div>
              </a> </li>
	*/
} else {
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$noteid = $row["id"];
		$initiator = $row["initiator"];
		$app = $row["app"];
		$note = $row["note"];
		$date_time = $row["date_time"];
		$date_time = strftime("%b %d, %Y", strtotime($date_time));
		
		$notification_list .= "<li class='has-action-left has-action-right has-long-story'><a href='<a href='/v2/app/dashboard/user/		$initiator' class='visible'>
           	  <div class='list-action-left'> <i class='ion-email icon text-indigo'></i> </div>
              <div class='list-content'> <span class='caption'>$note</span> </div>
              <div class='list-action-right'> <span class='top'>$app</span> <i class='ion-record text-green bottom'></i> </div>
              </a> </li>";
	}
}
mysqli_query($db_conx, "UPDATE users SET notescheck=now() WHERE username='$log_username' LIMIT 1");
?><?php
$friend_requests = "";
$sql = "SELECT * FROM friends WHERE user2='$log_username' AND accepted='0' ORDER BY datemade ASC";
$query = mysqli_query($db_conx, $sql);
$numorows = mysqli_num_rows($query);
if($numorows < 1){
}
else {
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		$reqID = $row["id"];
		$user1 = $row["user1"];
		$datemade = $row["datemade"];
		$datemade = strftime("%B %d", strtotime($datemade));
		$thumbquery = mysqli_query($db_conx, "SELECT avatar FROM users WHERE username='$user1' LIMIT 1");
		$thumbrow = mysqli_fetch_row($thumbquery);
		$user1avatar = $thumbrow[0];
		$user1pic = '<img src="userdata/'.$user1.'/'.$user1avatar.'" alt="'.$user1.'" class="user_pic">';
		if($user1avatar == NULL){
			$user1pic = '<img src="../user.png" alt="'.$user1.'" class="user_pic">';
		}
		
		$friend_requests .='<li id="friendreq_'.$reqID.'" class="has-action-left has-action-right has-long-story"><a href="/v2/app/dashboard/user/'.$user1.'" class="visible">
              <div class="list-action-left"> <i class="ion-email icon text-indigo"></i> </div>
              <div class="list-content"> <span class="caption">'.$user1.' requests friendship</span> <br /><button class="btn btn-success" onclick="friendReqHandler(\'accept\',\''.$reqID.'\',\''.$user1.'\',\'user_info_'.$reqID.'\')">accept</button>or <button class="btn btn-danger" onclick="friendReqHandler(\'reject\',\''.$reqID.'\',\''.$user1.'\',\'user_info_'.$reqID.'\')">reject</button></div>
              <div class="list-action-right"> <span class="top">'.$datemade.'</span> <i class="ion-record text-green bottom"></i> </div>
              </a> </li>';
	}
}
?>