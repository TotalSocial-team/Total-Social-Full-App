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
	header( "location: account-access" );
	exit();
}
// Select the member from the users table
$sql = "SELECT * FROM users";
$user_query = mysqli_query( $db_conx, $sql );

// Check to see if the viewer is the account owner
$isOwner = "No";
if ( $u == $log_username && $user_ok == true ) {
	$isOwner = "Yes";
	$profile_pic_btn = '<a href="#" onclick="return false;" onmousedown="toggleElement(\'avatar_form\')">Toggle Avatar Form</a>';
	$avatar_form = '<form id="avatar_form" enctype="multipart/form-data" method="post" action="photo_system.php">';
	$avatar_form .= '<h4>Change your avatar</h4>';
	$avatar_form .= '<input type="file" name="avatar" required>';
	$avatar_form .= '<p><input type="submit" value="Upload"></p>';
	$avatar_form .= '</form>';
}

include( "notifications.php" );
include( "php_includes/template_PageTop_user.php" );


$table .= '<tr>
			<td><input type="checkbox" class="checkboxes" value="1"></td>
			<td>'.$username.'</td>
			<td>'.$email.'</td>
			<td>'.$verified.'</td>
			<td>'.$sex.'</td>			
			<td><button class="btn btn-primary btn-xs dt-edit"><span class="glyphicon glyphicon-pencil"></span></button></td>
		</tr>
		<tr>
			<td><input type="checkbox" class="checkboxes" value="1"></td>
			<td>'.$username.'</td>
			<td>'.$email.'</td>
			<td>'.$verified.'</td>
			<td>'.$sex.'</td>			
			<td><button class="btn btn-primary btn-xs dt-edit"><span class="glyphicon glyphicon-pencil"></span></button></td>
		</tr>';

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
?>
<?php
$friend_button = '<button type="button" class="btn btn-primary" disabled>Follow</button>';
$block_button = '<button type="button" class="btn btn-danger" disabled>Block User</button>';
// LOGIC FOR FRIEND BUTTON
if ( $isFriend == true ) {
	$friend_button = '<button class="btn btn-danger" onclick="friendToggle(\'unfriend\',\'' . $u . '\',\'friendBtn\')">Unfollow</button>';
} else if ( $user_ok == true && $u != $log_username && $ownerBlockViewer == false ) {
	$friend_button = '<button class="btn btn-primary" onclick="friendToggle(\'friend\',\'' . $u . '\',\'friendBtn\')">Follow</button>';
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
		if ( $friend_avatar != "" ) {
			$friend_pic = 'userdata/' . $friend_username . '/' . $friend_avatar . '';
		} else {
			$friend_pic = '';
		}
		$friendsHTML .= '<a href="user?u=' . $friend_username . '"><img class="friendpics" src="' . $friend_pic . '" alt="' . $friend_username . '" title="' . $friend_username . '"></a>';
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
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<title>Total Social Admin Panel</title>

	<meta name="description" content="">
	<meta name="author" content="">

	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

	<!-- BEGIN CORE CSS -->
	<link rel="stylesheet" href="../../assets/admin1/css/admin1.css">
	<link rel="stylesheet" href="../../assets/globals/css/elements.css">
	<!-- END CORE CSS -->

	<!-- BEGIN PLUGINS CSS -->
	<link rel="stylesheet" href="../../assets/globals/plugins/datatables/media/css/jquery.dataTables.min.css">
	<link rel="stylesheet" href="../../assets/globals/plugins/datatables/themes/bootstrap/dataTables.bootstrap.css">
	<link rel="stylesheet" href="../../assets/globals/css/plugins.css">
	<!-- END PLUGINS CSS -->

	<!-- BEGIN SHORTCUT AND TOUCH ICONS -->
	<link rel="shortcut icon" href="../../assets/globals/img/icons/favicon.ico">
	<link rel="apple-touch-icon" href="../../assets/globals/img/icons/apple-touch-icon.png">
	<!-- END SHORTCUT AND TOUCH ICONS -->

	<script src="../../assets/globals/plugins/modernizr/modernizr.min.js"></script>
	<script scrc="/v2/app/dashboard/js/main.js"></script>
	<script src="/v2/app/dashboard/js/ajax.js"></script>
	<script>
		function getNames(a){var b=new RegExp;b=/[^a-z0-9]/gi;var c=a.search(b)>=0;if(c&&(a=a.replace(b,""),document.getElementById("searchUsername").value=a),""==a)return document.getElementById("memSearchResults").style.display="none",!1;var d=new XMLHttpRequest;d.open("POST","/v2/app/dashboard/search_exec.php",!0),d.setRequestHeader("Content-type","application/x-www-form-urlencoded"),d.onreadystatechange=function(){if(4==d.readyState&&200==d.status){var a=d.responseText;""!=a&&(document.getElementById("memSearchResults").style.display="block",document.getElementById("memSearchResults").innerHTML=a)}},d.send("u="+a)}function scrollFunction(){document.getElementById("memSearchResults").style.display="none",document.getElementById("searchUsername").value=""}window.addEventListener("mouseup",function(a){var b=document.getElementById("memSearchResults");a.target!=b&&a.target.parentNode!=b&&(b.style.display="none",document.getElementById("searchUsername").value="")}),window.onscroll=scrollFunction;
        function badnames
	</script>
</head>
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
			</div><!--.hamburger-->
		</div><!--.nav-menu-->

		<div class="nav-search">
			<span class="search"></span>
		</div><!--.nav-search-->

		<div class="nav-user">
			<div class="user">
				<img src="user.png" alt="">
				<span class="badge"><?php echo $numrows ?></span>
			</div><!--.user-->
			<div class="cross">
				<span class="line"></span>
				<span class="line"></span>
			</div><!--.cross-->
		</div><!--.nav-user-->
		<!-- END OF ICONS -->

		<div class="nav-bar-border"></div><!--.nav-bar-border-->

		<!-- BEGIN OVERLAY HELPERS -->
		<div class="overlay">
			<div class="starting-point">
				<span></span>
			</div><!--.starting-point-->
			<div class="logo">Total Social</div><!--.logo-->
		</div><!--.overlay-->

		<div class="overlay-secondary"></div><!--.overlay-secondary-->
		<!-- END OF OVERLAY HELPERS -->

	</div><!--.nav-bar-container-->

	<div class="content">

		<div class="page-header full-content bg-blue-grey">
			<div class="row">
				<div class="col-sm-6">
					<h1>Total Social</h1>
				</div><!--.col-->
				<div class="col-sm-6">
					<ol class="breadcrumb">
						<li><a href="#"><i class="ion-home"></i></a></li>
						<li><a href="#">User Profile</a></li>
						<li><a href="#" class="active">Database Admin</a></li>
					</ol>
				</div><!--.col-->
			</div><!--.row-->
		</div><!--.page-header-->

		<div class="row">
			<div class="col-md-12">
				<div class="panel">
					<div class="panel-heading">
						<div class="panel-title"><h4>Total Social User Database</h4></div>
					</div><!--.panel-heading-->
					<div class="panel-body">

						<div class="overflow-table">
						<table class="display datatables-crud display datatables-serverside">
							<thead>
								<tr>
									<th>Username</th>
									<th>Email</th>
									<th>Gender</th>
									<th>Verified</th>
									<th>Approved</th>
									<th>Userlevel</th>
									<th>Activated</th>
								</tr>
							</thead>
						</table>
						</div><!--.overflow-table-->

					</div><!--.panel-body-->
				</div><!--.panel-->
			</div><!--.col-md-12-->
		</div><!--.row-->
		
		<div class="row">
			<div class="col-md-6">
				<div class="panel">
					<div class="panel-heading">
						<div class="panel-title"><h4>Edit a User</h4></div>
					</div><!--.panel-heading-->
					<div class="panel-body">
						
					</div><!--.panel-body-->
				</div><!--.panel-->
			</div><!--.col-md-12-->
			<div class="col-md-6">
				<div class="panel">
					<div class="panel-heading">
						<div class="panel-title"><h4>Suspend a User</h4></div>
					</div><!--.panel-heading-->
					<div class="panel-body">
						
					</div><!--.panel-body-->
				</div><!--.panel-->
			</div><!--.col-md-12-->
		</div><!--.row-->
		<div class="row">
			<div class="col-md-12">
				<div class="panel">
					<div class="panel-heading">
						<div class="panel-title"><h4>Total Social Status Updates Database</h4></div>
					</div><!--.panel-heading-->
					<div class="panel-body">

						<div class="overflow-table">
						<table class="display datatables-crud display datatables-statusside">
							<thead>
								<tr>
									<th>Account Name</th>
									<th>Author</th>
									<th>Data</th>
									<th>Type</th>
									<th>Post Date</th>
								</tr>
							</thead>
						</table>
						</div><!--.overflow-table-->

					</div><!--.panel-body-->
				</div><!--.panel-->
			</div><!--.col-md-12-->
		</div><!--.row-->
	</div><!--.content-->

	<div class="layer-container">

		<!-- BEGIN MENU LAYER -->
		<div class="menu-layer">
			<ul>
				<?php echo $loginLink ?>
			</ul>
		</div><!--.menu-layer-->
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
			</div><!--.search-->

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
		</div><!--.search-layer-->
		<!-- END OF SEARCH LAYER -->

		<?php include("php_includes/top.php"); ?>



	<!-- BEGIN GLOBAL AND THEME VENDORS -->
	<script src="../../assets/globals/js/global-vendors.js"></script>
	<!-- END GLOBAL AND THEME VENDORS -->

	<!-- BEGIN PLUGINS AREA -->
	<script src="../../assets/globals/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
	<script src="../../assets/globals/plugins/datatables/themes/bootstrap/dataTables.bootstrap.js"></script>
	<!-- END PLUGINS AREA -->

	<!-- PLUGINS INITIALIZATION AND SETTINGS -->
	<script src="../../assets/globals/scripts/tables-datatables-sources.js"></script>
	<!-- END PLUGINS INITIALIZATION AND SETTINGS -->

	<!-- PLEASURE -->
	<script src="../../assets/globals/js/pleasure.js"></script>
	<!-- ADMIN 1 -->
	<script src="../../assets/admin1/js/layout.js"></script>

	<!-- BEGIN INITIALIZATION-->
	<script>
	$(document).ready(function () {
		Pleasure.init();
		Layout.init();
		TablesDataTablesSources.init();
	});
	</script>
	<!-- END INITIALIZATION-->

	<!-- BEGIN Google Analytics -->
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', Pleasure.settings.ga.urchin, Pleasure.settings.ga.url);
		ga('send', 'pageview');
	</script>
	<!-- END Google Analytics -->

</body>
</html>