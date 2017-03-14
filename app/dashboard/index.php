<?php
//session_start();
include( "php_includes/check_login_status.php" );
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
$all_friends = "";
$friendsCSV = "";
$thisPage = basename($_SERVER['PHP_SELF']);
$thisGroup = "";
$agList = "";
$mgList = "";
$account_status;
$_SESSION['user_id'] = md5(time());
$_SESSION['group'] = "notSet";

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
}

// Fetch the user row from the query above
while ( $row = mysqli_fetch_array( $user_query, MYSQLI_ASSOC ) ) {
	$profile_id = $row[ "id" ];
	$gender = $row[ "gender" ];
	$country = $row[ "country" ];
	$userlevel = $row[ "userlevel" ];
	$avatar = $row[ "avatar" ];
	$signup = $row[ "signup" ];
	$lastlogin = $row[ "lastlogin" ];
	$loginip = $row["loginip"];
	$signupip = $row["ip"];
	$ipdiff = $row["ipdiff"];
	$joindate = strftime( "%b %d, %Y", strtotime( $signup ) );
	$lastsession = strftime( "%b %d, %Y", strtotime( $lastlogin ) );
	if ( $gender == "f" ) {
		$sex = "Female";
	}

	if ( $avatar == NULL ) {
		//copy avatar
		$avatar = "user.png";
		$avatar2 = "userdata/$u/user.png";
		if ( !copy( $avatar, $avatar2 ) ) {
			echo "failed to copy!";
		}
	}
	$profile_pic = '<img src="/v2/app/dashboard/userdata/' . $u . '/' . $avatar . '" alt="' . $u . '">';
}
if ($signupip != $loginip){
	//running a query
	$sql = "UPDATE users SET ipdiff='1' WHERE username='$log_username'";
	$query = mysqli_query($db_conx, $sql);
}
if ($ipdiff == '1' && $signupip == $loginip) {
	//running a query
	$sql = "UPDATE users SET ipdiff='0' WHERE username='$log_username'";
	$query = mysqli_query($db_conx, $sql);
}

if($ipdiff == '0'){
$account_status = '<h3 class="text-capitalize text-green"><i class="ion-checkmark-circled"></i>
Account <strong>Protected</strong></h3>';
}
if ($ipdiff == '1'){
$account_status = '<h3 class="text-capitalize text-orange"><i class="ion-information-circled"></i>
<strong>IP</strong> Changed</h3><p>The last IP address you signed onto was: <strong>'.$loginip.'</strong>, which differs from the registration IP address,<strong>' .$signupip.'</strong>.</p><hr>
<button onclick="return false;" onmousedown="trustIP(\''.$loginip.'\');" class="btn btn-primary">Save changes</button> <button class="btn btn-danger" disabled>That was not me!</button>';
}

// Make sure the user is logged in and sanitize the session
if ( isset( $_SESSION[ 'username' ] ) ) {
	$u = $_SESSION[ 'username' ];
} else {
	header( "location: /v2/app/dashboard/account-access" );
	exit();
}
// get array of friends
$sql = "SELECT COUNT(id) FROM friends WHERE user1='$u' AND accepted='1' OR user2='$u' AND accepted='1'";
$query = mysqli_query( $db_conx, $sql );
$query_count = mysqli_fetch_row( $query );
$friend_count = $query_count[ 0 ];
if ( $friend_count < 1 ) {

} else {
	$all_friends = array();
	$sql = "SELECT user1, user2 FROM friends WHERE (user2='$u' OR user1='$u') AND accepted='1'";
	$query = mysqli_query( $db_conx, $sql );
	while ( $row = mysqli_fetch_array( $query, MYSQLI_ASSOC ) ) {
		if ( $row[ "user1" ] != $u ) {
			array_push( $all_friends, $row[ "user1" ] );
		}
		if ( $row[ "user2" ] != $u ) {
			array_push( $all_friends, $row[ "user2" ] );
		}
	}
}
// get feed
// based loosely on code in template_status.php
// my method to get images is based on my other video tutorial
// that always has a value in the database
// https://www.youtube.com/watch?v=U79z3ZJSBSc
// if you do not edit yours and have default images, this will not work properly
// broken image links and maybe errors
$statuslist = "";
$friendsCSV = join("','", $all_friends);
// all 1 line
$sql = "SELECT s.*, u.avatar
		FROM status AS s
		LEFT JOIN users AS u ON u.username = s.author
		WHERE s.author IN ('$friendsCSV') AND (s.type='a' OR s.type='c')
		ORDER BY s.postdate DESC LIMIT 20";

