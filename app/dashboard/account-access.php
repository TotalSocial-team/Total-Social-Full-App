<?php
// registration script begins here
session_start();
// If user is logged in, header them away
if ( isset( $_SESSION[ "username" ] ) ) {
   header( "location: /v2/app/dashboard/index/" . $_SESSION[ "username" ] );
	exit();
}
?>
<?php
// Ajax calls this NAME CHECK code to execute
if ( isset( $_POST[ "usernamecheck" ] ) ) {
	include_once( "php_includes/db_conx.php" );
	$username = preg_replace( '#[^a-z0-9]#i', '', $_POST[ 'usernamecheck' ] );
	$sql = "SELECT id FROM users WHERE username='$username' LIMIT 1";
	$query = mysqli_query( $db_conx, $sql );
	$uname_check = mysqli_num_rows( $query );
	if ( strlen( $username ) < 3 || strlen( $username ) > 16 ) {
		echo '<p style="color:#F44336;">3 - 16 characters please</p>';
		exit();
	}
	if ( is_numeric( $username[ 0 ] ) ) {
		echo '<p style="color:#F44336;">Usernames must begin with a letter</p>';
		exit();
	}
	if ( $uname_check < 1 ) {
		echo '<p style="color:#4CAF50;">' . $username . ' is available</p>';
		exit();
	} else {
		echo '<p style="color:#F44336;">' . $username . ' is taken</p>';
		exit();
	}
}
?>
<?php
//Ajax calls this code to check the email
if (isset($_POST["emailcheck"])) {
	include_once("php_includes/db_conx.php");
	$email = $_POST["emailcheck"];
	$sql = "SELECT id FROM users WHERE email='$email' LIMIT 1";
	$query = mysqli_query($db_conx, $sql);
	$email_check = mysqli_num_rows($query);
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			echo '<p style="color:#F44336;">' . $email . ' is not valid!</p>';
			exit();
		}
		if (filter_var($email, FILTER_VALIDATE_EMAIL) && $email_check < 1){
			echo '<p style="color:#4CAF50;">This email is valid and available!</p>';
			exit();
		}else{
			echo '<p style="color:#F44336;">' . $email . ' is taken</p>';
			exit();
		}
}
?>
<?php
// Ajax calls this REGISTRATION code to execute
if ( isset( $_POST[ "u" ] ) ) {
	// CONNECT TO THE DATABASE
	include_once( "php_includes/db_conx.php" );
	// GATHER THE POSTED DATA INTO LOCAL VARIABLES
	$u = preg_replace( '#[^a-z0-9]#i', '', $_POST[ 'u' ] );
	$e = mysqli_real_escape_string( $db_conx, $_POST[ 'e' ] );
	$p = $_POST[ 'p' ];
	$g = preg_replace( '#[^a-z]#', '', $_POST[ 'g' ] );
	$c = preg_replace( '#[^a-z ]#i', '', $_POST[ 'c' ] );
	// GET USER IP ADDRESS
	$ip = preg_replace( '#[^0-9.]#', '', getenv( 'REMOTE_ADDR' ) );
	// DUPLICATE DATA CHECKS FOR USERNAME AND EMAIL
	$sql = "SELECT id FROM users WHERE username='$u' LIMIT 1";
	$query = mysqli_query( $db_conx, $sql );
	$u_check = mysqli_num_rows( $query );
	/*********/
	$sql = "SELECT id FROM users WHERE email='$e' LIMIT 1";
	$query = mysqli_query( $db_conx, $sql );
	$e_check = mysqli_num_rows( $query );
	// FORM DATA ERROR HANDLING
	if ( $u == "" || $e == "" || $p == "" || $g == "" || $c == "" ) {
		echo "The form submission is missing values.";
		exit();
	} else if ( $u_check > 0 ) {
		echo "The username you entered is alreay taken";
		exit();
	}else if ($e_check > 0) {
		echo "The email you entered is alreay taken";
		exit();
	}else if ( strlen( $u ) < 3 || strlen( $u ) > 16 ) {
		echo "Username must be between 3 and 16 characters";
		exit();
	} else if ( is_numeric( $u[ 0 ] ) ) {
		echo 'Username cannot begin with a number';
		exit();
	} else {
		// END FORM DATA ERROR HANDLING
		// Begin Insertion of data into the database
		// Hash the password and apply your own mysterious unique salt
		$p_hash = md5( $p );
		// Add user info into the database table for the main site table
		$sql = "INSERT INTO users (username, email, password, gender, country, ip, signup, lastlogin, notescheck, avatar)
		        VALUES('$u','$e','$p_hash','$g','$c','$ip',now(),now(),now(), 'user.png')";
		$query = mysqli_query( $db_conx, $sql );
		$uid = mysqli_insert_id( $db_conx );
		// Establish their row in the useroptions table
		$sql = "INSERT INTO useroptions (id, username, background) VALUES ('$uid','$u','original')";
		$query = mysqli_query( $db_conx, $sql );
		// Create directory(folder) to hold each user's files(pics, MP3s, etc.)
		if ( !file_exists( "userdata/$u" ) ) {
			mkdir( "userdata/$u", 0755 );
		}
		$avatar = "user.png";
		$avatar2 = "userdata/$u/user.png";
		if ( !copy( $avatar, $avatar2 ) ) {
			echo "failed to copy!";
		}
		// Email the user their activation link
		$to = "$e";
		$from = "Total Social Account Services <accounts@totalsocial.ca>";
		$headers ="From: $from\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1 \n";
		$subject = 'Total Social Account Activation';
		$message = '<h2>Hello '.$u.',</h2><p>This is an automated message from Total Social.
    If you did not recently signup for Total Social,
    please disregard this email.</p><p>Click the link below to activate your account when ready:</p><br />
    <a href="https://www.totalsocial.ca/v2/app/dashboard/activation.php?id=' . $uid . '&u=' . $u . '&e=' . $e . '&p=' . $p_hash . '">Click here to activate your account now</a><br /><br />Login after successful activation using your:<br />* E-mail Address: <b>' . $e . '</b>';
		mail( $to, $subject, $message, $headers );
		echo "signup_success";
		exit();
	}
	exit();
}
?>

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

	<title>Account Access - Total Social</title>

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
	<link rel="stylesheet" href="../../assets/globals/plugins/bootstrap-social/bootstrap-social.css">
	<link rel="stylesheet" href="../../assets/globals/plugins/indicator/indicator.css">
	<!-- END PLUGINS CSS -->

	<!-- FIX PLUGINS -->
	<link rel="stylesheet" href="../../assets/globals/css/plugins.css">
	<!-- END FIX PLUGINS -->

	<!-- BEGIN SHORTCUT AND TOUCH ICONS -->
	<link rel="shortcut icon" href="../../assets/globals/img/icons/favicon.ico">
	<link rel="apple-touch-icon" href="../../assets/globals/img/icons/apple-touch-icon.png">
	<!-- END SHORTCUT AND TOUCH ICONS -->

	<script src="../../assets/globals/plugins/modernizr/modernizr.min.js"></script>
	<script src="js/main.js"></script>
	<script src="js/ajax.js"></script>
    <script src="js/swords.js"></script>
	<script>
