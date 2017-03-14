<?php
require(dirname(__FILE__).'/vendor/autoload.php');
include(dirname(__FILE__).'../../notifications.php');

$pusher = new Pusher('2b54502c7478473bc9f7', '0d6e6540f7cde54c42c6', '272574');

while (true) {
  sleep(60);
// trigger on my_channel' an event called 'my_event' with this payload:
  $notificationsrow = $numorows + $numrows;

  $data['message'] = $notificationsrow;

  $pusher->trigger('notifications', 'notify', $data);

echo $data['message'];
}
?>
