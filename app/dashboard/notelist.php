<?php
include("notifications.php");
// set php runtime to unlimited
set_time_limit(0);
/*
$notificationsrow = $numrows + $numorows;

$response = array(
    'notifications' => $notificationsrow
);

$json = json_encode($response);

echo $json;
*/

//notifications

while (true){
    //Ajax call for a timespan
    $last_ajax_call = isset($_GET['timestamp']) ? (int)$_GET['timestamp'] : null;

    //Clear Cache
    clearstatcache();

    $last_change_in_file = time();


    // if no timestamp delivered via ajax or data.txt has been changed SINCE last ajax timestamp
    if ($last_ajax_call == null || $last_change_in_file > $last_ajax_call){
        $result = array(
            'notifications' => $notification_list,
            'timestamp' => $last_change_in_file
        );

        //json
        $json = json_encode($result);
        echo $json;

        //leave the loop step
        break;
    } else {
        //wait for one seconds
        sleep(15);
        continue;
    }

}
?>
