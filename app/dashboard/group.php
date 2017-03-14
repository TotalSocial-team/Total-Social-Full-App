<?php
include_once( "php_includes/check_login_status.php" );
if ( !isset( $_SESSION[ "username" ] ) ) {
	echo "You must be logged in to view this. Press back button.";
	exit();
}
// Initialize any variables that the page might echo
$g = "";
$gName = "";
$gCreation = "";
$gLogo = "";
$invRule = "";
$privRule = "";
$creator = "";
$gMembers = "";
$moderators = array();
$approved = array();
$pending = array();
$all = array();
$joinBtn = "";
$addMembers = "";
$addAdmin = "";
$profile_pic_btn = "";
$avatar_form = "";
$mainPosts = "";
// Make sure the _GET username is set, and sanitize it
if ( isset( $_GET[ "g" ] ) ) {
	$g = preg_replace( '#[^a-z0-9_]#i', '', $_GET[ 'g' ] );
}
// Select the group from the groups table
$query = mysqli_query( $db_conx, "SELECT * FROM groups WHERE name='$g' LIMIT 1" );
// Make sure that group exists and get group data
$numrows = mysqli_num_rows( $query );
if ( $numrows < 1 ) {
	echo "That group does not exist, press back";
	exit();
} else {
	// Get data about group
	while ( $row = mysqli_fetch_array( $query, MYSQLI_ASSOC ) ) {
		$gName = $row[ "name" ];
		$gCreation = $row[ "creation" ];
		$creationDate = strftime( "%b %d, %Y", strtotime( $gCreation ) );
		$gLogo = $row[ "logo" ];
		$invRule = $row[ "invrule" ];
		$creator = $row[ "creator" ];
	}
}
$profile_pic = '<img src="/v2/app/dashboard/groups/' . $g . '/' . $gLogo . '" alt="' . $g . '">';
// Set session for group
$_SESSION[ 'group' ] = $gName;
// Get Member data
$sql = 'SELECT g.mname, g.approved, g.admin, u.avatar
		FROM gmembers AS g
		LEFT JOIN users AS u ON u.username = g.mname
		WHERE g.gname = "' . $g . '"';
