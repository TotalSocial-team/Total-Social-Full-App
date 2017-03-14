<?php
include_once( "php_includes/check_login_status.php" );
// Initialize any variables that the page might echo
$u = "";
$sex = "Male";
$userlevel = "";
$profile_pic = "";
$profile_pic_btn = "";
$avatar_form = "";
$country = "";
$joindate = "";
$lastsession = "";
$user1 = "";
$vBadage = "";
$uLevel = "";
// Make sure the _GET username is set, and sanitize it
if ( isset( $_GET[ "u" ] ) ) {
	$u = preg_replace( '#[^a-z0-9]#i', '', $_GET[ 'u' ] );
} else {
	header("location: /v2/app/dashboard/account-access");
	exit();
}
// Select the member from the users table
$sql = "SELECT * FROM users WHERE username='$u' AND activated='1' LIMIT 1";
$user_query = mysqli_query( $db_conx, $sql );
// Now make sure that user exists in the table
$numrows = mysqli_num_rows( $user_query );
if ( $numrows < 1 ) {
	echo "That user does not exist or is not yet activated, press back";
	exit();
}
// Check to see if the viewer is the account owner
$isOwner = "No";
if ( $u == $log_username && $user_ok == true ) {
	$isOwner = "Yes";
	$profile_pic_btn = '<a href="#" onclick="return false;" onmousedown="toggleElement(\'avatar_form\')">Toggle Avatar Form</a>';
	$avatar_form = '<form id="avatar_form" enctype="multipart/form-data" method="post" action="/v2/app/dashboard/photo_system.php">';
	$avatar_form .= '<h4>Change your avatar</h4>';
	$avatar_form .= '<input type="file" name="avatar" required>';
	$avatar_form .= '<p><input type="submit" value="Upload"></p>';
	$avatar_form .= '</form>';
}
// Fetch the user row from the query above
while ( $row = mysqli_fetch_array( $user_query, MYSQLI_ASSOC ) ) {
	$profile_id = $row[ "id" ];
	$email = $row[ 'email' ];
	$gender = $row[ "gender" ];
	$country = $row[ "country" ];
	$userlevel = $row[ "userlevel" ];
	$avatar = $row[ "avatar" ];
	$signup = $row[ "signup" ];
	$lastlogin = $row[ "lastlogin" ];
	$verified = $row[ 'verified' ];
	$joindate = strftime( "%b %d, %Y", strtotime( $signup ) );
	$lastsession = strftime( "%b %d, %Y", strtotime( $lastlogin ) );
	if ( $gender == "f" ) {
		$sex = "Female";
	}
    else if ($gender == "other"){
        $sex = "Other";
    }
	if ( $userlevel == "a" ) {
		$uLevel = "Basic User";
	} else if ( $userlevel == "b" ) {
		$uLevel = "VIP";
	} else if ( $userlevel == "c" ) {
		$uLevel = "Admin/Compliance";
	} else if ( $userlevel == "d" ) {
		$uLevel = "Admin with Full Access";
	}
	include( "php_includes/template_PageTop_user.php" );
	if ( $userlevel == "c" || $userlevel == "d" ) {
		$loginLink .= '<li><a href="/v2/app/dashboard/admin?u=' . $u . '">Admin Control Panel</a></li>';
	}
	if ( $verified == true) {
		$vBadage = '<i class="ion-checkmark-circled" data-toggle="popover" data-container="body" data-placement="right" title="True Account" data-content="Total Social confirms that this profile page is part of a recognized and authentic brand."></i>';
	}
	//$profile_pic = '<img src="userdata/' . $u . '/' . $avatar . '" alt="' . $u . '">';
	if ( $avatar == NULL ) {
		//copy avatar
		$avatar = "user.png";
		$avatar2 = "userdata/$u/user.png";
		if ( !copy( $avatar, $avatar2 ) ) {
			echo "failed to copy!";
		}

	}
	$profile_pic = '<img src="/v2/app/dashboard/userdata/' . $u . '/' . $avatar . '" alt="' . $u . '" class="user-image">';

}

