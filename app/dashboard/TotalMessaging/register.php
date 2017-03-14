<?php
    //connecting to databse
    include("php_includes/db_conx.php");

    //includes the password encryption library
   //require("password.php");

    //declaation of variables and storing data
    $failedtoCopy = "";

    $username = preg_replace( '#[^a-z0-9]#i', '', $_POST[ 'username' ] );
    $email = mysqli_real_escape_string( $db_conx, $_POST[ 'email' ] );
    $ppassword = $_POST[ 'ppassword' ];
    $gender = preg_replace( '#[^a-z]#', '', $_POST[ 'gender' ] );
    $country = preg_replace( '#[^a-z ]#i', '', $_POST[ 'country' ] );
    // GET USER IP ADDRESS
    $ip = preg_replace( '#[^0-9.]#', '', getenv( 'REMOTE_ADDR' ) );
    $passwordHash = md5($p);

    //declares the avatar
    $avatar = 'user.png';

    function registerUser(){
      global $username, $email, $password ,$passwordHash, $country, $gender, $avatar;
      $registerstatement = mysqli_prepare($db_conx, "INSERT INTO users (username, email, password, gender, country, ip, signup, lastlogin, notescheck, avatar)
      VALUES('?','?','?','?','?','?',now(),now(),now(), '?')");
      mysqli_stmt_bind_param($registerstatement, "sssssis", $username, $email, $passwordHash, $gender, $country, $ip, $avatar);
      mysqli_stmt_execute($registerstatement);
      mysqli_stmt_close($registerstatement);

      //Inserts the id into a variable
      $uid = mysqli_insert_id($db_conx);
      
      //Establish their useroptions table
      $useropts = mysqli_prepare($db_conx, "INSERT INTO useroptions (id, username, background) VALUES ('?', '?', 'original')");
      mysqli_stmt_bind_param($useropts, "is", $uid, $username);
      mysqli_stmt_execute($useropts);
      mysqli_stmt_close($useropts);

      //Creates a user folder for storage purposes
      if (!file_exists("userdata/$username")){
          mkdir("userdata/$username", 0755);
      }

      $avatar = "user.png";
      $avatar2 = "userdata/$username/user.png";

      if (!copy($avatar, $avatar2)){
          $failedtoCopy = "failed to copy!";
      }

    }
    function checkUsername(){
      global $username, $db_conx;
      $statement = mysqli_prepare($db_conx, "SELECT * FROM users WHERE username= ?");
      mysqli_stmt_bind_param($statement, "s", $username);
      mysqli_stmt_execute($statement);
      mysqli_stmt_store_result($statement);
      $count = mysqli_stmt_num_rows($statement);
      mysqli_stmt_close($statement);    
      if ($count < 1){
          return true;
      }else{
          return false;
      }
    }

    function checkEmail(){
      global $email, $db_conx;
      $statement = mysqli_prepare($db_conx, "SELECT * FROM users WHERE email=?");
      mysqli_stmt_bind_param($statement, "s", $email);
      mysqli_stmt_execute($statement);
      mysqli_stmt_store_result($statement);
      $count = mysqli_stmt_num_rows($statement);
      mysqli_stmt_close($statement);       
      if ($count < 1){
          return true;
      }else{
          return false;
      }
    }
    $response = array();
    $response["success"] = false;
    $response["failed"] = $failedtoCopy;
    if (usernameAvailable() && checkEmail()){
        registerUser();
        $response["success"] = true;  
    }
    
    echo json_encode($response);
    
?>