$query = mysqli_query( $db_conx, $sql );
while ( $row = mysqli_fetch_array( $query, MYSQLI_ASSOC ) ) {
	$mName = $row[ 'mname' ];
	$app = $row[ 'approved' ];
	$admin = $row[ 'admin' ];
	$avatar = $row[ 'avatar' ];

	// Set user image
	$member_pic = '/v2/app/dashboard/userdata/' . $mName . '/' . $avatar;
	$members .= '<div class="col-md-6">
								<div class="card tile card-friend"><img src="' . $member_pic . '" class="user-photo" alt="">
									<div class="friend-content">
										<p class="title">' . $mName . '</p>
										</p>
										<a class="btn btn-flat btn-primary btn-xs" href=/v2/app/dashboard/user/' . $mName . '>View Profile</a> </div>
									<!--.friend-content-->
								</div>
								<!--.card-->
							</div>';

	// Determine if approved
	switch ( $app ) {
		case 0:
			array_push( $pending, $mName );
			array_push( $all, $mName );
			break;
		case 1:
			array_push( $approved, $mName );
			array_push( $all, $mName );
			break;
	}

	// Determine if admin
	if ( $admin == 1 ) {
		array_push( $moderators, $mName );
	}
	// Get all counts
	$mod_count = count( $moderators );
	$app_count = count( $approved );
	$pend_count = count( $pending );

	// Output
	if ( $app == 1 ) {
		$gMembers .= '<a href="user.php?u=' . $mName . '"><img src="' . $member_pic . '" alt="' . $mName . '" title="' . $mName . '" width="70" height="70" ></a>';
	}
}
// Join group button
if ( ( isset( $_SESSION[ 'username' ] ) ) && ( !in_array( $_SESSION[ 'username' ], $all ) ) ) {
	$joinBtn = '<button class="btn btn-primary" id="joinBtn" onClick="joinGroup()">Join Group</button>';
}
// Pending members section for admin
if ( in_array( $_SESSION[ 'username' ], $moderators ) ) {
	for ( $x = 0; $x < $pend_count; $x++ ) {
		/* $addMembers .= '<a href="/v2/app/dashboard/user/' . $pending[ $x ] . '">' . $pending[ $x ] . '</a>';
		$addMembers .= '<button id="appBtn" onClick="approveMember(\'' . $pending[ $x ] . '\')">Approve</button>';
		$addMembers .= '<button id="appBtn" onClick="declineMember(\'' . $pending[ $x ] . '\')">Decline</button><br />'; */
		$addMembers .= '<div class="col-md-6">
								<div class="card tile card-friend">
									<div class="friend-content">
										<p class="title">' . $pending[$x] . '</p>
										</p>
										<a class="btn btn-flat btn-primary btn-xs" href=/v2/app/dashboard/user/'. $pending[ $x ] .'>View Profile</a> 
										<button id="appBtn" onClick="approveMember(\'' . $pending[ $x ] . '\')" class="btn btn-flat btn-success btn-xs" href=/v2/app/dashboard/user/'. $pending[ $x ] .'>Approve</button>									
										<button id="appBtn" onClick="declineMember(\'' . $pending[ $x ] . '\')" class="btn btn-flat btn-danger btn-xs" href=/v2/app/dashboard/user/'. $pending[ $x ] .'>Decline</button>		
										</div>
									<!--.friend-content-->
								</div>
								<!--.card-->
							</div>';
	}
	if ($pend_count < 1){
		$addMembers = 'No pending members';
	}
}
// Add admin
if ( in_array( $_SESSION[ 'username' ], $moderators ) ) {
	$addAdmin  = '<div class="inputer">
													<div class="input-wrapper">
														<input type="text" name="new_admin" id="new_admin"  class="form-control" placeholder=" Admin Username">
													</div>
												</div>';
	//$addAdmin .= '<button id="addAdm" onClick="addAdmin()">Add</button>';
	$addAdmin .= '<div class="row">
									<div class="pull-left">
										<button id="addAdm" onclick="addAdmin()" class="btn btn-default">Submit</button>
									</div>
								</div>
							</div>';
}
// Change logo for group creator only
if ( $_SESSION[ 'username' ] == $creator ) {
	$profile_pic_btn = '<a href="#" onclick="return false;" onmousedown="toggleElement(\'avatar_form\')">Toggle Avatar Form</a>';
	$avatar_form = '<form id="avatar_form" enctype="multipart/form-data" method="post" action="php_parsers/group_parser.php">';
	$avatar_form .= '<h4>Change logo</h4>';
	$avatar_form .= '<input type="file" name="avatar" required>';
	$avatar_form .= '<p><input type="submit" value="Upload"></p>';
	$avatar_form .= '</form>';
}
// Build posting mechanism
// Get all thread starting posts
$sql = 'SELECT g.*, u.avatar
		FROM grouppost AS g
		LEFT JOIN users AS u ON u.username = g.author
		WHERE g.gname = "' . $g . '" AND type="0" ORDER BY pdate DESC';