?> <?php
/*$photo_form = "";
// Check to see if the viewer is the account owner
$isOwner = "No";
if ( $u == $log_username && $user_ok == true ) {
	$isOwner = "Yes";
	$photo_form = '<form id="photo_form" enctype="multipart/form-data" method="post" action="photo_system.php">
	<div class="row example-row">
		<div class="col-md-3">Choose Gallery</div><!--.col-md-3-->
			<div class="col-md-9">
				<select class="selecter" required>
					<option value=""></option>
					<option value="Myself">Myself</option>
					<option value="Family">Family</option>
					<option value="Pets">Pets</option>
					<option value="Friends">Friends</option>
					<option value="Random">Random</option>
				</select>
			</div><!--.col-md-9-->
		</div><!--.row-->
		<div class="row example-row">
<div class="col-md-3">Choose Photo</div> <!--.col-md-3-->
<div class="col-md-9">

	<div class="fileinput fileinput-new" data-provides="fileinput">
		<div class="fileinput-preview thumbnail" data-trigger="fileinput" style="width: 200px; height: 150px;"></div>
		<div>
			<span class="btn btn-default btn-file">
			<span class="fileinput-new">Select image</span>
			<span class="fileinput-exists">Change</span>
			<input type="file"name="photo" multiple accept="image/*" required>
			</span>
			<a href="#" class="btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
		</div>
	</div>

</div> <!--.col-md-9-->
</div><!--.row-->
<p><button type="submit" class="btn btn-default">Upload Photos Now</button></p>
	</form>';

}
// Select the user galleries
$gallery_list = "";
$gallery_filter = "";
$sql = "SELECT DISTINCT gallery FROM photos WHERE user='$u'";
$query = mysqli_query( $db_conx, $sql );
if ( mysqli_num_rows( $query ) < 1 ) {
	$gallery_list = "This user has not uploaded any photos yet.";
} else {
	while ( $row = mysqli_fetch_array( $query, MYSQLI_ASSOC ) ) {
		$gallery = $row[ "gallery" ];
		$countquery = mysqli_query( $db_conx, "SELECT COUNT(id) FROM photos WHERE user='$u' AND gallery='$gallery'" );
		$countrow = mysqli_fetch_row( $countquery );
		$count = $countrow[ 0 ];
		$filequery = mysqli_query( $db_conx, "SELECT filename FROM photos WHERE user='$u' AND gallery='$gallery' ORDER BY RAND() LIMIT 7" );
		$filerow = mysqli_fetch_row( $filequery );
		$file = $filerow[ 0 ];
		$gallery_list .= '<div class="col-md-3">
				<div class="card card-image card-light-blue bg-image bg-opaque8 sample-bg-image6" onclick="showGallery(\'' . $gallery . '\',\'' . $u . '\')">

					<div class="context has-action-left has-action-right">

						<div class="tile-content">
							<span class="text-title">'. $gallery .'</span>
							<span class="text-subtitle">'.$count. ' photos</span>
						</div>


					</div>

				</div><!--.card-->
			</div><!--.col-->';

	}
}
*/
	?><?php
include_once( "php_includes/check_login_status.php" );
// Make sure the _GET "u" is set, and sanitize it
$u = "";
if ( isset( $_GET[ "u" ] ) ) {
	$u = preg_replace( '#[^a-z0-9]#i', '', $_GET[ 'u' ] );
} else {
	header( "location: user" );
	exit();
}
$photo_form = "";
// Check to see if the viewer is the account owner
$isOwner = "No";
if ( $u == $log_username && $user_ok == true ) {
	$isOwner = "Yes";
	/*$photo_form  = '<form id="photo_form" enctype="multipart/form-data" method="post" action="photo_system.php">';
	$photo_form .=  '<div class="row example-row">';
	$photo_form .=  '<div class="col-md-3">Choose Gallery</div><!--.col-md-3-->';
	$photo_form .=	'<div class="col-md-9">';
	$photo_form .=	'<select class="selecter" required>';
	$photo_form .=	'<option value=""></option>';
	$photo_form .=	'<option value="Myself">Myself</option>';
	$photo_form .=	'<option value="Family">Family</option>';
	$photo_form .=	'<option value="Pets">Pets</option>';
	$photo_form .=	'<option value="Friends">Friends</option>';
	$photo_form .=	'<option value="Random">Random</option>';
	$$photo_form .= '</select>';
	$photo_form .=   '<br /> <p><button type="submit" class="btn btn-default">Upload Photos Now</button></p>';
	$photo_form .= '</form>';*/
	$photo_form = '						<div class="row">
							<div class="col-md-12">
								<div class="panel">
									<div class="panel-heading">
										<div class="panel-title">
											<h4>Upload a photo, ' . $u . '.</h4>
										</div>
									</div>
									<!--.panel-heading-->
									<div class="panel-body">';
	$photo_form .= '<form id="photo_form" enctype="multipart/form-data" method="post" action="/v2/app/dashboard/photo_system.php">';
	$photo_form .= '<div class="row example-row">';
	$photo_form .= '<div class="col-md-3">Choose Gallery</div><!--.col-md-3-->';
	$photo_form .= '<div class="col-md-9">';
	$photo_form .= '<select class="selecter" style="width: 100px;" name="gallery" required>';
	$photo_form .= '<option value=""></option>';
	$photo_form .= '<option value="Myself">Myself</option>';
	$photo_form .= '<option value="Family">Family</option>';
	$photo_form .= '<option value="Pets">Pets</option>';
	$photo_form .= '<option value="Friends">Friends</option>';
	$photo_form .= '<option value="Random">Random</option>';
	$photo_form .= '</select>';
	$photo_form .= '</div>';
	$photo_form .= '</div>';
	$photo_form .= '<div class="row example-row">
<div class="col-md-3">Choose Photo</div> <!--.col-md-3-->
<div class="col-md-9">

	<div class="fileinput fileinput-new" data-provides="fileinput">
		<div class="fileinput-preview thumbnail" data-trigger="fileinput" style="width: 200px; height: 150px;"></div>
		<div>
			<span class="btn btn-default btn-file">
			<span class="fileinput-new">Select image</span>
			<span class="fileinput-exists">Change</span>
			<input type="file"name="photo" multiple accept="image/*" required>
			</span>
			<a href="#" class="btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
		</div>
	</div>

</div> <!--.col-md-9-->
</div><!--.row-->';
	$photo_form .= '<p><button type="submit" class="btn btn-default">Upload Photos Now</button></p>';
	$photo_form .= '</form>';
	$photo_form .= '</div>
									<!--.panel-body-->
								</div>
								<!--.panel-->
							</div>
							<!--.col-md-12-->
						</div>
						<!--.row-->';
}
// Select the user galleries
$gallery_list = "";
$gallery_filter = "";
$sql = "SELECT DISTINCT gallery FROM photos WHERE user='$u'";
$query = mysqli_query( $db_conx, $sql );
if ( mysqli_num_rows( $query ) < 1 ) {
	$gallery_list = "This user has not uploaded any photos yet.";
} else {
	while ( $row = mysqli_fetch_array( $query, MYSQLI_ASSOC ) ) {
		$gallery = $row[ "gallery" ];
		$countquery = mysqli_query( $db_conx, "SELECT COUNT(id) FROM photos WHERE user='$u' AND gallery='$gallery'" );
		$countrow = mysqli_fetch_row( $countquery );
		$count = $countrow[ 0 ];
		$filequery = mysqli_query( $db_conx, "SELECT filename FROM photos WHERE user='$u' AND gallery='$gallery' ORDER BY RAND() LIMIT 7" );
		$filerow = mysqli_fetch_row( $filequery );
		$file = $filerow[ 0 ];
		$gallery_list .= '<div class="col-md-3">
				<div class="card card-image" onclick="showGallery(\'' . $gallery . '\',\'' . $u . '\')">
<img src="/v2/app/dashboard/userdata/' . $u . '/' . $file . '" alt="cover photo">
					<div class="context has-action-left has-action-right">

						<div class="tile-content">
							<span class="text-title">' . $gallery . '</span>
							<span class="text-subtitle">' . $count . ' photos</span>
						</div>


					</div>

				</div><!--.card-->
			</div><!--.col-->';
		/* <figure class="animal effect-zoe magnific" data-mfp-src="../assets/global/images/gallery/9.jpg">
                      <img src="../assets/global/images/gallery/9.jpg" alt="9"/>
                      <figcaption>
                        <h2>Beautiful <span>Hover</span></h2>
                        <i class="fa fa-heart"></i>
                        <i class="fa fa-eye"></i>
                        <i class="fa fa-paperclip"></i>
                        <p>You can add many crazy hover effects.</p>
                      </figcaption>
                    </figure> */
	}
}
?>

