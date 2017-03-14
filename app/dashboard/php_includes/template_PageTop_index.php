<?php
// It is important for any file that includes this file, to have
// check_login_status.php included at its very top.
$envelope = '';
$loginLink = '';
$topbar = '';

if($user_ok == true) {
	$sql = "SELECT notescheck FROM users WHERE username='$log_username' LIMIT 1";
	$query = mysqli_query($db_conx, $sql);
	$row = mysqli_fetch_row($query);
	$notescheck = $row[0];
	$sql = "SELECT id FROM notifications WHERE username='$log_username' AND date_time > '$notescheck' AND did_read = '0' LIMIT 1";
	$query = mysqli_query($db_conx, $sql);
	$numrows = mysqli_num_rows($query);    $loginLink = '<li data-open-after="true"> <a href="/v2/app/dashboard/index/'.$log_username.'">Dashboard</a> </li>
      <li> <a href="/v2/app/dashboard/user/'.$u.'">User Profile</a> </li>';
}

?>