$query = mysqli_query( $db_conx, $sql );
$numrows = mysqli_num_rows( $query );
if ( $numrows > 0 ) {
	while ( $row = mysqli_fetch_array( $query, MYSQLI_ASSOC ) ) {
		$post_id = $row[ "id" ];
		$post_auth = $row[ "author" ];
		$post_type = $row[ "type" ];
		$post_data = $row[ "data" ];
		$post_date = $row[ "pdate" ];
		$post_avatar = $row[ "avatar" ];
		$avatar_pic = '/v2/app/dashboard/userdata/' . $post_auth . '/' . $post_avatar;
		$user_image = '<img src="' . $avatar_pic . '" alt="' . $post_auth . '" title="' . $post_auth . '" class="user-image">';
		/* // Build threads	  
		$mainPosts .= '<div id="pB_' . $post_id . '" class="postsWrapper">';
		$mainPosts .= '<div class="postsHead">';
		$mainPosts .= 'Posted by: ' . $post_auth . ' ---- ' . date( 'F d, Y - g:ia', strtotime( $post_date ) );
		$mainPosts .= '</div>';
		$mainPosts .= '<div class="postsBody">';
		$mainPosts .= '<div class="postsPic">';
		$mainPosts .= $user_image;
		$mainPosts .= '</div>';
		$mainPosts .= '<div class="postsWords">';
		$mainPosts .= $post_data;
		$mainPosts .= '</div>';
		$mainPosts .= '<div class="clear"></div>';
		$mainPosts .= '</div>'; */
		$mainPosts .= '<div id="pB_' . $post_id . '" class="col-md-4">
              <div class="card tile card-post">
                <div class="card-heading">' . $user_image . '
                  <p class="author"><a href="/v2/app/dashboard/user/' . $post_auth . '">' . $post_auth . '</a></p>
                  <p class="time">' .date('F d, Y - g:ia', strtotime($post_date)).'</p>
                </div>
                <!--.card-heading -->
                <div class="card-body">' . $post_data . '
                </div>
                <!--.card-body -->
                <div class="card-footer">
                  <ul class="card-comments" id="replies_' . $post_id . '">
                   ' . $groupReply . '
                  </ul>
				  	<div class="input-group">
                    <div class="inputer">
                      <div class="input-wrapper">
                        <textarea id="reply_post_' . $post_id . '" class="replytext form-control maxlength maxlength-textarea" maxlength="250" rows="1" placeholder="Write a comment here"></textarea>
                      </div>
                    </div>
                    <div class="input-group-btn">
                      <button id="reBtn" onClick="replyPost(\'' . $post_id . '\')" type="button" class="btn btn-default">Send</button>
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

		//Gather up the replies
		$groupReply = "";
		// Get replies and user images using inner loop
		$sql2 = 'SELECT g.author, g.data, g.pdate, u.avatar
				 FROM grouppost AS g
				 LEFT JOIN users AS u ON u.username = g.author
		         WHERE pid="' . $post_id . '"';
		$query2 = mysqli_query( $db_conx, $sql2 );
		$numrows2 = mysqli_num_rows( $query2 );
		if ( $numrows2 > 0 ) {
			while ( $row2 = mysqli_fetch_array( $query2, MYSQLI_ASSOC ) ) {
				$reply_auth = $row2[ "author" ];
				$reply_data = $row2[ "data" ];
				$reply_date = $row2[ "pdate" ];
				$reply_avatar = $row2[ "avatar" ];
				$re_avatar_pic = 'user/' . $reply_auth . '/' . $reply_avatar;
				$reply_image = '<img src="' . $re_avatar_pic . '" alt="' . $reply_auth . '" title="' . $reply_auth . '" class="user-photo" >';
				// Build replies
				/* $mainPosts .= '<div class="postsBody">';
				$mainPosts .= '<div class="postsPic">';
				$mainPosts .= $reply_image;
				$mainPosts .= '</div>';
				$mainPosts .= '<div class="postsWords">';
				$mainPosts .= $reply_auth . ' replied on ' . date( 'F d, Y - g:ia', strtotime( $reply_date ) ) . '<br /><br />';
				$mainPosts .= $reply_data;
				$mainPosts .= '</div>';
				$mainPosts .= '<div class="clear"></div>';
				$mainPosts .= '</div>'; */
				$groupReply = '<li id="reply_' . $post_id . '"><div class="user-photo"><a href="/v2/app/dashboard/user/' . $reply_auth . '">' . $reply_image . '</a></div><div class="comment">' . $reply_data . '</div></li>';
			}
		}
		// Time to build the Reply To section
		/* $mainPosts .= '<textarea id="reply_post_' . $post_id . '" class="repost" placeholder="Reply to this..."></textarea>';
		$mainPosts .= '<button id="reBtn" onClick="replyPost(\'' . $post_id . '\')">Post</button>'; */
	}
}
?>
<?php 
include("php_includes/template_PageTop_group.php");
?>
<!DOCTYPE html>
<!--[if  IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js">
<!--<![endif]-->

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>
		<?php echo $g ?> - Group Page</title>
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
	<script src="/v2/app/dashboard/js/sA/dist/sweetalert.min.js"></script>
	<link rel="stylesheet" type="text/css" href="/v2/app/dashboard/js/sA/dist/sweetalert.css">
	<!-- PLEASURE -->
	<!--<script src="/v2/app/dashboard/assets/globals/js/pleasure.js"></script>
		<!-- ADMIN 1 -->
	<!--<script src="/v2/app/dashboard/assets/admin1/js/layout.js"></script> -->
	<script>
		function joinGroup() {
			var ajax = ajaxObj( "POST", "/v2/app/dashboard/group_parser.php" );
			ajax.onreadystatechange = function () {
				if ( ajaxReturn( ajax ) == true ) {
					var datArray = ajax.responseText;
					if ( datArray == "pending_approval" ) {
						swal( "Pending Approval", "Your request is awaiting approval.", "info" );
					}

					if ( datArray == "refresh_now" ) {
						location.reload();
					}

				}
			}
			ajax.send( "action=join_group" );
		}

		function approveMember( u ) {
			var ajax = ajaxObj( "POST", "/v2/app/dashboard/group_parser.php" );
			ajax.onreadystatechange = function () {
				if ( ajaxReturn( ajax ) == true ) {
					var datArray = ajax.responseText;
					if ( datArray == "member_approved" ) {
						Pleasure.handleToastrSettings( /* closeButton */ false, /*postion */ 'toast-bottom-left', /*sticky*/ false, /*type*/ 'success', /*closeOthers*/ true, /* title */ 'Member Approved', /* notification*/ 'This member can type and comment on other posts!' );		
					}
				}
			}
			ajax.send( "action=approve_member&u=" + u );
		}

		function declineMember( u ) {
			var ajax = ajaxObj( "POST", "/v2/app/dashboard/group_parser.php" );
			ajax.onreadystatechange = function () {
				if ( ajaxReturn( ajax ) == true ) {
					var datArray = ajax.responseText;
					if ( datArray == "member_declined" ) {
						Pleasure.handleToastrSettings( /* closeButton */ false, /*postion */ 'toast-bottom-left', /*sticky*/ false, /*type*/ 'error', /*closeOthers*/ true, /* title */ 'Member Declined', /* notification*/ 'This member cannot comment or type other posts!' );			
					}
				}
			}
			ajax.send( "action=decline_member&u=" + u );
		}

		function quitGroup() {
			swal( {
					title: "Are you sure?",
					text: "Press okay if you really want to leave this group.",
					type: "warning",
					showCancelButton: true,
					confimButtonColor: "#DD6B55",
					confirmButtonText: "Leave Group",
					closeOnConfirm: true
				},
				function ( conf ) {
					if ( conf != true ) {
						return false;
					} else {
						var ajax = ajaxObj( "POST", "/v2/app/dashboard/group_parser.php" );
						ajax.onreadystatechange = function () {
							if ( ajaxReturn( ajax ) == true ) {
								if ( ajax.responseText == "was_removed" ) {
									swal( "you have been removed" );
									location.reload();
								}
							}
						}
						ajax.send( "action=quit_group" );
					}
				} );
		}

		function addAdmin() {
			var n = _( "new_admin" ).value;
			var ajax = ajaxObj( "POST", "/v2/app/dashboard/group_parser.php" );
			ajax.onreadystatechange = function () {
				if ( ajaxReturn( ajax ) == true ) {
					var datArray = ajax.responseText;
					if ( datArray == "admin_added" ) {
						Pleasure.handleToastrSettings( /* closeButton */ false, /*postion */ 'toast-bottom-left', /*sticky*/ false, /*type*/ 'success', /*closeOthers*/ true, /* title */ 'Admin Created!', /* notification*/ 'This member has now been added as an admin!' );
					}
					else if (datArray == "user_not_exist") {
						Pleasure.handleToastrSettings( /* closeButton */ false, /*postion */ 'toast-bottom-left', /*sticky*/ false, /*type*/ 'error', /*closeOthers*/ true, /* title */ 'Admin creation failed!', /* notification*/ 'This user does not exist or is not part of this group!' );

					}
				}
			}
			ajax.send( "action=add_admin&n=" + n );
		}

		function newPost() {
			var data = _( 'new_post' ).value;
			if ( data == "" ) {
				swal( "Looks like you haven't typed anything.", " You can't post nothing. Go ahead and type something :)", "error" );
				return false;
			}

			swal( {
					title: "Careful!",
					text: "Double check this post before letting this go out to the public. Once posted, you will not have the option to delete this. We are working on it! Are you sure you want to post this?",
					type: "warning",
					showCancelButton: true,
					confirmButtonColor: "#DD6B55",
					confirmButtonText: "Yes, I am sure!",
					closeOnConfirm: true
				},

				function ( conf ) {
					if ( conf != true ) {
						Pleasure.handleToastrSettings( /* closeButton */ false, /*postion */ 'toast-bottom-left', /*sticky*/ false, /*type*/ 'info', /*closeOthers*/ true, /* title */ '', /* notification*/ 'Operation Cancelled' );
						return false;
					} else {
						_( "postBtn" ).disabled = true;
						var ajax = ajaxObj( "POST", "/v2/app/dashboard/group_parser.php" );
						ajax.onreadystatechange = function () {
							if ( ajaxReturn( ajax ) == true ) {
								var datArray = ajax.responseText.split( "|" );
								if ( datArray[ 0 ] == "post_ok" ) {
									var sid = datArray[ 1 ];
									data = data.replace( /</g, "&lt;" ).replace( />/g, "&gt;" ).replace( /n/g, "<br />" ).replace( /r/g, "<br />" );
									var currentHTML = _( "post_list" ).innerHTML;
									_( "post_list" ).innerHTML = '<div id="pB_'+sid+ '" class="col-md-4"> <div class="card tile card-post"> <div class="card-heading"><?php echo $user_image ?><p class="author">Yourself</p> <p class="time">Just Now</p> </div> <div class="card-body">'  +data+ ' </div><div class="card-footer"> <ul class="card-comments" id="replies_' + sid + '"></ul><div class="input-group"> <div class="inputer"> <div class="input-wrapper"><textarea id="reply_post_' +sid+ '" class="replytext form-control maxlength maxlength-textarea" maxlength="250" rows="1" placeholder="Write a comment here"></textarea> </div> </div> <div class="input-group-btn"> <button id="reBtn" onClick="replyPost(\'' +sid+ '\')" type="button" class="btn btn-default">Send</button> </div></div></div> </div> </div>' + currentHTML;
									_( "postBtn" ).disabled = false;

									_( 'new_post' ).value = "";
								} else {
									alert( ajax.responseText );
								}
							}
						}
					}
					ajax.send( "action=new_post&data=" + data );
				} );
		}

		function replyPost( sid ) {
			var ta = "reply_post_" + sid;
			var data = _( ta ).value;
			if ( data == "" ) {
				swal( "Looks like you haven't typed anything.", " You can't post nothing. Go ahead and type something :)", "error" );
				return false;
			}
			swal( {
					title: "Careful!",
					text: "Double check this post before letting this go out to the public. Once posted, you will not have the option to delete this. We are working on it! Are you sure you want to post this?",
					type: "warning",
					showCancelButton: true,
					confirmButtonColor: "#DD6B55",
					confirmButtonText: "Yes, I am sure!",
					closeOnConfirm: true
				},

				function ( conf ) {
					if ( conf != true ) {
						Pleasure.handleToastrSettings( /* closeButton */ false, /*postion */ 'toast-bottom-left', /*sticky*/ false, /*type*/ 'info', /*closeOthers*/ true, /* title */ '', /* notification*/ 'Operation Cancelled' );
						return false;
					} else {
						var ajax = ajaxObj( "POST", "/v2/app/dashboard/group_parser.php" );
						ajax.onreadystatechange = function () {
							if ( ajaxReturn( ajax ) == true ) {
								var datArray = ajax.responseText.split( "|" );
								if ( datArray[ 0 ] == "reply_ok" ) {
									var rid = datArray[ 1 ];
									data = data.replace( /</g, "&lt;" ).replace( />/g, "&gt;" ).replace( /n/g, "<br />" ).replace( /r/g, "<br />" );
									_( "replies_" + rid ).innerHTML += '<li id="reply_' + rid + '" class="reply_boxes"><div class="user-photo"><?php echo $user_image ?></div><div class="comment">' + data + ' <br /> </div></li>';
									_( ta ).value = "";
								} else {
									alert( ajax.responseText );
								}
							}
						}
					}

					ajax.send( "action=post_reply&sid=" + sid + "&data=" + data );
				} );
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
	// http://www.developphp.com/view.php?tid=1185
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
		document.getElementById("searchUsername").value = "";
    }
});