<?php
$isFriend = false;
$ownerBlockViewer = false;
$viewerBlockOwner = false;
if ( $u != $log_username && $user_ok == true ) {
	$friend_check = "SELECT id FROM friends WHERE user1='$log_username' AND user2='$u' AND accepted='1' OR user1='$u' AND user2='$log_username' AND accepted='1' LIMIT 1";
	if ( mysqli_num_rows( mysqli_query( $db_conx, $friend_check ) ) > 0 ) {
		$isFriend = true;
	}
	$block_check1 = "SELECT id FROM blockedusers WHERE blocker='$u' AND blockee='$log_username' LIMIT 1";
	if ( mysqli_num_rows( mysqli_query( $db_conx, $block_check1 ) ) > 0 ) {
		$ownerBlockViewer = true;
	}
	$block_check2 = "SELECT id FROM blockedusers WHERE blocker='$log_username' AND blockee='$u' LIMIT 1";
	if ( mysqli_num_rows( mysqli_query( $db_conx, $block_check2 ) ) > 0 ) {
		$viewerBlockOwner = true;
	}
}
?> <?php
$friend_button = '<button type="button" class="btn btn-primary" disabled>Follow</button>';
$block_button = '<button type="button" class="btn btn-danger" disabled>Block User</button>';
// LOGIC FOR FRIEND BUTTON
if ( $isFriend == true ) {
	$friend_button = '<button class="btn btn-danger" onclick="friendToggle(\'unfollow\',\'' . $u . '\',\'friendBtn\')">Unfollow</button>';
} else if ( $user_ok == true && $u != $log_username && $ownerBlockViewer == false ) {
	$friend_button = '<button class="btn btn-primary" onclick="friendToggle(\'follow\',\'' . $u . '\',\'friendBtn\')">Follow</button>';
}


// LOGIC FOR BLOCK BUTTON
if ( $viewerBlockOwner == true ) {
	$block_button = '<button class="btn btn-danger" onclick="blockToggle(\'unblock\',\'' . $u . '\',\'blockBtn\')">Unblock User</button>';
} else if ( $user_ok == true && $u != $log_username ) {
	$block_button = '<button class="btn btn-danger" onclick="blockToggle(\'block\',\'' . $u . '\',\'blockBtn\')">Block User</button>';
}
?>

