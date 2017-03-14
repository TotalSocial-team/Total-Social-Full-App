
<?php
// Protect this script from direct url access
// You may further enhance this protection by checking for certain sessions and other means
if ((!isset($isFriend)) || (!isset($isOwner))){
	exit;
}
// Initialize our ui
$pm_ui = "";
// If visitor to profile is a friend and is not the owner can send you a pm
// Build ui carry the profile id, vistor name, pm subject and comment to js
if($isFriend == true && $isOwner == "no"){
	$pm_ui = "<hr>";
	$pm_ui .= '<input id="pmsubject" onkeyup="statusMax(this,30)" placeholder="Subject of pm..."><br />';
	$pm_ui .= '<textarea id="pmtext" onkeyup="statusMax(this,250)" placeholder="Send '.$u.' a private message"></textarea>';
	$pm_ui .= '<button id="pmBtn" onclick="postPm(''.$u.'',''.$log_username.'','pmsubject','pmtext')">Send</button>';
}
?>
<script>
function postPm(tuser,fuser,subject,ta){
	var data = _(ta).value;
	var data2 = _(subject).value;
	if(data == "" || data2 == ""){
		alert("Fill all fields");
		return false;
	}
	_("pmBtn").disabled = true;
	var ajax = ajaxObj("POST", "php_parsers/pm_system.php");
	ajax.onreadystatechange = function() {
		if(ajaxReturn(ajax) == true) {
			if(ajax.responseText == "pm_sent"){
				alert("Message has been sent.");
				_("pmBtn").disabled = false;
				_(ta).value = "";
				_(subject).value = "";
			} else {
				alert(ajax.responseText);
			}
		}
	}
	ajax.send("action=new_pm&fuser="+fuser+"&tuser="+tuser+"&data="+data+"&data2="+data2);
}
function statusMax(field, maxlimit) {
	if (field.value.length > maxlimit){
		alert(maxlimit+" maximum character limit reached");
		field.value = field.value.substring(0, maxlimit);
	}
}
</script>
<div id="statusui">
  <?php echo $pm_ui; ?>
</div><!-- BEGIN SIDEBAR -->
<div class="sidebar">
  <div class="logopanel">
    <h1>
      <a href="index.php"></a>
    </h1>
  </div>
  <div class="sidebar-inner">
    <ul class="nav nav-sidebar">
      <li class=" nav-active active"><a href="dashboard.php"><i class="icon-home"></i><span>Dashboard</span></a></li>
      <li class="nav-parent">
        <a href=""><i class="icon-user"></i><span>User </span><span class="fa arrow"></span></a>
      </li>
    </ul>
    <div class="sidebar-footer clearfix">
      <a class="pull-left footer-settings" href="#" data-rel="tooltip" data-placement="top" data-original-title="Settings">
      <i class="icon-settings"></i></a>
      <a class="pull-left toggle_fullscreen" href="#" data-rel="tooltip" data-placement="top" data-original-title="Fullscreen">
      <i class="icon-size-fullscreen"></i></a>
      <a class="pull-left" href="user-lockscreen.html" data-rel="tooltip" data-placement="top" data-original-title="Lockscreen">
      <i class="icon-lock"></i></a>
      <a class="pull-left btn-effect" href="user-login-v1.html" data-modal="modal-1" data-rel="tooltip" data-placement="top" data-original-title="Logout">
      <i class="icon-power"></i></a>
    </div>
  </div>
</div>
<!-- END SIDEBAR -->
<div class="main-content">
  <!-- BEGIN TOPBAR -->
  <div class="topbar">
    <div class="header-left">
      <div class="topnav">
        <a class="menutoggle" href="#" data-toggle="sidebar-collapsed"><span class="menu__handle"><span>Menu</span></span></a>
        <ul class="nav nav-icons">
          <li><a href="#" class="toggle-sidebar-top"><span class="icon-user-following"></span></a></li>
          <li><a href="mailbox.html"><span class="octicon octicon-mail-read"></span></a></li>
          <li><a href="#"><span class="octicon octicon-flame"></span></a></li>
          <li><a href="builder-page.html"><span class="octicon octicon-rocket"></span></a></li>
        </ul>
      </div>
    </div>
    <div class="header-right">
      <ul class="header-menu nav navbar-nav">
        <!-- BEGIN USER DROPDOWN -->
        <li class="dropdown" id="language-header">
          <a href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
          <i class="icon-globe"></i>
          <span>Language</span>
          </a>
          <ul class="dropdown-menu">
            <li>
              <a href="#" data-lang="en"><img src="../assets/global/images/flags/usa.png" alt="flag-english"> <span>English</span></a>
            </li>
            <li>
              <a href="#" data-lang="es"><img src="../assets/global/images/flags/spanish.png" alt="flag-english"> <span>Español</span></a>
            </li>
            <li>
              <a href="#" data-lang="fr"><img src="../assets/global/images/flags/french.png" alt="flag-english"> <span>Français</span></a>
            </li>
          </ul>
        </li>
        <!-- END USER DROPDOWN -->
        <!-- BEGIN NOTIFICATION DROPDOWN -->
        <li class="dropdown" id="notifications-header">
          <a href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
          <i class="icon-bell"></i>
          <span class="badge badge-danger badge-header">6</span>
          </a>
        <!-- BEGIN MESSAGES DROPDOWN -->
        <li class="dropdown" id="messages-header">
          <a href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
          <i class="icon-paper-plane"></i>

          </a>
          <!-- BEGIN USER DROPDOWN -->
          <li class="dropdown" id="user-header">
            <a href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
            <img src="../assets/global/images/avatars/user1.png" alt="user image">
            <span class="username"><?php echo $u?></span>
            </a>
            <ul class="dropdown-menu">
              <li>
                <a href="user.php"><i class="icon-user"></i><span>My Profile</span></a>
              </li>
              <li>
                <a href="#"><i class="icon-settings"></i><span>Account Settings</span></a>
              </li>
              <li>
                <a href="#"><i class="icon-logout"></i><span>Logout</span></a>
              </li>
            </ul>
          </li>
          <!-- END USER DROPDOWN -->
          <!-- CHAT BAR ICON -->
          <li id="quickview-toggle"><a href="#"><i class="icon-bubbles"></i></a></li>
        </ul>
      </div>
      <!-- header-right -->