$query = mysqli_query( $db_conx, $sql );
$statusnumrows = mysqli_num_rows( $query );
if ( $statusnumrows > 0 ) {
	while ( $row3 = mysqli_fetch_array( $query, MYSQLI_ASSOC ) ) {
		$statusid = $row3[ "id" ];
		$account_name = $row3[ "account_name" ];
		$author = $row3[ "author" ];
		$postdate = $row3[ "postdate" ];
		$data = $row3[ "data" ];
		$avatar3 = $row3[ "avatar" ];
		$data = nl2br( $data );
		$data = str_replace( "&amp;", "&", $data );
		$data = stripslashes( $data );
		$statusDeleteButton = '';
		$profile_pic2 = '<img src="/v2/app/dashboard/userdata/' . $author . '/' . $avatar . '" class="user-image" />';

		if ( $author == $log_username || $account_name == $log_username ) {
		$statusDeleteButton = '<a id="sdb_' . $statusid . '" href="#" onclick="return false;" onmousedown="deleteStatus(\'' . $statusid . '\',\'status_' . $statusid . '\');" title="DELETE THIS STATUS AND ITS REPLIES">Delete</a> &nbsp; &nbsp;';
		}
		$shareButton = "";
		if($log_username != "" && $author != $log_username && $account_name != $log_username){
				$shareButton = '<a href="#" onclick="return false;" onmousedown="shareStatus(\''.$statusid.'\');" title="SHARE THIS">Share</a>';
		}
		// GATHER UP ANY STATUS REPLIES
		$status_replies = "";
		// all 1 line
		$sql2 = "SELECT s.*, u.avatar
			 	FROM status AS s
			 	LEFT JOIN users AS u ON u.username = s.author
			 	WHERE s.osid = '$statusid'
			 	AND s.type='b'
			 	ORDER BY postdate ASC";
		$query_replies = mysqli_query( $db_conx, $sql2 );
		$replynumrows = mysqli_num_rows( $query_replies );
		if ( $replynumrows > 0 ) {
			while ( $row2 = mysqli_fetch_array( $query_replies, MYSQLI_ASSOC ) ) {
				$statusreplyid = $row2[ "id" ];
				$replyauthor = $row2[ "author" ];
				$replydata = $row2[ "data" ];
				$replydata = nl2br( $replydata );
				$replypostdate = $row2[ "postdate" ];
				$avatar2 = $row2[ "avatar" ];
				$replydata = str_replace( "&amp;", "&", $replydata );
				$replydata = stripslashes( $replydata );
				$replyDeleteButton = '';
				$profile_pic3 = '<img src="/v2/app/dashboard/userdata/' . $replyauthor . '/' . $avatar2 . '"  />';

				if ( $replyauthor == $log_username || $account_name == $log_username ) {
				$replyDeleteButton = '<a id="srdb_' . $statusreplyid . '" href="#" onclick="return false;" onmousedown="deleteReply(\'' . $statusreplyid . '\',\'reply_' . $statusreplyid . '\');" title="DELETE THIS COMMENT">remove</a>';
			}
				// all 1 line
			$status_replies .= '<li id="reply_' . $statusreplyid . '"><div class="user-photo"><a href="/v2/app/dashboard/user/' . $replyauthor . '">'.$profile_pic.'</a></div><div class="comment">' . $replydata . ' <br /> ' . $replyDeleteButton . '</div></li>';			}
		}
		// all 1 line
		$statuslist .= '
		<div id="status_' . $statusid . '" class="col-md-4">
              <div class="card tile card-post">
                <div class="card-heading">'.$profile_pic2.'
                  <p class="author"><a href="/v2/app/dashboard/user/' . $author . '">' . $author . '</a></p>
                  <p class="time">' . date('F d, Y - g:ia', strtotime($postdate)).  '</p>
                </div>
                <!--.card-heading -->
                <div class="card-body">' . $data . '
                  <ul class="post-action">
                    <li>'.$shareButton.'</li>
					<li>' . $statusDeleteButton . '</li>
                  </ul>
                </div>
                <!--.card-body -->
                <div class="card-footer">
                  <ul class="card-comments" id="replies_' . $statusid . '">
                   ' . $status_replies . '
                  </ul>
				  <div class="input-group">
                    <div class="inputer">
                      <div class="input-wrapper">
                        <textarea id="replytext_' . $statusid . '" class="replytext form-control maxlength maxlength-textarea" maxlength="250" rows="1" placeholder="Write a comment here"></textarea>
                      </div>
                    </div>
                    <div class="input-group-btn">
                      <button id="replyBtn_' . $statusid . '" onclick="replyToStatus(' . $statusid . ',\'' . $u . '\',\'replytext_' . $statusid . '\',this)" type="button" class="btn btn-default">Send</button>
                    </div>
                    <!--.input-group-btn -->
                  </div>
                  <!--.input-group-->
                </div>
                <!--.card-footer -->
              </div>
              <!--.card -->
			</div>
            <!--.col-md-4 -->';
	}
}
if ($thisPage == "group.php"){
	if(isset($_GET["g"])){
		$thisGroup = preg_replace('#[^a-z0-9_]#i', '', $_GET['g']);
		$_SESSION['group'] = $thisGroup;
	}
}
if (isset($_SESSION['username'])) {
// All groups list
	$query = mysqli_query($db_conx, "SELECT name,logo FROM groups");
	$g_check = mysqli_num_rows($query);
	if ($g_check > 0){
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			$agList .= '<a href="/v2/app/dashboard/group?g='.$row["name"].'"><img src="/v2/app/dashboard/groups/'.$row["name"].'/'.$row["logo"].'" alt="'.$row["name"].'" title="'.$row["name"].'" width="50" height="50" border="0" /> '.$row["name"].'</a><br />';

		}
	}
	else {
		$agList = 'No groups have been created.';
	}