<?php
$friendsHTML = '';
$friends_view_all_link = '';
$sql = "SELECT COUNT(id) FROM friends WHERE user1='$u' AND accepted='1' OR user2='$u' AND accepted='1'";
$query = mysqli_query( $db_conx, $sql );
$query_count = mysqli_fetch_row( $query );
$friend_count = $query_count[ 0 ];
if ( $friend_count < 1 ) {
	$friendsHTML = $u . " has no followers yet";
} else {
	$max = 18;
	$all_friends = array();
	$sql = "SELECT user1 FROM friends WHERE user2='$u' AND accepted='1' ORDER BY RAND() LIMIT $max";
	$query = mysqli_query( $db_conx, $sql );
	while ( $row = mysqli_fetch_array( $query, MYSQLI_ASSOC ) ) {
		array_push( $all_friends, $row[ "user1" ] );
	}
	$sql = "SELECT user2 FROM friends WHERE user1='$u' AND accepted='1' ORDER BY RAND() LIMIT $max";
	$query = mysqli_query( $db_conx, $sql );
	while ( $row = mysqli_fetch_array( $query, MYSQLI_ASSOC ) ) {
		array_push( $all_friends, $row[ "user2" ] );
	}
	$friendArrayCount = count( $all_friends );
	if ( $friendArrayCount > $max ) {
		array_splice( $all_friends, $max );
	}
	if ( $friend_count > $max ) {
		$friends_view_all_link = '<a href="view_friends.php?u=' . $u . '">view all</a>';
	}
	$orLogic = '';
	foreach ( $all_friends as $key => $user ) {
		$orLogic .= "username='$user' OR ";
	}
	$orLogic = chop( $orLogic, "OR " );
	$sql = "SELECT username, avatar FROM users WHERE $orLogic";
	$query = mysqli_query( $db_conx, $sql );
	while ( $row = mysqli_fetch_array( $query, MYSQLI_ASSOC ) ) {
		$friend_username = $row[ "username" ];
		$friend_avatar = $row[ "avatar" ];
		$friend_pic = '/v2/app/dashboard/userdata/' . $friend_username . '/' . $friend_avatar . '';

		$friendsHTML .= '<div class="col-md-6">
								<div class="card tile card-friend"><img src="' . $friend_pic . '" class="user-photo" alt="">
									<div class="friend-content">
										<p class="title">' . $friend_username . '</p>
										<p class="caption">' . $friend_count . ' follower(s)</p>
										</p>
										<a class="btn btn-flat btn-primary btn-xs" href=/v2/app/dashboard/user/' . $friend_username . '>View Profile</a> </div>
									<!--.friend-content-->
								</div>
								<!--.card-->
							</div>';
	}
}
?>