function restrict( elem ) {
			var tf = _( elem );
			var rx = new RegExp;
			if ( elem == "username" ) {
				rx = /[^a-z0-9]/gi;
			}
			tf.value = tf.value.replace( rx, "" );
		}

		function emptyElement( x ) {
			_( x ).innerHTML = "";
		}

		function checkusername() {
			var u = _( "username" ).value;
			if ( u != "" ) {
				_( "unamestatus" ).innerHTML = 'checking ...';
				var ajax = ajaxObj( "POST", "account-access" );
				ajax.onreadystatechange = function () {
					if ( ajaxReturn( ajax ) == true ) {
						_( "unamestatus" ).innerHTML = ajax.responseText;
                        compare = u.toLowerCase();
            for (var i = 0; i < badwordarray.length; i++){
            if (compare.indexOf(badwordarray[i]) > -1){
                _( "unamestatus" ).innerHTML = "<p style='color:#F44336;'>Your username contains bad words.</p>";

            }
           }
					}
				}
				ajax.send( "usernamecheck=" + u );
			}
		}

        function swearwords(){
            var u = _("username").value;

        }

		function checkemail(){
			var e = _("email").value;
			if (e != ""){
				_("estatus").innerHTML = 'checking';
				var ajax = ajaxObj("POST", "account-access");
				ajax.onreadystatechange = function() {
					if (ajaxReturn(ajax) == true) {
						if (ajax.responseText != '<p style="color:#4CAF50;">This email is valid and available!</p>'){
							_( "signupbtn" ).style.display = "none";
							_("estatus").innerHTML = ajax.responseText;
						}
						else {
							_("estatus").innerHTML = ajax.responseText;
							_( "signupbtn" ).style.display = "block";
						}
					}
				}
				ajax.send("emailcheck=" + e);
			}
		}

		function signup() {
			var u = _( "username" ).value;
			var e = _( "email" ).value;
			var p1 = _( "password1" ).value;
			var p2 = _( "password2" ).value;
			var c = _( "country" ).value;
			var g = _( "gender" ).value;
			var status = _( "status" );
			if ( u == "" || e == "" || p1 == "" || p2 == "" || c == "" || g == "" ) {
				status.innerHTML = "<p style='color:#F44336;'>Fill out all of the form data</p>";
			} else if ( p1 != p2 ) {
				status.innerHTML = "<p style='color:#F44336;'>Passwords don't match!</p>";
			} else {
				_( "signupbtn" ).style.display = "none";
				status.innerHTML = 'please wait ...';
				var ajax = ajaxObj( "POST", "account-access" );
				ajax.onreadystatechange = function () {
					if ( ajaxReturn( ajax ) == true ) {
						if ( ajax.responseText != "signup_success" ) {
							status.innerHTML = ajax.responseText;
							_( "signupbtn" ).style.display = "block";
						} else {
							window.scrollTo( 0, 0 );
							_( "signupform" ).innerHTML = "OK " + u + ", check your email inbox and junk mail box at <u>" + e + "</u> in a moment to complete the sign up process by activating your account. You will not be able to do anything on the site until you successfully activate your account.";
						}
					}
				}
				ajax.send( "u=" + u + "&e=" + e + "&p=" + p1 + "&c=" + c + "&g=" + g );
			}
		}
		/* function addEvents(){
			_("elemID").addEventListener("click", func, false);
		}
		window.onload = addEvents; */
		function emptyElement(x){
		  _(x).innerHTML = "";
		}

		function login(){
		  var e = _("email_login").value;
		  var p = _("password").value;
		  if(e == "" || p == ""){
			_("status_login").innerHTML = "Fill out all of the form data";
		  } else {
			_("loginbtn").style.display = "none";
			_("status").innerHTML = 'please wait ...';
			var ajax = ajaxObj("POST", "login");
				ajax.onreadystatechange = function() {
				  if(ajaxReturn(ajax) == true) {
					  if(ajax.responseText == "login_failed"){
				  _("status_login").innerHTML = "Login unsuccessful, please try again.";
				  _("loginbtn").style.display = "block";
				} else if(ajax.responseText == "exceed"){
          _("status_login").innerHTML = "loginblocked!";
				  _("loginbtn").style.display = "block";
        }else {
				  window.location = "/app/dashboard/index/"+ajax.responseText;
				}
				  }
				}
				ajax.send("e="+e+"&p="+p);
		  }
		}

		function forgotpass() {
			var a = _( "email_forgotpassword" ) . value;
			if ( "" == a )_( "status_pass" ) . innerHTML = "Type in your email address";
			else {
				_( "forgotpassbtn" ) . style . display = "none", _( "status_pass" ) . innerHTML = "please wait ...";
				var b = ajaxObj( "POST", "/v2/app/dashboard/password" );
				b . onreadystatechange = function () {
					if ( 1 == ajaxReturn( b ) ) {
						var a = b . responseText;
						"success" == a ? ( _( "forgotpassform" ) . innerHTML = "<strong>Check your inbox in a few minutes.</strong><br /> You will be receiving an email to reset your password. Just be patient.", _( "status_pass" ) . style . display = "none" ) : "no_exist" == a ? ( _( "status_pass" ) . innerHTML = "This email is invalid!", _( "forgotpassbtn" ) . style . display = "block" ) : "email_send_failed" == a ? ( _( "status_pass" ) . innerHTML = "Mail function failed to execute", _( "forgotpassbtn" ) . style . display = "block" ) : ( _( "status_pass" ) . innerHTML = "An unknown error has occured", _( "forgotpassbtn" ) . style . display = "block" )
					}
				}, b . send( "efp=" + a )
			}
		}
	</script>