// My groups list
	$sql = "SELECT gm.gname, gp.logo
			FROM gmembers AS gm
			LEFT JOIN groups AS gp ON gp.name = gm.gname
			WHERE gm.mname = '$log_username'";
	$query = mysqli_query($db_conx, $sql);
	$g_check = mysqli_num_rows($query);
	if ($g_check > 0){
		while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
			$mgList .= '<a href="/v2/app/dashboard/group?g='.$row['gname'].'"><img src="/v2/app/dashboard/groups/'.$row['gname'].'/'.$row['logo'].'" alt="'.$row['gname'].'" title="'.$row['gname'].'" width="50" height="50" border="0"/> '.$row["gname"].'</a><br />';
		}
	}
	else {
		$mgList = 'You are not apart of any group!';
	}
}
?>

<?php include("php_includes/template_PageTop_index.php"); ?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js">
<!--<![endif]-->

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<title>Total Social New Dashboard</title>

	<meta name="description" content="Pleasure is responsive, material admin dashboard panel">
	<meta name="author" content="Teamfox">

	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<meta name="apple-touch-fullscreen" content="yes">

	<!-- BEGIN CORE CSS -->
	<link rel="stylesheet" href="/v2/app/dashboard/assets/admin1/css/admin1.css">
	<link rel="stylesheet" href="/v2/app/dashboard/assets/globals/css/elements.css">
	<!-- END CORE CSS -->

	<!-- BEGIN PLUGINS CSS -->
	<link rel="stylesheet" href="/v2/app/dashboard/assets/globals/plugins/rickshaw/rickshaw.min.css">
	<link rel="stylesheet" href="/v2/app/dashboard/assets/globals/plugins/bxslider/jquery.bxslider.css">

	<link rel="stylesheet" href="/v2/app/dashboard/assets/globals/css/plugins.css">
	<!-- END PLUGINS CSS -->

	<!-- BEGIN SHORTCUT AND TOUCH ICONS -->
	<link rel="shortcut icon" href="/v2/app/dashboard/assets/globals/img/icons/favicon.ico">
	<link rel="apple-touch-icon" href="/v2/app/dashboard/assets/globals/img/icons/apple-touch-icon.png">
	<!-- END SHORTCUT AND TOUCH ICONS -->

	<script src="/v2/app/dashboard/assets/globals/plugins/modernizr/modernizr.min.js"></script>
