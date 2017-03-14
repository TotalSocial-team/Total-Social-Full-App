<?php
// AJAX CALLS THIS CODE TO EXECUTE
if(isset($_POST["efp"])){
	// CONNECT TO THE DATABASE
	include_once( "php_includes/db_conx.php" );
	$eForgotPass = mysqli_real_escape_string($db_conx, $_POST['efp']);
	$sql = "SELECT id, username FROM users WHERE email='$eForgotPass' AND activated='1' LIMIT 1";
	$query = mysqli_query($db_conx, $sql);
	$numrows = mysqli_num_rows($query);
	if($numrows > 0){
		while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)){
			$id = $row["id"];
			$u = $row["username"];
		}
		$emailcut = substr($eForgotPass, 0, 4);
		$randNum = rand(10000,99999);
		$tempPass = "$emailcut$randNum";
		$hashTempPass = md5($tempPass);
		$sql = "UPDATE useroptions SET temp_pass='$hashTempPass' WHERE username='$u' LIMIT 1";
	    $query = mysqli_query($db_conx, $sql);
		$to = "$eForgotPass";
		$from = "Total Social <support@totalsocial.ca>";
		$headers ="From: $from\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1 \n";
		$subject ="Total Social Password Request";
		$msg = '<h2>Hello '.$u.',</h2><p>This is an automated message from Total Social.
    If you did not recently initiate the Forgot Password process,
    please disregard this email.</p><p>You indicated that you forgot your
    login password. We can generate a temporary password for you to log in with,
    then once logged in you can change your password to anything you like.</p>
    <p>After you click the link below your password to login will be:<br /><b>'.$tempPass.'</b></p>
    <p><a href="https://totalsocial.ca/v2/app/dashboard/password?u='.$u.'&p='.$hashTempPass.'">Click here now to apply the temporary password shown below to your account</a></p>
    <p>If you do not click the link in this email, no changes will be made to your account. In order to set your login password to the temporary password you must click the link above.</p>';
		if(mail($to,$subject,$msg,$headers)) {
			echo "success";
			exit();
		} else {
			echo "email_send_failed";
			exit();
		}
    } else {
        echo "no_exist";
    }
    exit();
}

?>
<?php
	include_once( "php_includes/db_conx.php" );
// EMAIL LINK CLICK CALLS THIS CODE TO EXECUTE
if(isset($_GET['u']) && isset($_GET['p'])){
	$u = preg_replace('#[^a-z0-9]#i', '', $_GET['u']);
	$temppasshash = preg_replace('#[^a-z0-9]#i', '', $_GET['p']);
	if(strlen($temppasshash) < 10){
		exit();
	}
	$sql = "SELECT id FROM useroptions WHERE username='$u' AND temp_pass='$temppasshash' LIMIT 1";
	$query = mysqli_query($db_conx, $sql);
	$numrows = mysqli_num_rows($query);
	if($numrows == 0){
		header("location: message.php?msg=There is no match for that username with that temporary password in the system. We cannot proceed.");
    	exit();
	} else {
		$row = mysqli_fetch_row($query);
		$id = $row[0];
		$sql = "UPDATE users SET password='$temppasshash' WHERE username='$u' LIMIT 1";
	    $query = mysqli_query($db_conx, $sql);
		$sql = "UPDATE useroptions SET temp_pass='' WHERE username='$u' LIMIT 1";
	    $query = mysqli_query($db_conx, $sql);
	    header("location: /v2/app/dashboard/account-access");
        exit();
    }
}
?>