</head>

<body class="bg-login printable">

	<div class="login-screen">
		<div class="panel-login blur-content">
			<div class="panel-heading"><img src="../../assets/globals/img/totalsocial.png" height="100" alt="">
			</div>
			<!--.panel-heading-->
			<div id="pane-login" class="panel-body active">
				<h2>Login onto Total Social</h2>
			<form name="loginform" id="loginform" onsubmit="return false;" role="form">
				<div class="form-group">
					<div class="inputer">
						<div class="input-wrapper">
							<input type="email"  id="email_login" name="email_login" class="form-control" placeholder="Enter your email" maxlength="88" required>
						</div>
					</div>
				</div>
				<!--.form-group-->
				<div class="form-group">
					<div class="inputer">
						<div class="input-wrapper">
							<input id="password" name="password" type="password" class="form-control" placeholder="Enter your password">
						</div>
					</div>
				</div>
				<!--.form-group-->
				<div class="form-buttons clearfix">
					<button type="submit" id="loginbtn" onclick="login()" class="btn btn-success pull-right">Login</button>
				</div>
				<!--.form-buttons-->
				<span id="status_login"></span>
			</form>
				<ul class="extra-links">
					<li><a href="#" class="show-pane-forgot-password">Forgot your password</a>
					</li>
					<li><a href="#" class="show-pane-create-account">Create a new account</a>
					</li>
				</ul>
			</div>
			<!--#login.panel-body-->

			<div id="pane-forgot-password" class="panel-body">
				<h2>Forgot Your Password</h2>
				<form id="forgotpassform" onsubmit="return false;" role="form">
				<div class="form-group">
					<div class="inputer">
						<div class="input-wrapper">
							<input id="email_forgotpassword" type="text" onfocus="_('status_pass').innerHTML='';" maxlength="88" class="form-control" placeholder="Enter your email address" required>
						</div>
					</div>
				</div>
				<!--.form-group-->
				<div class="form-buttons clearfix">
					<button type="submit" class="btn btn-white pull-left show-pane-login">Cancel</button>
					<button type="submit" class="btn btn-success pull-right" id="forgotpassbtn" onclick="forgotpass()">Send</button>
				</div>
				<!--.form-buttons-->
				</form>
				<div class="form-group">
					<span id="status_pass"></span>
				</div>


			</div>
			<!--#pane-forgot-password.panel-body-->

			<div id="pane-create-account" class="panel-body">
				<h2>Create a New Account</h2>
			<form name="signupform" id="signupform" onsubmit="return false;" role ="form">
				<div class="form-group">
					<div class="inputer">
						<div class="input-wrapper">
							<input type="text" id="username" name="username"  class="form-control" onblur="checkusername(), swearwords()" onkeyup="restrict('username')" maxlength="16" placeholder="Enter your desired username" required>
						</div>
						<span id="unamestatus"></span>
					</div>
				</div>
				<!--.form-group-->
				<div class="form-group">
					<div class="inputer">
						<div class="input-wrapper">
							<input type="text" id="email" name="email" class="form-control" onblur="checkemail()"  maxlength="88" placeholder="Enter your email address" required>
						</div>
						<span id="estatus"></span>
					</div>
				</div>
				<!--.form-group-->
				<div class="form-group">
					<div class="inputer">
						<div class="input-wrapper">
							<input id="password1" name="password1" type="password" class="form-control password-strength2" placeholder="Enter your password" onfocus="emptyElement('status')" maxlength="16" required>
						</div>
					</div>
					<div class="pwstrength_viewport_verdict"></div>

				</div>
				<!--.form-group-->
				<div class="form-group">
					<div class="inputer">
						<div class="input-wrapper">
							<input id="password2" name="password2" type="password" class="form-control" placeholder="Enter your password again" maxlegth="16" onFocus="emptyElement('status')">
						</div>
					</div>
				</div>
				<!--.form-group-->
				<div class="form-group">
					<div class="inputer">
						<div class="input-wrapper">
							<select data-live-search="true" id="gender" name="gender" onfocus="emptyElement('status')" class="form-control" required>
								<option value="">Select gender</option>
								<option value ="m">Male</option>
								<option value ="f">Female</option>
							</select>
						</div>
					</div>
				</div>
				<!--.form-group-->
				<div class="form-group">
					<div class="inputer">
						<div class="input-wrapper">
							<select id="country" name="country" onfocus="emptyElement('status')" class="form-control" required>
								<?php include_once("php_includes/template_country_list.php"); ?>
							</select>
						</div>
					</div>
				</div>
				<div class="form-buttons clearfix">
					<button type="submit" class="btn btn-white pull-left show-pane-login">Cancel</button>
					<button type="submit" id="signupbtn" onclick="signup()" class="btn btn-success pull-right">Sign Up</button>
				</div>
				<!--.form-buttons-->
				<br />
				<div class="form-group">
					<p>By signing up to our site, you agree to our <a href="https://www.totalsocial.ca/terms" target="_blank">terms of use</a>.</p>
				</div>
				</form>
				<div class="form-group">
					<span id="status"></span>
				</div>
				<!-- .form-group -->
			</div>
			<!--#login.panel-body-->

		</div>
		<!--.blur-content-->
	</div>
	<!--.login-screen-->

	<div class="bg-blur dark">
		<div class="overlay"></div>
	</div>
	<svg version="1.1" xmlns='http://www.w3.org/2000/svg'>
		<filter id='blur'>
			<feGaussianBlur stdDeviation='7'/>
		</filter>
	</svg>


	<!-- BEGIN GLOBAL AND THEME VENDORS -->
	<script src="../../assets/globals/js/global-vendors.js"></script>
	<!-- END GLOBAL AND THEME VENDORS -->

	<!-- BEGIN PLUGINS AREA -->
	<script src="../../assets/globals/plugins/handlebars/handlebars.min.js"></script>

	<script src="../../assets/globals/plugins/strength/strength.min.js"></script>
	<script src="../../assets/globals/plugins/indicator/indicator.js"></script>
	<script src="../../assets/globals/plugins/pwstrength-bootstrap/dist/pwstrength-bootstrap-1.2.2.min.js"></script>
	<script src="../../assets/globals/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
	<script src="../../assets/globals/plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
	<script src="../../assets/globals/plugins/jquery.inputmask/dist/jquery.inputmask.bundle.js"></script>
	<script src="../../assets/globals/plugins/ipmask/jquery.input-ip-address-control.min.js"></script>

	<script src="../../assets/globals/plugins/jquery.payment/lib/jquery.payment.js"></script>

	<script src="https://www.google.com/recaptcha/api.js"></script>

	<script src="../../assets/globals/plugins/typehead.js/dist/typeahead.bundle.min.js"></script>
	<script src="../../assets/globals/plugins/overlay/jquery.overlay.js"></script>
	<script src="../../assets/globals/plugins/jquery-textcomplete/dist/jquery.textcomplete.min.js"></script>
	<script src="../../assets/globals/plugins/emojify.js/emoji-list.js"></script>

	<script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places"></script>
	<script src="../../assets/globals/plugins/ubilabs-geocomplete/jquery.geocomplete.min.js"></script>
	<!-- END PLUGINS AREA -->

	<!-- PLUGINS INITIALIZATION AND SETTINGS -->
	<script src="../../assets/globals/scripts/user-pages.js"></script>
	<script src="../../assets/globals/scripts/forms-tools.js"></script>
	<!-- END PLUGINS INITIALIZATION AND SETTINGS -->

	<!-- PLEASURE Initializer -->
	<script src="../../assets/globals/js/pleasure.js"></script>
	<!-- ADMIN 1 Layout Functions -->
	<script src="../../assets/admin1/js/layout.js"></script>

	<!-- BEGIN INITIALIZATION-->
	<script>
		$( document ).ready( function () {
			Pleasure.init();
			Layout.init();
			UserPages.login();
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