</head>
<script src="/v2/app/dashboard/js/sA/dist/sweetalert.min.js"></script>
<link rel="stylesheet" type="text/css" href="/v2/app/dashboard/js/sA/dist/sweetalert.css">
<script src="/v2/app/dashboard/js/ajax.js"></script>
<script src="/v2/app/dashboard/js/main.js"></script>
<script src="/v2/app/dashboard/js/swords.js"></script>
<script>
function postToStatus( action, type, user, ta ) {
		var data = _( ta ).value;
		if (data == "") {
			sweetAlert( "Looks like you haven't typed anything.", " You can't post nothing. Go ahead and type something :)", "error" );
			return false;
		}
		_( "statusBtn" ).disabled = true;
		var ajax = ajaxObj( "POST", "/v2/app/dashboard/status_system.php" );
		ajax.onreadystatechange = function () {
			data.trim();
			if ( ajaxReturn( ajax ) == true ) {
				var datArray = ajax.responseText.split( "|" );
				if ( datArray[ 0 ] == "post_ok" ) {
					var sid = datArray[ 1 ];
					data = data.replace( /</g, "&lt;" ).replace( />/g, "&gt;" ).replace( /\n/g, "<br />" ).replace( /\r/g, "<br />" );
					var currentHTML = _( "statusarea" ).innerHTML;
					_( "statusarea" ).innerHTML = '<div id="status_' + sid + '" class="col-md-4"> <div class="card tile card-post"> <div class="card-heading"><?php echo $profile_pic ?><p class="author">Yourself</p> <p class="time">Just Now</p> </div> <div class="card-body"> ' + data + ' <ul class="post-action"> <li><a href="#">Share</a></li> <li><a id="sdb_' + sid + '" href="#" onclick="return false;" onmousedown="deleteStatus(\'' + sid + '\',\'status_' + sid + '\');" title="DELETE THIS STATUS AND ITS REPLIES">Delete</a></li> </ul> </div> <div class="card-footer"> <ul class="card-comments" id="replies_' + sid + '"></ul> <div class="input-group"> <div class="inputer"> <div class="input-wrapper"> <textarea id="replytext_' + sid + '" class=" form-control maxlength maxlength-textarea" maxlength="250" rows="1" placeholder="Write a comment here"></textarea> </div> </div> <div class="input-group-btn"> <button id="replyBtn_' + sid + '" onclick="replyToStatus(' + sid + ',\'<?php echo $u; ?>\',\'replytext_' + sid + '\',this)" type="button" class="btn btn-default">Send</button> </div> </div> </div> </div> </div>' + currentHTML;
					_( "statusBtn" ).disabled = false;
					_( ta ).value = "";
				} else {
					alert( ajax.responseText );
				}
			}
		}
		ajax.send( "action=" + action + "&type=" + type + "&user=" + user + "&data=" + data );
	}

	function replyToStatus( sid, user, ta, btn ) {
		var data = _( ta ).value;
		if ( data == "" ) {
			sweetAlert( "Replies cannot be empty", "Please enter something and try again!", "error" );
			return false;
		}
		var compaare = data.toLowerCase();
    var $str1 = compaare;
    for (var i = 0; i < badwordarray.length; i++){
    var $str2 = badwordarray[i];
    var perc=Math.round(similarity($str1,$str2)*10000)/100;
    //console.log(perc);
    //console.log($str2);
    if (perc > 50){
        swal( "Your post was too similar to our swear words." , "Your post, "+$str1+ " was "+perc+"% similar to our list of swear words. As a result, you cannot continue until you make your post less vulgar.", "error" );
        return false;
    }
}
		_( "replyBtn_" + sid ).disabled = true;
		var ajax = ajaxObj( "POST", "/v2/app/dashboard/status_system.php" );
		ajax.onreadystatechange = function () {
			if ( ajaxReturn( ajax ) == true ) {
				var datArray = ajax.responseText.split( "|" );
				if ( datArray[ 0 ] == "reply_ok" ) {
					var rid = datArray[ 1 ];
					data = data.replace( /</g, "&lt;" ).replace( />/g, "&gt;" ).replace( /\n/g, "<br />" ).replace( /\r/g, "<br />" );
					_( "replies_" + sid ).innerHTML += '<li id="reply_' + rid + '" class="reply_boxes"><div class="user-photo"><?php echo $profile_pic2 ?></div><div class="comment">' + data + ' <br /> <a id="srdb_' + rid + '" href="#" onclick="return false;" onmousedown="deleteReply(\'' + rid + '\',\'reply_' + rid + '\');" title="DELETE THIS COMMENT">remove</a></div></li>';
					_( "replyBtn_" + sid ).disabled = false;
					_( ta ).value = "";
				} else {
					alert( ajax.responseText );
				}
			}
		}
		ajax.send( "action=status_reply&sid=" + sid + "&user=" + user + "&data=" + data );
	}

	function deleteStatus( statusid, statusbox ) {

		sweetAlert( {
				title: "Are you sure?",
				text: "You will not be able to recover this post!",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Yes, delete it!",
				closeOnConfirm: true
			},

			function ( conf ) {
				if ( conf != true ) {
					return false;
				} else {
					var ajax = ajaxObj( "POST", "/v2/app/dashboard/status_system.php" );
					ajax.onreadystatechange = function () {
						if ( ajaxReturn( ajax ) == true ) {
							if ( ajax.responseText == "delete_ok" ) {
								_( statusbox ).style.display = 'none';
								_( "replytext_" + statusid ).style.display = 'none';
								_( "replyBtn_" + statusid ).style.display = 'none';
							} else {
								SweetAlert( ajax.responseText );
							}
						}
					}
					ajax.send( "action=delete_status&statusid=" + statusid );
				}
			} );
	}

	function deleteReply( replyid, replybox ) {
		sweetAlert( {
				title: "Are you sure?",
				text: "You will not be able to recover this reply!",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Yes, delete it!",
				closeOnConfirm: true
			},

			function ( conf ) {
				if ( conf != true ) {
					return false;
				} else {
					var ajax = ajaxObj( "POST", "/v2/app/dashboard/status_system.php" );
					ajax.onreadystatechange = function () {
						if ( ajaxReturn( ajax ) == true ) {
							if ( ajax.responseText == "delete_ok" ) {
								_( replybox ).style.display = 'none';
							} else {
								alert( ajax.responseText );
							}
						}
					}
					ajax.send( "action=delete_reply&replyid=" + replyid );
				}
			} );

	}

	function statusMax( field, maxlimit ) {
		if ( field.value.length > maxlimit ) {
			alert( maxlimit + " maximum character limit reached" );
			field.value = field.value.substring( 0, maxlimit );
		}
	}
