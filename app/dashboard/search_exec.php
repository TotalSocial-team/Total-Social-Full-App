<?php
$output = "";
$sex = "Male";
if(isset($_POST['u'])){
	$u = preg_replace('#[^a-z0-9]#i', '', $_POST['u']);	
	if ($u == ""){
		// They tried to defeat our security
		echo $output;
		exit;		
	}
	include("php_includes/db_conx.php");	
	$sql = "SELECT username, avatar FROM users 
	        WHERE username LIKE '$u%' 
			ORDER BY username ASC";
	$user_query = mysqli_query($db_conx, $sql);
	$numrows = mysqli_num_rows($user_query);
	if($numrows > 0){
		while ($row = mysqli_fetch_array($user_query, MYSQLI_ASSOC)){
			$uname = $row["username"];
			$avatar = $row["avatar"];
			$gender = $row['gender'];

			if ($gender == "f")
			{
				$sex = "Female";
			}
			/* $output .= '<li class="has-action-left">
										<a href="#" class="hidden"><i class="ion-android-delete"></i></a>
										<a href="#" class="visible">
											<div class="list-action-left">
												<img src="../../assets/globals/img/faces/1.jpg" class="face-radius" alt="">
											</div>
											<div class="list-content">
												<span class="title">Pari Subramanium</span>
												<span class="caption">Legacy Response Assistant</span>
											</div>
										</a>
									</li>'; */
			$output .= '<li class="has-action-left">';
			$output .= '<a href="/v2/app/dashboard/user/'.$uname.'" class="hidden"><i class="ion-android-delete"></i></a>';
			$output .= '<a href="/v2/app/dashboard/user/'.$uname.'" class="visible">';
			$output .= '<div class="list-action-left">'; 
			$output .= '<img src="/v2/app/dashboard/userdata/' . $uname . '/' . $avatar . '" class="face-radius" alt=""> </div>';
			$output .= '<div class="list-content"> <span class="title">'.$uname.'</span><span class="caption">'.$sex.'</span></div>';
			$output .= '</a>';
			$output .= '</li>'; 
			
			/*
			 	<li class="has-action-left">
										<a href="#" class="hidden"><i class="ion-android-delete"></i></a>
										<a href="#" class="visible">
											<div class="list-action-left">
												<img src="../../assets/globals/img/faces/1.jpg" class="face-radius" alt="">
											</div>
											<div class="list-content">
												<span class="title">Pari Subramanium</span>
												<span class="caption">Legacy Response Assistant</span>
											</div>
										</a>
									</li>
			*/
		}
		echo $output;
		exit;
	} else {
		// No results from search
		$output = "No results";
		echo $output;
		exit;
	}
}
?>