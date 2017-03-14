<?php
$status_ui = "";
$statuslist = "";
$statusreply = "";
$statustextreply = "";
$author = "";
if ( $isOwner == "Yes" ) {
	$status_ui = '<div class="row">
            <div class="col-md-12">
              <div class="panel">
                <div class="panel-heading">
                  <div class="panel-title">
                    <h4>NEW POST</h4>
                  </div>
                </div>
                <!--.panel-heading-->
                <div class="panel-body">
                  <div class="form-content">
                    <div class="inputer">
                      <div class="input-wrapper">
                        <textarea id="statustext" class="form-control maxlength maxlength-textarea" maxlength="250" rows="4" placeholder="What is on your mind, ' . $u . '?"></textarea>
                      </div>
                    </div>
                  </div>
                  <div class="form-buttons clearfix">
                    <div class="pull-left">
                      <button type="button" id="triggerBtn_SP" class="btn btn-default" onclick="triggerUpload(event, \'fu_SP\')"><span class="ion-android-attach"></span></button>
					  <div id="standardUpload" class="hiddenStuff">
					  	<form id="image_SP" enctype="multipart/form-data" method="post">
						<input type="file" name="FileUpload" id="fu_SP" onchange="doUpload(\'fu_SP\')"/>
						</form>
					  </div>
                    </div>
                    <div class="pull-right">
                      <button type="button" id="statusBtn" onclick="postToStatus(\'status_post\',\'a\',\'' . $u . '\',\'statustext\')" class="btn btn-default ">Post Status Update <i class="ion-android-arrow-forward"></i></button>
                    </div>
                  </div>
                </div>
				<div id="uploadDisplay_SP"></div>
                <!--.panel-body-->
              </div>
              <!--.panel-->

            </div>
            <!--.col-md-12-->
          </div>
		  ';

} else if ( $isFriend == true && $log_username != $u ) {
	$status_ui = '<div class="row">
            <div class="col-md-12">
              <div class="panel">
                <div class="panel-heading">
                  <div class="panel-title">
                    <h4>NEW POST</h4>
                  </div>
                </div>
                <!--.panel-heading-->
                <div class="panel-body">
                  <div class="form-content">
                    <div class="inputer">
                      <div class="input-wrapper">
                        <textarea id="statustext" class="form-control maxlength maxlength-textarea" maxlength="250" rows="4" placeholder="Hello ' . $log_username . ', say something to your friend, ' . $u . '."></textarea>
                      </div>
                    </div>
                  </div>

                  <div class="form-buttons clearfix">
                    <div class="pull-left">
                      <button type="button" class="btn btn-default"><span class="ion-android-attach"></span></button>
                    </div>
                    <div class="pull-right">
                      <button type="button" id="statusBtn" onclick="postToStatus(\'status_post\',\'c\',\'' . $u . '\',\'statustext\')" class="btn btn-default">Post Status Update <i class="ion-android-arrow-forward"></i></button>
                    </div>
                  </div>
                </div>
                <!--.panel-body-->
              </div>
              <!--.panel-->

            </div>
            <!--.col-md-12-->
          </div>';
} else if ( $isFriend == false && $log_username != $u ) {
	$status_ui = '<h1> Oops!</h1>';
	$status_ui .= '<p>You could still reply on previous posts, but you cannot post to this user until you follow them.<p>';
	$status_ui .= '<p><span id="friendBtn">' . $friend_button . '</span></p>';
}
?>
<?php
$sql = "SELECT * FROM status WHERE account_name='$u' AND type='a' OR account_name='$u' AND type='c' ORDER BY postdate DESC LIMIT 20";
$query = mysqli_query( $db_conx, $sql );
$statusnumrows = mysqli_num_rows( $query );
while ( $row = mysqli_fetch_array( $query, MYSQLI_ASSOC ) ) {
	$statusid = $row[ "id" ];
	$account_name = $row[ "account_name" ];
	$author = $row[ "author" ];
	$postdate = $row[ "postdate" ];
	$data = $row[ "data" ];
	$data = nl2br( $data );
	$data = str_replace( "&amp;", "&", $data );
	$data = stripslashes( $data );
	$statusDeleteButton = '';
	if ( $author == $log_username || $account_name == $log_username ) {
		$statusDeleteButton = '<a id="sdb_' . $statusid . '" href="#" onclick="return false;" onmousedown="deleteStatus(\'' . $statusid . '\',\'status_' . $statusid . '\');" title="DELETE THIS STATUS AND ITS REPLIES">Delete</a> &nbsp; &nbsp;';
	}
	$shareButton = "";
	if($log_username != "" && $author != $log_username && $account_name != $log_username){
			$shareButton = '<a href="#" onclick="return false;" onmousedown="shareStatus(\''.$statusid.'\');" title="SHARE THIS">Share</a>';
	}
	// GATHER UP ANY STATUS REPLIES
	$status_replies = "";
	$query_replies = mysqli_query( $db_conx, "SELECT * FROM status WHERE osid='$statusid' AND type='b' ORDER BY postdate ASC" );
	$replynumrows = mysqli_num_rows( $query_replies );
	if ( $replynumrows > 0 ) {
		while ( $row2 = mysqli_fetch_array( $query_replies, MYSQLI_ASSOC ) ) {
			$statusreplyid = $row2[ "id" ];
			$replyauthor = $row2[ "author" ];
			$replydata = $row2[ "data" ];
			$replydata = nl2br( $replydata );
			$replypostdate = $row2[ "postdate" ];
			$replydata = str_replace( "&amp;", "&", $replydata );
			$replydata = stripslashes( $replydata );
			$replyDeleteButton = '';
			if ( $replyauthor == $log_username || $account_name == $log_username ) {
				$replyDeleteButton = '<a id="srdb_' . $statusreplyid . '" href="#" onclick="return false;" onmousedown="deleteReply(\'' . $statusreplyid . '\',\'reply_' . $statusreplyid . '\');" title="DELETE THIS COMMENT">remove</a>';
			} else {
				$replyDeleteButton = '';
			}
			//$status_replies .= '<div id="reply_' . $statusreplyid . '" class="reply_boxes"><div><b>Reply by <a href="user.php?u=' . $replyauthor . '">' . $replyauthor . '</a> ' . $replypostdate . ':</b> ' . $replyDeleteButton . '<br />' . $replydata . '</div></div>';

			$status_replies .= '<li id="reply_' . $statusreplyid . '"><div class="user-photo"><a href="/v2/app/dashboard/user/' . $replyauthor . '">' . $profile_pic . '</a></div><div class="comment">' . $replydata . ' <br /> ' . $replyDeleteButton . '</div></li>';
		}
	}
	$statuslist .= '<div id="status_' . $statusid . '" class="col-md-4">
              <div class="card tile card-post">
                <div class="card-heading">' . $profile_pic . '
                  <p class="author"><a href="/v2/app/dashboard/user/' . $author . '">' . $author . '</a></p>
                  <p class="time">' .date('F d, Y - g:ia', strtotime($postdate)). '</p>
                </div>
                <!--.card-heading -->
                <div class="card-body fixed-ratio-resize">' . $data . '
                  <ul class="post-action">
                    <li>'.$shareButton.'</li>
					<li>' . $statusDeleteButton . '</li>
                  </ul>
                </div>
                <!--.card-body -->
                <div class="card-footer">
                  <ul class="card-comments" id="replies_' . $statusid . '">
                   ' . $status_replies . '
                  </ul>
				  	<div class="input-group">
                    <div class="inputer">
                      <div class="input-wrapper">
                        <textarea id="replytext_' . $statusid . '" class="replytext form-control maxlength maxlength-textarea" maxlength="250" rows="1" placeholder="Write a comment here"></textarea>
                      </div>
                    </div>
                    <div class="input-group-btn">
                      <button id="replyBtn_' . $statusid . '" onclick="replyToStatus(' . $statusid . ',\'' . $u . '\',\'replytext_' . $statusid . '\',this)" type="button" class="btn btn-default">Send</button>
                    </div>
                    <!--.input-group-btn -->
                  </div>
                  <!--.input-group-->
                </div>
                <!--.card-footer -->
              </div>
              <!--.card -->
			</div>
            <!--.col-md-4 -->';
}
?>
<script src="/v2/app/dashboard/js/sA/dist/sweetalert.min.js"></script>
<link rel="stylesheet" type="text/css" href="/v2/app/dashboard/js/sA/dist/sweetalert.css">
<script src="/v2/app/dashboard/js/main.js"></script>
<script src="/v2/app/dashboard/js/ajax.js"></script>
<script src="/v2/app/dashboard/js/swords.js" type="text/javascript"></script> 
<style type="text/css">
    .fixed-ratio-resize { /* basic responsive img */
	max-width: 100%;
	height: auto;
	width: auto\9; /* IE8 */
}
</style>