<?php
$coverpic = "";
$sql = "SELECT filename FROM photos WHERE user='$u' ORDER BY RAND() LIMIT 1";
$query = mysqli_query( $db_conx, $sql );
if ( mysqli_num_rows( $query ) > 0 ) {
	$row = mysqli_fetch_row( $query );
	$filename = $row[ 0 ];
	$coverpic = '<img src="userdata/' . $u . '/' . $filename . '" alt="pic" class="class="effect-layla">';
}
?>
<!DOCTYPE html>
<!--[if  IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js">
<!--<![endif]--><head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?php echo $u ?> - User Profile</title>
	<meta name="description" content="">
	<meta name="author" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

	<!-- BEGIN CORE CSS -->
	<link rel="stylesheet" href="/v2/app/dashboard/assets/admin1/css/admin1.css">
	<link rel="stylesheet" href="/v2/app/dashboard/assets/globals/css/elements.css">
	<!-- END CORE CSS -->

	<!-- BEGIN PLUGINS CSS -->
	<link rel="stylesheet" href="/v2/app/dashboard/assets/globals/plugins/blueimp-gallery/css/blueimp-gallery.min.css">
	<link rel="stylesheet" href="/v2/app/dashboard/assets/globals/plugins/blueimp-bootstrap-image-gallery/css/bootstrap-image-gallery.min.css">
	<link rel="stylesheet" href="/v2/app/dashboard/assets/globals/plugins/jasny-bootstrap/dist/css/jasny-bootstrap.min.css">
	<link rel="stylesheet" href="/v2/app/dashboard/assets/globals/css/plugins.css">
	<!-- END PLUGINS CSS -->

	<!-- BEGIN SHORTCUT AND TOUCH ICONS -->
	<link rel="shortcut icon" href="/v2/app/dashboard/assets/globals/img/icons/favicon.ico">
	<link rel="apple-touch-icon" href="/v2/app/dashboard/assets/globals/img/icons/apple-touch-icon.png">
	<!-- END SHORTCUT AND TOUCH ICONS -->

	<script src="/v2/app/dashboard/assets/globals/plugins/modernizr/modernizr.min.js"></script>
	<script src="/v2/app/dashboard/js/ajax.js"></script>
	<script src="/v2/app/dashboard/js/main.js"></script>
	<script src="/v2/app/dashboard/js/jquery-3.1.1.min.js"></script>
	<style>
		div#picbox> img {
			display: block;
			margin: 0px auto;
		}

		div#picbox> button {
			display: block;
			float: right;
			padding: 3px 16px;
		}
	</style>

	<script>
		function friendToggle(a,b,c){swal({title:"Confirm "+a+".",text:"Press confirm to confirm the '"+a+"' action for user <?php echo $u; ?>.",type:"warning",showCancelButton:!0,confimButtonColor:"#DD6B55",confirmButtonText:"Confirm "+a+".",closeOnConfirm:!0},function(d){if(1!=d)return!1;var e=ajaxObj("POST","/v2/app/dashboard/friend_system.php");e.onreadystatechange=function(){1==ajaxReturn(e)&&("friend_request_sent"==e.responseText?alert("Follow Request Sent"):"unfriend_ok"==e.responseText?_(c).innerHTML="<button class=\"btn btn-block btn-primary bd-0 no-bd\" onclick=\"friendToggle('follow','<?php echo $u; ?>','friendBtn')\">Follow</button>":(alert(e.responseText),alert("Try again")))},e.send("type="+a+"&user="+b)})}function blockToggle(a,b,c){var d=confirm("Press OK to confirm the '"+a+"' action on user <?php echo $u; ?>.");if(1!=d)return!1;var c=document.getElementById(c),e=ajaxObj("POST","/v2/app/dashboard/block_system.php");e.onreadystatechange=function(){1==ajaxReturn(e)&&("blocked_ok"==e.responseText?c.innerHTML="<button class=\"btn btn-block btn-danger\" onclick=\"blockToggle('unblock','<?php echo $u; ?>','blockBtn')\">Unblock User</button>":"unblocked_ok"==e.responseText?c.innerHTML="<button class=\"btn btn-block btn-danger\" onclick=\"blockToggle('block','<?php echo $u; ?>','blockBtn')\">Block User</button>":(alert(e.responseText),alert("Try again later")))},e.send("type="+a+"&blockee="+b)}function friendReqHandler(a,b,c,d){var e=confirm("Press OK to '"+a+"' this friend request.");if(1!=e)return!1;var f=ajaxObj("POST","/v2/app/dashboard/friend_system.php");f.onreadystatechange=function(){1==ajaxReturn(f)&&("accept_ok"==f.responseText?alert("Your are now friends"):"reject_ok"==f.responseText?alert("You chose to reject friendship with this user"):alert(f.responseText))},f.send("action="+a+"&reqid="+b+"&user1="+c)}function showGallery(a,b){_("galleries").style.display="none",_("section_title").innerHTML='<button class="btn btn-primary" onclick="backToGalleries()">Back to galleries </button><br /><br/>',_("photos").style.display="block",_("photos").innerHTML="loading photos ...";var c=ajaxObj("POST","/v2/app/dashboard/photo_system.php");c.onreadystatechange=function(){if(1==ajaxReturn(c)){_("photos").innerHTML="";for(var a=c.responseText.split("|||"),d=0;d<a.length;d++){var e=a[d].split("|");_("photos").innerHTML+='<div class="col-xs-6 col-sm-4 col-md-3 col-lg-2"><a href="/v2/app/dashboard/userdata/'+b+"/"+e[1]+'" data-gallery="#user-gallery"><img onclick="photoShowcase(\''+a[d]+'\')" src="/v2/app/dashboard/userdata/'+b+"/"+e[1]+'" alt="pic"></a><div>'}_("photos").innerHTML+='<p style="clear:left;"></p>'}},c.send("show=galpics&gallery="+a+"&user="+b)}function backToGalleries(){_("photos").style.display="none",_("galleries").style.display="block",_("section_title").style.display="none"}function photoShowcase(a){var b=a.split("|");_("photos").style.display="none",_("picbox").style.display="block",_("section_title").innerHTML="",_("picbox").innerHTML='<button style="float:right" class="btn btn-floating btn-primary" onclick="closePhoto()">x</button>',_("picbox").innerHTML+='<img src="/v2/app/dashboard/userdata/<?php echo $u; ?>/'+b[1]+'" alt="photo">'}function closePhoto(){_("picbox").innerHTML="",_("picbox").style.display="none",_("photos").style.display="block",_("section_title").style.display="block"}function deletePhoto(a){var b=confirm("Press OK to confirm the delete action on this photo.");if(1!=b)return!1;_("deletelink").style.visibility="hidden";var c=ajaxObj("POST","/v2/app/dashboardphoto_system.php");c.onreadystatechange=function(){1==ajaxReturn(c)&&"deleted_ok"==c.responseText&&alert("This picture has been deleted successfully. We will now refresh the page for you.")},c.send("delete=photo&id="+a)}function getNames(a){var b=new RegExp;b=/[^a-z0-9]/gi;var c=a.search(b)>=0;if(c&&(a=a.replace(b,""),document.getElementById("searchUsername").value=a),""==a)return document.getElementById("memSearchResults").style.display="none",!1;var d=new XMLHttpRequest;d.open("POST","/v2/app/dashboard/search_exec.php",!0),d.setRequestHeader("Content-type","application/x-www-form-urlencoded"),d.onreadystatechange=function(){if(4==d.readyState&&200==d.status){var a=d.responseText;""!=a&&(document.getElementById("memSearchResults").style.display="block",document.getElementById("memSearchResults").innerHTML=a)}},d.send("u="+a)}function scrollFunction(){document.getElementById("memSearchResults").style.display="none"}window.addEventListener("mouseup",function(a){var b=document.getElementById("memSearchResults");a.target!=b&&a.target.parentNode!=b&&(b.style.display="none")});
