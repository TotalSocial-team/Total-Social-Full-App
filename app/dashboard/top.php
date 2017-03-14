<?php
//session info
include_once( "php_includes/check_login_status.php" );
?>
<?php
//user echo and current page
$thisPage = basename($_SERVER['PHP_SELF']);

$result = mysqli_query( $db_conx, "SELECT * FROM users WHERE username='$log_username'" );
while ( $row = mysqli_fetch_array( $result ) ) {
	$profile_id = $row[ "id" ];
	$log_email = $row[ 'email' ];
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
	if ( $userlevel == "a" ) {
		$uLevel = "Basic User";
	} else if ( $userlevel == "b" ) {
		$uLevel = "VIP";
	} else if ( $userlevel == "c" ) {
		$uLevel = "Admin/Compliance";
	} else if ( $userlevel == "d" ) {
		$uLevel = "Admin with Full Access";
	}
}
?>
<?php
//checking the email address
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
	if ($log_email == $email){
		echo '<p style="color:#4CAF50;">Your email will stay the same.</p>';
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
//Ajax calls this code to execute on edit account_info
if (isset($_POST['e'])){
	//Connect to database
	include_once("php_includes/db_conx.php");
	//gather the posted data into local variables
	$e = mysqli_real_escape_string($db_conx, $_POST['e']);
	$g = preg_replace( '#[^a-z0-9]#i', '', $_POST[ 'g' ] );
	$c = preg_replace( '#[^a-z ]#i', '', $_POST[ 'c' ] );
	$uselect = "SELECT id FROM users WHERE email='$e'AND username='$log_username' LIMIT 1";
	$query = mysqli_query( $db_conx, $uselect );
	$e_check = mysqli_num_rows( $query );

	//Form error handling
	if ($e == "" || $g == ""|| $c == "" || $p = ""){
		echo "missing_values";
		exit();
	}else {
		$update = "UPDATE users SET gender='$g', country='$c', email= '$e' WHERE id='$profile_id'";
		$query = mysqli_query( $db_conx, $update );

		echo "account_updated";
		exit();
	}
	exit();
}
?>
<?php
//Ajax calls this code to execute on password edit
 if ( isset( $_POST[ 'p' ] ) ) {
	//Connect to database
	include_once( "php_includes/db_conx.php" );
	//gather the posted data into local variables
	$p = $_POST['p'];
	$p_hash = md5( $p );
	$passupdate = "UPDATE users SET password='$p_hash' WHERE id='$profile_id'";
	$query = mysqli_query( $db_conx, $passupdate );
	$_SESSION["password"] = $p_hash;
	setcookie( "pass", $p_hash, strtotime( '+ 30 days' ), "/", "", "", TRUE );
	echo "password_update";
	//exit();
}
?>
<!-- BEGIN USER LAYER -->
<script sr="/v2/app/dashboard/js/main.js"></script>
<script sr="/v2/app/dashboard/js/ajax.js"></script>
<script type="text/javascript" src="https://code.jquery.com/jquery.min.js"></script>

<script type="text/javascript">
	 $( document ).ready(function() {
		checkEmail();
		//checkusername();
	});
	function restrict( elem ) {
			var tf = _( elem );
			var rx = new RegExp;
			if ( elem == "username" ) {
				rx = /[^a-z0-9]/gi;
			}
			tf.value = tf.value.replace( rx, "" );
	}
	function emptyElement(x){
		_(x).innerHTML="";
	}
	function enableButton(x){
		_(x).style.display = "block";
	}
	function checkEmail(){
		var e = _("email").value;

		if (e != ""){
			_("estatus").innerHTML = 'checking...';
			var ajax = ajaxObj("POST", "/v2/app/dashboard/top");
			ajax.onreadystatechange = function(){
				if (ajaxReturn(ajax) == true){
					if (ajax.responseText == '<p style="color:#4CAF50;">This email is valid and available!</p>' || ajax.responseText == '<p style="color:#4CAF50;">Your email will stay the same.</p>'){
							_( "editBtn" ).style.display = "block";
							_("estatus").innerHTML = ajax.responseText;
						}
						else {
							_("estatus").innerHTML = ajax.responseText;
							_( "editBtn" ).style.display = "none";
						}
				}
			}
			ajax.send("emailcheck="+e)
		}
	}
	function editAccountInfo(){
		//var u = _("username").value;
		var e = _( "email" ).value;
		var c = _( "country" ).value;
		var g = _( "gender" ).value;

		if (e == "" || c == "" || g == "" ){
			_( "status" ).innerHTML = "<p style='color:#F44336;'>Fill out all of the form data</p>";
		} else {
			_("editBtn").innerHTML = "Loading...";
			var ajax = ajaxObj("POST", "/v2/app/dashboard/top");
			ajax.onreadystatechange = function(){
				if (ajaxReturn(ajax) == true){
					_("editBtn").innerHTML = "Save Changes";
					if (ajax.responseText != "account_updated"){
						Pleasure.handleToastrSettings( /* closeButton */ false, /*postion */ 'toast-bottom-left', /*sticky*/ false, /*type*/ 'success', /*closeOthers*/ true, /* title */ '', /* notification*/ 'Account successfully updated!' );
						_( "status" ).innerHTML = "";
					}else {
						_("editBtn").style.display = "none";
					}

				}
			}
			ajax.send("e=" + e + "&c=" + c + "&g=" + g );
		}
	}

	function editPassword(){
		var p1 = _("password1").value;
		var p2 = _("password2").value;
		if (p1 == "" || p2== ""){
			_("status_pass").innerHTML = "<p style='color:#F44336;'>Fill out all of the form data</p>";
		} else if (p1 != p2){
			_("status_pass").innerHTML = "<p style='color:#F44336;'>Your passwords don't match!</p>";
		}else {
			_("editPass").innerHTML = "Loading...";
			var ajax = ajaxObj("POST", "/v2/app/dashboard/top");
			ajax.onreadystatechange = function(){
				if (ajaxReturn(ajax) == true){
					_("editPass").innerHTML = "Change Passwords";
					if (ajax.responseText != "password_update"){
						Pleasure.handleToastrSettings( /* closeButton */ false, /*postion */ 'toast-bottom-left', /*sticky*/ false, /*type*/ 'success', /*closeOthers*/ true, /* title */ '', /* notification*/ 'Password successfully updated!' );
						_("status_pass").innerHTML = "";
						_("password1").value = "";
						_("password2").value = "";
					}
				}
			}
			ajax.send("p=" +p1)
		}
	}
</script>

<div class="user-layer">
	<ul class="nav nav-tabs nav-justified" role="tablist">
		<li class="active"><a href="#notifications" data-toggle="tab">Notifications <div class="badge" id="notesBadge"></div></a>
		</li>
		 <script>
			$(function() {
                noteBadge();
            });

             function noteBadge(timestamp)
             {
                 var notes = {'timestamp' : timestamp};

                 $.ajax(
                     {
                         type: 'GET',
                         url: '/v2/app/dashboard/badge.php',
                         data: notes,
                         success: function(data){
                             var obj = jQuery.parseJSON(data);
                             $('#notesBadge').html(obj.notifications);

                             noteBadge(obj.timestamp);
                         }
                     }
                 );
             }

        </script>
		<?php if ($thisPage != "index.php"){ ?>
		<li><a href="#settings" data-toggle="tab">Account Info</a>
		</li>
		<?php } ?>
	</ul>
	<div class="row no-gutters tab-content">
		<!--div class="tab-pane" id="messages">
        <div class="col-md-4">
          <div class="message-list-overlay"></div>
          <ul class="list-material message-list">
          </ul>
        </div>
		<div class="col-md-8">
          <div class="message-send-container">
            <div class="messages">
              <div class="message left">
                <div class="message-text">Hello!</div>
                <img src="../../assets/globals/img/faces/1.jpg" class="user-picture" alt=""> </div>
              <div class="message right">
                <div class="message-text">Hi!</div>
                <div class="message-text">Credibly innovate granular internal or "organic" sources whereas high standards in web-readiness. Energistically scale future-proof core competencies vis-a-vis impactful experiences.</div>
                <img src="../../assets/globals/img/faces/tolga-ergin.jpg" class="user-picture" alt=""> </div>
              <div class="message left">
                <div class="message-text">Dramatically synthesize integrated schemas with optimal networks.</div>
                <img src="../../assets/globals/img/faces/1.jpg" class="user-picture" alt=""> </div>
              <div class="message right">
                <div class="message-text">Interactively procrastinate high-payoff content</div>
                <img src="../../assets/globals/img/faces/tolga-ergin.jpg" class="user-picture" alt=""> </div>
              <div class="message left">
                <div class="message-text">Globally incubate standards compliant channels before scalable benefits. Quickly disseminate superior deliverables whereas web-enabled applications. Quickly drive clicks-and-mortar catalysts for change before vertical architectures.</div>
                <div class="message-text">Credibly reintermediate backend ideas for cross-platform models. Continually reintermediate integrated processes through technically sound intellectual capital. Holistically foster superior methodologies without market-driven best practices.</div>
                <img src="../../assets/globals/img/faces/1.jpg" class="user-picture" alt=""> </div>
              <div class="message right">
                <div class="message-text">Distinctively exploit optimal alignments for intuitive bandwidth</div>
                <img src="../../assets/globals/img/faces/tolga-ergin.jpg" class="user-picture" alt=""> </div>
              <div class="message left">
                <div class="message-text">Quickly coordinate e-business applications through</div>
                <img src="../../assets/globals/img/faces/1.jpg" class="user-picture" alt=""> </div>
            </div>


		<<div class="send-message">
              <div class="input-group">
                <div class="inputer inputer-blue">
                  <div class="input-wrapper">
                    <textarea rows="1" id="send-message-input" class="form-control js-auto-size" placeholder="Message"></textarea>
                  </div>
                </div>

		<span class="input-group-btn">
                <button id="send-message-button" class="btn btn-blue" type="button">Send</button>
                </span> </div>
            </div>


		</div>

		</div>


		<div class="mobile-back">
          <div class="mobile-back-button"><i class="ion-android-arrow-back"></i></div>
        </div>
		</div>
      -->

		<div class="tab-pane fade in active" id="notifications">
			<div class="col-md-6 col-md-offset-3">
				<ul class="list-material has-hidden" id="notelist">
					<?php if ($userlevel == 'a') {?>
						<p>Your account is not eligible to receive this feature. This feature is only
							available on VIP accounts and above.</p>
					<?php } ?>
				</ul>
				<script>
				 $(function() {
									 note();
							 });

								function note(timestamp)
								{
										var notes = {'timestamp' : timestamp};

										$.ajax(
												{
														type: 'GET',
														url: '/v2/app/dashboard/notelist.php',
														data: notes,
														success: function(data){
																var obj = jQuery.parseJSON(data);
																$('#notelist').html(obj.notifications);

																note(obj.timestamp);
														}
												}
										);
								}

					 </script>
			</div>
			<!--.col-->
		</div>
		<!--.tab-pane #notifications-->

		<div class="tab-pane fade" id="settings">
			<div class="col-md-6 col-md-offset-3">
				<div class="settings-panel">
					<p class="text-grey">Here, you can edit your account information, and logout here.
					</p>
						<form onsubmit="return false;" role="form" id="editForm">
							<div class="legend">Edit your acocunt info.</div>
							<ul>
								<li>
									<div class="inputer floating-label">
										<div class="input-wrapper">
											<input type="text" class="form-control" id="username" value="<?php echo $log_username ?>" onfocus="emptyElement('status'), enableButton('editBtn')" onKeyUp="checkusername()" disabled>
											<label for="username">Username</label>
										</div>
									</div>
								</li>
								<span id="unamestatus"></span>
								<li>
									<div class="inputer floating-label">
										<div class="input-wrapper">
											<input type="email" class="form-control" id="email" value="<?php echo $log_email ?>" onfocus="emptyElement('status'), enableButton('editBtn')" onKeyUp="checkEmail()" required>
											<label for="email">Email</label>
										</div>
									</div>
								</li>
								<span id="estatus"></span>
								<li>
								<div class="form-group">
									<div class="input-wrapper">
											<select id="country" class="selectpicker" data-width="100%"  onfocus="emptyElement('status'), enableButton('editBtn')" required>
												<?php include("php_includes/template_country_list.php") ?>
											</select>
										</div>
									</div>
								</li>
								<li>
									<div class="form-group">
									<div class="input-wrapper">
											<select id="gender" class="selectpicker" data-width="100%" onfocus="emptyElement('status'), enableButton('editBtn')" required>
												<option value="">Select gender</option>
												<option value="m">Male</option>
												<option value="f">Female</option>
												<option value="other">Other</option>
											</select>
										</div>
									</div>
								</li>
								<li>
								<div class="checkboxer checkboxer-indigo pull-right">
										<button id="editBtn" class="btn btn-default" onclick="editAccountInfo()">Save changes</button>
									</div>

								<div class="checkboxer checkboxer-indigo pull-left">
									<span id="status"></span>
									</div>
								</li>


							</ul>
					</form>
					<form onsubmit="return false;" role="form" id="editPassForm">
							<div class="legend">Edit Passwords.</div>
							<ul>
								<li>
									<div class="inputer floating-label">
										<div class="input-wrapper">
											<input type="password" class="form-control" id="password1"  onfocus="emptyElement('status_pass')" >
											<label for="password1">New Pasword</label>
										</div>
									</div>
								</li>
								<li>
									<div class="inputer floating-label">
										<div class="input-wrapper">
											<input type="password" class="form-control" id="password2" onfocus="emptyElement('status_pass')">
											<label for="password2">Confirm New Password</label>
										</div>
									</div>
								</li>
								<li>
								<div class="checkboxer checkboxer-indigo pull-right">
										<button id="editPass" onClick="editPassword()" class="btn btn-default">Change Passwords</button>
									</div>

								<div class="checkboxer checkboxer-indigo pull-left">
									<span id="status_pass"></span>
									</div>
								</li>


							</ul>
					</form>
						<div class="legend">Account Details</div>
							<ul>
								<li> Additional Controls:
									<div class="switcher switcher-indigo pull-right">
										<a href="/v2/app/dashboard/logout.php" class="btn btn-danger">Logout</a>
									</div>
									<!--.switcher-->
								</li>
							</ul>


				</div>
				<!--.settings-panel-->

			</div>
			<!--.col-->
		</div>
		<!--.tab-pane #settings-->

	</div>
	<!--.row-->
</div>
<!--.user-layer-->
<!-- END OF USER LAYER -->
<!--.layer-container-->