function scrollFunction() {
    document.getElementById("memSearchResults").style.display = "none";
    document.getElementById("searchUsername").value = "";
}

window.onscroll = scrollFunction;
	</script>
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
					<?php echo $profile_pic ?>
				</div>
				<!--.profile-photo-->
				<div class="profile-text light">
					<?php echo $g ?>
					<div class="pull-right">
						<?php echo $joinBtn ?>
						 <?php if (in_array($_SESSION['username'],$approved)){ ?>
  						  <button id="quitBtn" class="btn btn-danger" onClick="quitGroup()">Quit Group</button>
  						<?php } ?>
					</div>

					<span class="caption">
					This group was created by: <?php echo $creator ?>
					</span>
				



				</div>
				<!--.profile-text-->
			</div>
			<!--.profile-info-->

			<div class="row">
				<div class="col-sm-6">
					<h1>Group page of <small><?php echo $g ?></small></h1>
				</div>
				<!--.col-->
				<div class="col-sm-6">
					<ol class="breadcrumb">
						<li><a href="#"><i class="ion-home"></i></a>
						</li>
						<li><a href="#" class="active">Groups</a>
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
					<li><a href="#members" data-toggle="tab" class="btn-ripple">Members</a>
					</li>
					<?php if (in_array($_SESSION['username'],$moderators)){ ?>
					<li><a href="#admincontrols" data-toggle="tab" class="btn-ripple">Admin Controls</a>
					</li>
					<?php } ?>

				</ul>
			</div>
		</div>
		<!--.page-header-->

		<div class="row user-profile">
			<div class="col-md-12">
				<div class="tab-content without-border">
					<div id="timeline" class="tab-pane active">
						<?php if (in_array($_SESSION['username'],$approved)){ ?>
						<div class="row">
							<div class="col-md-12">
								<div class="panel">
									<div class="panel-heading">
										<div class="panel-title">
											<h4>NEW POST</h4>
										</div>
									</div>
									<!--.panel-heading-->
									<div class="panel-body">
										<div class="form-content">
											<div class="inputer">
												<div class="input-wrapper">
													<textarea id="new_post" class="form-control maxlength maxlength-textarea" maxlength="250" rows="4" placeholder="Post something to this group,  <?php echo $g ?>."></textarea>
												</div>
											</div>
										</div>
										<div class="form-buttons clearfix">

											<div class="pull-right">
												<button type="button" id="postBtn" onclick="newPost()" class="btn btn-default ">Post to group <i class="ion-android-arrow-forward"></i></button>
											</div>
										</div>
									</div>
									<!--.panel-body-->
								</div>
								<!--.panel-->

							</div>
							<!--.col-md-12-->
						</div>
						<?php } ?>
						<div id="post_list">
							<?php echo $mainPosts ?>
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
								</ul>
							</div>
							<!--.col-md-3-->
							<div class="col-md-9">
								<div class="tab-content">
									<div class="tab-pane active" id="about_overview">

										<div class="legend">About</div>
										<div class="row">
											<div class="col-md-3">Established: </div>
											<!--.col-md-3-->
											<div class="col-md-9">
												<?php echo $creationDate ?>
											</div>
											<!--.col-md-9-->
										</div>
										<!--.row-->
										<div class="row">
											<div class="col-md-3">Members:</div>
											<!--.col-md-3-->
											<div class="col-md-9">
												<?php echo $app_count ?>
											</div>
											<!--.col-md-9-->
										</div>
										<!--.row-->
										<div class="row">
											<div class="col-md-3">Admins / Moderators: </div>
											<!--.col-md-3-->
											<div class="col-md-9">
												<?php echo $mod_count ?>
											</div>
											<!--.col-md-9-->
										</div>
										<!--.row-->
										<div class="row">
											<div class="col-md-3">Members Pending Approval:</div>
											<!-- .col-md-3 -->
											<div class="col-md-9">
												<?php echo $pend_count ?>
											</div>
											<!--.col-md-9-->
										</div>
									</div>
									<!--#about_overview.tab-pane-->


								</div>
								<!--.tab-content-->

							</div>
							<!--.col-md-9-->
						</div>
						<!--.row-->
					</div>
					<!--#about.tab-pane-->

					<div id="members" class="tab-pane">
						<div class="row">
							<?php echo $members ?>
						</div>



					</div>
					<!--#friends.tab-pane-->
					<div id="admincontrols" class="tab-pane">
						<div class="row">
							<div class="col-md-12">
								<div class="panel">
									<div class="panel-heading">
										<div class="panel-title">
											<h4>ADD AN ADMIN</h4>
										</div>
									</div>
									<!--.panel-heading-->
									<div class="panel-body">

										<div class="row example-row">
											<div class="col-md-3">Admin Username: </div>
											<!--.col-md-3-->
											<div class="col-md-9">
												<?php echo $addAdmin ?>
											</div>
											
											
										

									</div>
									<!--.panel-body-->
								</div>
								<!--.panel-->
							</div>
							<!--.col-md-12-->
						</div>
						<!--.row-->
						<div class="row">
							<div class="col-md-12">
								<div class="panel">
									<div class="panel-heading">
										<div class="panel-title">
											<h4>APPROVE PENDING MEMBERS</h4>
										</div>
									</div>
									<!--.panel-heading-->
									<div class="panel-body">

										<div class="row example-row">
											<div class="col-md-3">Pending Members: </div>
											<!--.col-md-3-->
											<div class="col-md-9">
											<?php echo $addMembers ?>
											
											
										

									</div>
									<!--.panel-body-->
								</div>
								<!--.panel-->
							</div>
							<!--.col-md-12-->
						</div>
						<!--.row-->


					</div>

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
				<form>
					<div class="form-group">
						<input type="text" id="input-search" class="form-control" onKeyUp="getNames(this.value)" placeholder="Search a Member">
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
		<script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places"></script>
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