function checkGname(){
	var u = _("gname").value;
	var rx = new RegExp;
	rx = /[^a-z 0-9_]/gi;
	u = u.replace(rx, "");
	var rxx = new RegExp;
	rxx = /[ ]/g;
	u = u.replace(rxx, "_");

	if(u != ""){
		_("gnamestatus").innerHTML = 'checking ...';
		var ajax = ajaxObj("POST", "/v2/app/dashboard/group_parser.php");
        ajax.onreadystatechange = function() {
	        if(ajaxReturn(ajax) == true) {
	            _("gnamestatus").innerHTML = ajax.responseText;
	        }
        }
        ajax.send("gnamecheck="+u);
	}
}
function createGroup(){
	var name = _("gname").value;
	var inv = _("invite").value;
	if(name == "" || inv == "null"){
		alert("Fill all fields");
		return false;
	} else {
		status.innerHTML = 'please wait ...';
		var ajax = ajaxObj("POST", "/v2/app/dashboard/group_parser.php");
		ajax.onreadystatechange = function() {
			if(ajaxReturn(ajax) == true) {
				var datArray = ajax.responseText.split("|");
				if(datArray[0] == "group_created"){
				var sid = datArray[1];
					window.location = "/v2/app/dashboard/group?g="+sid;
				} else {
					alert(ajax.responseText);
				}
			}
		}
		ajax.send("action=new_group&name="+name+"&inv="+inv);
	}
}
			function getNames(u){
	var rx = new RegExp;
	rx = /[^a-z0-9]/gi;
	var replaced = u.search(rx) >= 0;
	if(replaced){
    	u = u.replace(rx, "");
		document.getElementById("searchUsername").value = u;
	}
	if(u == ""){
		document.getElementById("memSearchResults").style.display = "none";
		return false;
	}
	// https://www.developphp.com/view.php?tid=1185
    var hr = new XMLHttpRequest();
    hr.open("POST", "/v2/app/dashboard/search_exec.php", true);
    hr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    hr.onreadystatechange = function() {
	    if(hr.readyState == 4 && hr.status == 200) {
		    var return_data = hr.responseText;
			if(return_data != ""){
				document.getElementById("memSearchResults").style.display = "block";
				document.getElementById("memSearchResults").innerHTML = return_data;
			}
	    }
    }
    hr.send("u="+u);
}