</script>
	</script>
	<style type="text/css">
		div#profile_pic_box> a {
			display: none;
			position: absolute;
			margin: 140px 0px 0px 120px;
			z-index: 4000;
			background: #D8F08E;
			border: #81A332 1px solid;
			border-radius: 3px;
			padding: 5px;
			font-size: 12px;
			text-decoration: none;
			color: #60750B;
		}

		div#profile_pic_box> form {
			display: none;
			position: absolute;
			z-index: 3000;
			padding: 10px;
			opacity: .8;
			width: 180px;
			height: 180px;
		}

		div#profile_pic_box:hover a {
			display: block;
		}

		img.friendpics {
			1px solid;
			width: 40px;
			height: 40px;
			margin: 2px;
		}

		.hiddenStuff {
			display: none;
		}

		.triggerBtn {
			float: right;
			cursor: pointer;
			margin-right: 500px;
		}

		img.statusImage {
			max-width: 200px;
		}
	</style>
</head>

<body>
	<div class="nav-bar-container">

		<!-- BEGIN ICONS -->
		<div class="nav-menu">
			<div class="hamburger"> <span class="patty"></span> <span class="patty"></span> <span class="patty"></span> <span class="patty"></span> <span class="patty"></span> <span class="patty"></span> </div>
			<!--.hamburger-->
		</div>
		<!--.nav-menu-->

		<div class="nav-search"> <span class="search"></span> </div>
		<!--.nav-search-->

		<div class="nav-user">
			<div class="user">
				<?php echo $profile_pic ?>
				<div class="badge" id="notes"></div>

				<script>
					/*$(document).ready(function(){
						$('#notes').load('/v2/app/dashboard/badge.php');
						notes();
					});
					function notes()
					{
						setTimeout( function() {
						  $('#notes').load('/v2/app/dashboard/badge.php');
						  notes();
						}, 1000);
					}*/
				</script>

			</div>
			<!--.user-->
			<div class="cross"> <span class="line"></span> <span class="line"></span> </div>
			<!--.cross-->
		</div>
		<!--.nav-user-->
		<!-- END OF ICONS -->

		<div class="nav-bar-border"></div>
		<!--.nav-bar-border-->

		<!-- BEGIN OVERLAY HELPERS -->
		<div class="overlay">
			<div class="starting-point"> <span></span> </div>
			<!--.starting-point-->
			<div class="logo">Total Social</div>
			<!--.logo-->
		</div>
		<!--.overlay-->

		<div class="overlay-secondary"></div>
		<!--.overlay-secondary-->
		<!-- END OF OVERLAY HELPERS -->

	</div>
	<!--.nav-bar-container-->

	<div class="content">
		<div class="page-header full-content parallax" style="height: 600px; overflow: hidden">
			<div class="profile-info">
				<div class="profile-photo">
					<?php echo $profile_pic; ?>
				</div>
				<!--.profile-photo-->
				<div class="profile-text light">
					<?php echo $u ?>
					<?php echo $vBadage ?>
					<div class="pull-right">
						&nbsp;
						<?php echo $friend_button ?>
						<?php echo $block_button ?>
					</div>


				</div>

				<!--.profile-text-->

			</div>
			<!--.profile-info-->

			<div class="row">
				<div class="col-sm-6">
					<h1>User Profile <small><?php echo $u ?></small></h1>
				</div>
				<!--.col-->
				<div class="col-sm-6">
					<ol class="breadcrumb">
						<li><a href="#"><i class="ion-home"></i></a>
						</li>
						<li><a href="#" class="active">User Profile</a>
						</li>
					</ol>
				</div>
				<!--.col-->
			</div>
			<!--.row-->

			<div class="header-tabs scrollable-tabs sticky">
				<ul class="nav nav-tabs tabs-active-text-white tabs-active-border-yellow">
					<li class="active"><a href="#timeline" data-toggle="tab" class="btn-ripple">Timeline</a>
					</li>
					<li><a href="#about" data-toggle="tab" class="btn-ripple">About</a>
					</li>
					<li><a href="#gallery" data-toggle="tab" class="btn-ripple">Photos</a>
					</li>
					<li><a href="#followers" data-toggle="tab" class="btn-ripple">Followers</a>
					</li>
				</ul>
			</div>
		</div>
		<!--.page-header-->

		<div class="row user-profile">
			<div class="col-md-12">
				<div class="tab-content without-border">
					<div id="timeline" class="tab-pane active">
						<?php include("php_includes/template_status.php"); ?>
						<div id="statusui">
							<?php echo $status_ui; ?>
						</div>

						<div class="row masonry">
							<?php include("php_includes/template_statuslist.php"); ?>
						</div>

						<!--


          <div class="col-md-4">
              <div class="card tile card-post">
                <div class="card-heading"> <img src="/assets/globals/img/faces/14.jpg" class="user-image" alt="">
                  <p class="author"><a href="#">Ryan Perkins</a> shared a post.</p>
                  <p class="time">September 5 at 02:33</p>
                </div>
                <!--.card-heading
                <div class="card-body"> Collaboratively administrate empowered markets via plug-and-play networks. Dynamically procrastinate B2C users after installed base benefits. Dramatically visualize customer directed convergence without revolutionary ROI.
                  <ul class="post-action">
                    <li><a href="#">Like</a></li>
                    <li><a href="#">Comment</a></li>
                    <li><a href="#">Share</a></li>
                  </ul>
                </div>
                <!--.card-body
                <div class="card-footer">
                  <div class="post-likers"> <a href="#">Crystal Wells</a> and <a href="#">30 others</a> like this. </div>
                  <!--.post-likers
                  <ul class="card-comments">
                    <li>
                      <div class="user-photo"><a href="#"><img src="/assets/globals/img/faces/3.jpg" alt=""></a></div>
                      <div class="comment"><a href="#">Jonathan Diaz</a> I love it and i will share in my timeline</div>
                    </li>
                    <li>
                      <div class="user-photo"><a href="#"><img src="/assets/globals/img/faces/16.jpg" alt=""></a></div>
                      <div class="comment"><a href="#">Douglas Hall</a> One of my favourite quotes from the old days. So glad it's back! :-)</div>
                    </li>
                  </ul>
                  <div class="input-group">
                    <div class="inputer">
                      <div class="input-wrapper">
                        <input type="text" class="form-control">
                      </div>
                    </div>
                    <div class="input-group-btn">
                      <button type="button" class="btn btn-default">Send</button>
                    </div>
                    <!--.input-group-btn
                  </div>
                  <!--.input-group
                </div>
                <!--.card-footer
              </div>
              <!--.card
            </div>
            <!--.col-md-4
          -->
					</div>
					<!--#timeline.tab-pane-->

					<div id="about" class="tab-pane">
						<div class="row">
							<div class="col-md-3">
								<ul class="nav nav-tabs borderless vertical">
									<li class="active"><a href="#about_overview" data-toggle="tab">Overview</a>
									</li>
									<li><a href="#about_login" data-toggle="tab">Login Info</a>
									</li>
								</ul>
							</div>
							<!--.col-md-3-->
							<div class="col-md-9">
								<div class="tab-content">
									<div class="tab-pane active" id="about_overview">

										<div class="legend">About</div>
										<div class="row">
											<div class="col-md-3">Gender</div>
											<!--.col-md-3-->
											<div class="col-md-9">
												<?php echo $sex ?>
											</div>
											<!--.col-md-9-->
										</div>
										<!--.row-->
										<div class="row">
											<div class="col-md-3">Country:</div>
											<!--.col-md-3-->
											<div class="col-md-9">
												<?php echo $country ?>
											</div>
											<!--.col-md-9-->
										</div>
										<!--.row-->
										<div class="row">
											<div class="col-md-3">Email</div>
											<!--.col-md-3-->
											<div class="col-md-9">
												<?php echo $email ?>
											</div>
											<!--.col-md-9-->
										</div>
										<!--.row-->
										<div class="row">
											<div class="col-md-3">User level</div>
											<!-- .col-md-3 -->
											<div class="col-md-9">
												<?php echo $uLevel ?>
											</div>
											<!--.col-md-9-->
										</div>
									</div>
									<!--#about_overview.tab-pane-->

									<div class="tab-pane" id="about_login">
										<div class="legend">Login/Registration Info</div>
										<div class="row">
											<div class="col-md-3">Logged in & Active?</div>
											<!--.col-md-3-->
											<div class="col-md-9">
												<?php echo $isOwner ?>
											</div>
											<!--.col-md-9-->
										</div>
										<!--.row-->
										<div class="row">
											<div class="col-md-3">Join Date</div>
											<!--.col-md-3-->
											<div class="col-md-9">
												<?php echo $joindate ?>
											</div>
											<!--.col-md-9-->
										</div>
										<!--.row-->
										<div class="row">
											<div class="col-md-3">Last Active:</div>
											<!--.col-md-3-->
											<div class="col-md-9">
												<?php echo $lastsession ?>
											</div>
											<!--.col-md-9-->
										</div>
									</div>
									<!--#about_timeline.tab-pane-->

								</div>
								<!--.tab-content-->

							</div>
							<!--.col-md-9-->
						</div>
						<!--.row-->
					</div>
					<!--#about.tab-pane-->

					<div id="gallery" class="tab-pane">
						<?php echo $photo_form ?>
						<div class="image-row">
						  <div id="section_title"></div>

							<div id="galleries">
								<?php echo $gallery_list; ?>
							</div>
							<div id="photos"></div>
							<div id="picbox"></div>
							<!-- row -->
						</div>
					</div>
					<!--#photos.tab-pane-->
					<div id="followers" class="tab-pane">
						<div class="row">
							<?php echo $friendsHTML; ?>
						</div>



					</div>
					<!--#friends.tab-pane-->
				</div>
				<!--.tab-content-->
			</div>
			<!--.col-->
		</div>
		<!--.row-->
		<!-- Bootstrap Image Gallery lightbox -->
		<!-- To use original bootstrap modal window erase data-use-boostrap-model attr -->
		<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-use-bootstrap-modal="false">
			<div class="slides"></div>
			<h3 class="title">Gallery</h3>
			<a class="prev">‹</a> <a class="next">›</a> <a class="close">×</a> <a class="play-pause"></a>
			<ol class="indicator">
			</ol>
			<div class="modal fade">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" aria-hidden="true">&times;</button>
							<h4 class="modal-title">Gallery</h4>
						</div>
						<div class="modal-body next"></div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default pull-left prev"> <i class="glyphicon glyphicon-chevron-left"></i> Previous </button>
							<button type="button" class="btn btn-primary next"> Next <i class="glyphicon glyphicon-chevron-right"></i> </button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End of Bootstrap Image Gallery lightbox -->

	</div>
	<!--.content-->

	<div class="layer-container">

		<!-- BEGIN MENU LAYER -->
		<div class="menu-layer">
			<ul>
				<?php echo $loginLink; ?>
			</ul>
		</div>
		<!--.menu-layer-->
		<!-- END OF MENU LAYER -->

