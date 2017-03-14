<?php
include("php_includes/check_login_status.php");

//query the user data of the logged in user.
$sql = "SELECT * FROM users WHERE username='$log_username' AND activated='1' LIMIT 1";
$user_query = mysqli_query( $db_conx, $sql );
while ( $row = mysqli_fetch_array( $user_query, MYSQLI_ASSOC ) ) {
  $loginip = $row["loginip"];
  $signupip = $row["ip"];
  $ipdiff = $row["ipdiff"];
}

//trusting the current IP address
if (isset($_POST['action']) && $_POST['action'] == "trustip"){
		//running a query
		$sql = "UPDATE users SET ip='$loginip', ipdiff='0' WHERE username='$log_username'";
		$query = mysqli_query($db_conx, $sql);
    mysqli_close($db_conx);
		echo "ipaddresschanged";
		exit();
}

?>