window.addEventListener('mouseup', function(event){
	var box = document.getElementById('memSearchResults');
	if (event.target != box && event.target.parentNode != box){
        box.style.display = 'none';
    }
});

function scrollFunction() {
    document.getElementById("memSearchResults").style.display = "none";
}

window.onscroll = scrollFunction;
	function shareStatus(id){
	var ajax = ajaxObj("POST", "/v2/app/dashboard/status_system.php");
	ajax.onreadystatechange = function() {
		if(ajaxReturn(ajax) == true) {
			if(ajax.responseText == "share_ok"){
				Pleasure.handleToastrSettings( /* closeButton */ false, /*postion */ 'toast-bottom-left', /*sticky*/ false, /*type*/ 'info', /*closeOthers*/ true, /* title */ '', /* notification*/ 'Post successsfully shared!' );
			} else {
				alert(ajax.responseText);
			}
		}
	}
	ajax.send("action=share&id="+id);
}

function trustIP(ip){
	var ajax = ajaxObj("POST", "/v2/app/dashboard/blockit");
	ajax.onreadystatechange = function(){
		if (ajaxReturn(ajax) == true){
			if (ajax.responseText == "ipaddresschanged"){
				swal("IP Address Changed", "Your IP address has been successsfully changed!", "success");
				location.reload();
			}else {
				swal("Error", "An unexpected error has occurred! Please try your previous action again!", "error");
			}
		}
	}
	ajax.send("action=trustip&ip="+ip);
}
</script>
<body>

	<div class="nav-bar-container">

		<!-- BEGIN ICONS -->
		<div class="nav-menu">
			<div class="hamburger">
				<span class="patty"></span>
				<span class="patty"></span>
				<span class="patty"></span>
				<span class="patty"></span>
				<span class="patty"></span>
				<span class="patty"></span>
			</div>
			<!--.hamburger-->
		</div>
		<!--.nav-menu-->

		<div class="nav-search">
			<span class="search"></span>
		</div>
		<!--.nav-search-->

		<div class="nav-user">
			<div class="user">
				<?php echo $profile_pic ?>
          <div class="badge" id="noteindex"></div>
                <script src="/v2/app/dashboard/js/jquery-3.1.1.min.js"></script>
			<script>

							 function refresh(timestamp)
							 {
									 var notes = {'timestamp' : timestamp};

									 $.ajax(
											 {
													 type: 'GET',
													 url: '/v2/app/dashboard/badge.php',
													 data: notes,
													 success: function(data){
															 var obj = jQuery.parseJSON(data);
															 $('#noteindex').html(obj.notifications);

															 refresh(obj.timestamp);
													 }
											 }
									 );
							 }
							 $(function() {
									refresh();
									if( window.localStorage )
								  {
								    if( !localStorage.getItem( 'firstLoad' ) )
								    {
								      localStorage[ 'firstLoad' ] = true;
								      window.location.reload();
								    }
								    else
								      localStorage.removeItem( 'firstLoad' );
								  }
							});
        </script>
			</div>
			<!--.user-->
			<div class="cross">
				<span class="line"></span>
				<span class="line"></span>
			</div>
			<!--.cross-->
		</div>

		<!--.nav-user-->
		<!-- END OF ICONS -->

		<div class="nav-bar-border"></div>
		<!--.nav-bar-border-->

		<!-- BEGIN OVERLAY HELPERS -->
		<div class="overlay">
			<div class="starting-point">
				<span></span>
			</div>
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

		<div class="page-header full-content">
			<div class="row">
				<div class="col-sm-6">
					<h1>Dashboard <small>Activity Summary</small></h1>
				</div>
				<!--.col-->
				<div class="col-sm-6">
					<ol class="breadcrumb">
						<li><a href="#" class="active"><i class="ion-home"></i> Dashboard</a>
						</li>
					</ol>
				</div>
				<!--.col-->
			</div>
			<!--.row-->
		</div>
		<!--.page-header-->
		<div class="row">
			<div class="col-md-4">
				<div class="panel">
					<div class="panel-heading">
						<div class="panel-title">
							<h4>TOTAL SOCIAL BLOCKIT STATUS</h4>
						</div>
					</div>
					<div class="panel-body">
						<div class="text-center">
								<?php echo $account_status; ?>
						</div>
					</div>


				</div>
				<div class="card card-event card-clickable">
					<div class="card-body">
						<h4>Groups</h4>
						<p>My groups</p>
						<?php echo $mgList; ?>

						<hr>
						<p>All groups</p>
						<?php echo $agList; ?>
					</div><!--.card-body-->

					<div class="clickable-button">
						<div class="layer bg-red"></div>
						<a class="btn btn-floating btn-red initial-position floating-open"><i class="ion-android-add"></i></a>
					</div>
					<!--
					<div id="groupWrapper"><div id="groupList"><h2>My Groups</h2><hr /><?php echo $mgList; ?><h2>All Groups</h2><hr /><?php echo $agList; ?></div><div id="groupForm"><h2>Create New Group</h2><hr /><p>Group Name:<br /><input type="text" id="gname" onBlur="checkGname()" ><span id="gnamestatus"></span></p><p>How do people join your group?<br /><select name="invite" id="invite"><option value="null" selected>&nbsp;</option><option value="1">By requesting to join.</option><option value="2">By simply joining.</option></select></p><button id="newGroupBtn" onClick="createGroup()">Create Group</button><span id="status"></span></div></div><div class="clear"></div>

					-->

					<div class="layered-content bg-red">
						<div class="overflow-content">
							<h4>Create a new group</h4>
								<div class="inputer floating-label">
									<div class="input-wrapper">
										<input type="text" id="gname" class="form-control" id="new-group" onBlur="checkGname()">
										<label for="new-group">Group Name</label>
									</div>
								</div>
								<span id="gnamestatus"></span>
								<br />
								<div class="row example-row">
									<div class="col-md-12">
										<p>How do people join your group?</p>
										<select class="selecter" name="invite" id="invite">
											<option value="" ></option>
											<option value="1">By requesting to join.</option>
											<option value="2">By simply joining.</option>
										</select>
									</div><!--.col-md-9-->
								</div><!--.row-->
								<div class="row example-row">
									<div class="col-md-12">
										<button id="newGroupBtn" class="btn btn-default" onClick="createGroup()">Create Group</button>
										<span id="status"></span>
									</div><!--.col-md-9-->
								</div><!--.row-->
								<div>
							</div>
						</div><!--.overflow-content-->
						<div class="clickable-close-button">
							<a class="btn btn-floating initial-position floating-close"><i class="ion-android-close"></i></a>
						</div>
					</div>

				</div><!--.card-->
				<h4><strong>How likely are you going to recommend us?</strong></h4>
				<hr>
				<!-- Change the width and height values to suit you best -->
