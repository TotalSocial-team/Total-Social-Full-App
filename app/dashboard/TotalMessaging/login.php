<?php 
//connecting to database
$conn = mysqli_connect("localhost", "tsnetwo2_ts", "Diandian123@", "tsnetwo2_totalsocial");
// Evaluate the connection
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}

//posts the email and username
$email = $_POST["email"];
$password = md5($_POST["password"]);
$name = "";

$statement = mysqli_prepare($conn, "SELECT id, username, email, gender, country, verified, avatar FROM users WHERE email=? AND password = ?");
mysqli_stmt_bind_param($statement,"ss" , $email, $password);
mysqli_stmt_execute($statement);


mysqli_stmt_store_result($statement);
mysqli_stmt_bind_result($statement, $userID, $username, $email, $gender, $country, $verified, $avatar);

$response = array();
$response["success"] = false;


while (mysqli_stmt_fetch($statement)){
    $response["success"] = true; 
    $response["username"] = $username;
    $response["email"] = $email;
    $response["gender"] = $gender;
    $response["country"] = $country;
    $response["verified"] = $verified;
    $response["avatar"] = $avatar;
}


$sql = "SELECT * FROM gmembers WHERE mname='$username'";

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_array($result, MYSQL_BOTH)){
    $response['groups'][] = $row['gname'];

    //array_push($response,$row_array);

    //$i++;
   // $response["success"] = true;
   //$response["rows"] = mysqli_num_rows($result);
    
}
mysqli_free_result($result);
mysqli_close($conn);


echo json_encode($response);

?>

<html>
<head>
<title>Total Social Test</title>
</head>
<body>
    <form method="POST" role="form">
        <input type="email" name="email" placeholder="Enter Email" />
        <input type="password" name="password" placeholder="Enter Password" />
        <input type="submit" name="submit" value="Login" />
    </form>
</body>
</html>