<!-- BEGIN SEARCH LAYER -->
		<div class="search-layer">
			<div class="search">
				<form >
				  <div class="form-group">
						<input type="text" id="input-search" alt=""onKeyUp="getNames(this.value)"  class="form-control" placeholder="Find a Member">
						<button type="submit" class="btn btn-default disabled"><i class="ion-search"></i></button>
				  </div>
				</form>
			</div>
			<!--.search-->

			<div class="results">
			  <div class="row">
				<div class="col-md-4">
				  <div class="result result-users">
					  <ul class="list-material" id="memSearchResults">

				    </ul>
					</div>
					<!--.results-user-->
				  </div>
				  <!--.col-->
				</div>
					<!--.row-->
		  </div>
				<!--.results-->
</div>
			<!--.search-layer-->
			<!-- END OF SEARCH LAYER -->

		<?php include("top.php"); ?>

		<!-- BEGIN GLOBAL AND THEME VENDORS -->
		<script src="/v2/app/dashboard/assets/globals/js/global-vendors.js"></script>
		<!-- END GLOBAL AND THEME VENDORS -->

		<!-- BEGIN PLUGINS AREA -->
		<script src="/v2/app/dashboard/assets/globals/plugins/handlebars/handlebars.min.js"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/strength/strength.min.js"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/indicator/indicator.js"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/pwstrength-bootstrap/dist/pwstrength-bootstrap-1.2.2.min.js"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/jquery.inputmask/dist/jquery.inputmask.bundle.js"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/ipmask/jquery.input-ip-address-control.min.js"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/jquery.payment/lib/jquery.payment.js"></script>
		<script src="https://www.google.com/recaptcha/api.js"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/masonry/dist/masonry.pkgd.min.js"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/blueimp-gallery/js/jquery.blueimp-gallery.min.js"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/blueimp-bootstrap-image-gallery/js/bootstrap-image-gallery.min.js"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/typehead.js/dist/typeahead.bundle.min.js"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/overlay/jquery.overlay.js"></script>
		<script src="/v2/app/dashboard/assets/globals/scripts/user-pages.js"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/jquery-textcomplete/dist/jquery.textcomplete.min.js"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/emojify.js/emoji-list.js"></script>
		<script src="https://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/ubilabs-geocomplete/jquery.geocomplete.min.js"></script>
		<script src="/v2/app/dashboard/assets/globals/plugins/jasny-bootstrap/dist/js/jasny-bootstrap.min.js"></script>
		<!-- END PLUGINS AREA -->

		<!-- PLUGINS INITIALIZATION AND SETTINGS -->
		<script src="/v2/app/dashboard/assets/globals/scripts/forms-tools.js"></script>
		<!-- END PLUGINS INITIALIZATION AND SETTINGS -->

		<!-- PLEASURE -->
		<script src="/v2/app/dashboard/assets/globals/js/pleasure.js"></script>
		<!-- ADMIN 1 -->
		<script src="/v2/app/dashboard/assets/admin1/js/layout.js"></script>

		<!-- END PLUGINS INITIALIZATION AND SETTINGS -->

		<!-- BEGIN INITIALIZATION-->
		<script>
			$( document ).ready( function () {
				Pleasure.init();
				Layout.init();
				FormsTools.init();
			} );
		</script>
		<!-- END INITIALIZATION-->

		<!-- BEGIN Google Analytics -->
		<script>
			( function ( i, s, o, g, r, a, m ) {
				i[ 'GoogleAnalyticsObject' ] = r;
				i[ r ] = i[ r ] || function () {
					( i[ r ].q = i[ r ].q || [] ).push( arguments )
				}, i[ r ].l = 1 * new Date();
				a = s.createElement( o ),
					m = s.getElementsByTagName( o )[ 0 ];
				a.async = 1;
				a.src = g;
				m.parentNode.insertBefore( a, m )
			} )( window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga' );

			ga( 'create', Pleasure.settings.ga.urchin, Pleasure.settings.ga.url );
			ga( 'send', 'pageview' );
		</script>
		<!-- END Google Analytics -->

</body>

</html>