<div class="typeform-widget" data-url="https://totalsocial.typeform.com/to/DAFg8a" data-text="Net Promoter Score®" style="width:100%;height:500px;"></div>
<script>(function(){var qs,js,q,s,d=document,gi=d.getElementById,ce=d.createElement,gt=d.getElementsByTagName,id='typef_orm',b='https://s3-eu-west-1.amazonaws.com/share.typeform.com/';if(!gi.call(d,id)){js=ce.call(d,'script');js.id=id;js.src=b+'widget.js';q=gt.call(d,'script')[0];q.parentNode.insertBefore(js,q)}})()</script>
<div style="font-family: Sans-Serif;font-size: 12px;color: #999;opacity: 0.5; padding-top: 5px;">Powered by<a href="https://www.typeform.com/examples/?utm_campaign=DAFg8a&amp;utm_source=typeform.com-2278823-Basic&amp;utm_medium=typeform&amp;utm_content=typeform-embedded-poweredbytypeform&amp;utm_term=EN" style="color: #999" target="_blank">Typeform</a></div>
		<hr>
				<h4><strong>Why not tell us what you think?</strong></h4>
				<hr>
			<!-- Change the width and height values to suit you best -->
<div class="typeform-widget" data-url="https://totalsocial.typeform.com/to/bFI9Rx" data-text="Customer Satisfaction Survey" style="width:100%;height:500px;"></div>
<script>(function(){var qs,js,q,s,d=document,gi=d.getElementById,ce=d.createElement,gt=d.getElementsByTagName,id='typef_orm',b='https://s3-eu-west-1.amazonaws.com/share.typeform.com/';if(!gi.call(d,id)){js=ce.call(d,'script');js.id=id;js.src=b+'widget.js';q=gt.call(d,'script')[0];q.parentNode.insertBefore(js,q)}})()</script>
<div style="font-family: Sans-Serif;font-size: 12px;color: #999;opacity: 0.5; padding-top: 5px;">Powered by<a href="https://www.typeform.com/examples/surveys/?utm_campaign=bFI9Rx&amp;utm_source=typeform.com-2278823-Basic&amp;utm_medium=typeform&amp;utm_content=typeform-embedded-onlinesurvey&amp;utm_term=EN" style="color: #999" target="_blank">Typeform</a></div>
			</div>

			<?php echo $statuslist ?>
			<div class="col-md-4">
				<div class="card card-news card-green">
					<span class="label label-info">Promoted</span>
					<div class="card-heading">
						<a href="#" class="author"><?php echo $profile_pic ?></a>
						<h3 class="card-title">Promoted Post</h3><!--.card-title-->
					</div><!--.card-heading-->

					<div class="card-body">
						<p>This is just a sample of what the actual promoted post would look like.</p>
					</div><!--.card-body-->

					<div class="card-footer">
						<ul class="action">
							<li><a href="#"><i class="fa fa-comment"></i></a></li>
						</ul>
					</div><!--.card-footer-->

				</div><!--.card-->
			</div><!--.col-md-4-->

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
					<form>
						<div class="form-group">
							<input type="text" id="input-search" class="form-control" onKeyUp="getNames(this.value)" placeholder="Find a Member">
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
			<script src="https://maps.google.com/maps/api/js?sensor=true&amp;libraries=places"></script>
			<script src="/v2/app/dashboard/assets/globals/plugins/gmaps/gmaps.js"></script>
			<script src="/v2/app/dashboard/assets/globals/plugins/bxslider/jquery.bxslider.min.js"></script>
			<script src="/v2/app/dashboard/assets/globals/plugins/audiojs/audiojs/audio.min.js"></script>
			<script src="/v2/app/dashboard/assets/globals/plugins/d3/d3.min.js"></script>
			<script src="/v2/app/dashboard/assets/globals/plugins/rickshaw/rickshaw.min.js"></script>
			<script src="/v2/app/dashboard/assets/globals/plugins/jquery-knob/excanvas.js"></script>
			<script src="/v2/app/dashboard/assets/globals/plugins/jquery-knob/dist/jquery.knob.min.js"></script>
			<script src="/v2/app/dashboard/assets/globals/plugins/gauge/gauge.min.js"></script>
			<!-- END PLUGINS AREA -->

			<!-- PLEASURE -->
			<script src="/v2/app/dashboard/assets/globals/js/pleasure.js"></script>
			<!-- ADMIN 1 -->
			<script src="/v2/app/dashboard/assets/admin1/js/layout.js"></script>
			<script src="/v2/app/dashboard/assets/globals/scripts/forms-tools.js"></script>
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
