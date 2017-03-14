<?php 
//connecting to database
$conn = mysqli_connect("localhost", "tsnetwo2_ts", "Diandian123@", "tsnetwo2_totalsocial");
// Evaluate the connection
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}

if(isset($_POST["submit"])){

//posts the username
$name = $_POST["username"];
/*
$statement = mysqli_prepare($conn, "SELECT * FROM gmembers WHERE mname=?");
mysqli_stmt_bind_param($statement,"s" , $name);
mysqli_stmt_execute($statement);


mysqli_stmt_store_result($statement);
mysqli_stmt_bind_result($statement,$id, $groups, $member, $appr,$admin);

$response = array();
$response["success"] = false;


while (mysqli_stmt_fetch($statement)){
    $response["success"] = true; 
    $response["group"] = $groups;
    $response["row"] = mysqli_stmt_num_rows($statement);
}

*/

$response = array();
$response["success"] = false;

$sql = "SELECT gname FROM gmembers WHERE mname='$name'";

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_array($result ,MYSQL_ASSOC)){
    $response["success"] = true;
    $response["groups"][] = $row["gname"];
    
}
mysqli_close($conn);

echo json_encode($response);

}
?>

<html>
<head>
<title>Total Social Test</title>
</head>
<body>
    <form method="POST" role="form">
        <input type="text" name="username" placeholder="Enter Username" />
        <input type="submit" name="submit" value="Check Groups" />
    </form>
</body>
</html>