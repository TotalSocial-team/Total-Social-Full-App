var hasImage = "";

var badwordarray = [
        'bitch',
        'fuck',
        'asshole',
        'ass',
        'kill',
        'die',
        'nigger',
        'faggot',
        'aryan',
        'bastard']

window.onbeforeunload = function(){
	if(hasImage != ""){
	    return "You have not posted your image";
	}
}
function showBtnDiv(){
	_("statustext").style.height = "80px";
	_("btns_SP").style.display = "block";
}
function doUpload(id){
	// www.developphp.com/video/JavaScript/File-Upload-Progress-Bar-Meter-Tutorial-Ajax-PHP
	var file = _(id).files[0];
	if(file.name == ""){
		return false;
	}
	if(file.type != "image/jpeg" && file.type != "image/gif"){
		alert("That file type is not supported.");
		return false;
	}
	_("triggerBtn_SP").style.display = "none";
	_("uploadDisplay_SP").innerHTML = "Image uploading......";
	var formdata = new FormData();
	formdata.append("stPic", file);
	var ajax = new XMLHttpRequest();
	ajax.addEventListener("load", completeHandler, false);
	ajax.addEventListener("error", errorHandler, false);
	ajax.addEventListener("abort", abortHandler, false);
	ajax.open("POST", "/v2/app/dashboard/photo_system.php");
	ajax.send(formdata);
}
function completeHandler(event){
	var data = event.target.responseText;
	var datArray = data.split("|");
	if(datArray[0] == "upload_complete"){
		hasImage = datArray[1];
		_("uploadDisplay_SP").innerHTML = '<img src="/v2/app/dashboard/tempUploads/'+datArray[1]+'" class="statusImage" />';
	} else {
		_("uploadDisplay_SP").innerHTML = datArray[0];
		_("triggerBtn_SP").style.display = "block";
	}
}
function errorHandler(event){
	_("uploadDisplay_SP").innerHTML = "Upload Failed";
	_("triggerBtn_SP").style.display = "block";
}
function abortHandler(event){
	_("uploadDisplay_SP").innerHTML = "Upload Aborted";
	_("triggerBtn_SP").style.display = "block";
}
    function similarity(s1, s2) {
  var longer = s1;
  var shorter = s2;
  if (s1.length < s2.length) {
    longer = s2;
    shorter = s1;
  }
  var longerLength = longer.length;
  if (longerLength === 0) {
    return 1.0;
  }
  return (longerLength - editDistance(longer, shorter)) / parseFloat(longerLength);
}

function editDistance(s1, s2) {
  s1 = s1.toLowerCase();
  s2 = s2.toLowerCase();

  var costs = new Array();
  for (var i = 0; i <= s1.length; i++) {
    var lastValue = i;
    for (var j = 0; j <= s2.length; j++) {
      if (i == 0)
        costs[j] = j;
      else {
        if (j > 0) {
          var newValue = costs[j - 1];
          if (s1.charAt(i - 1) != s2.charAt(j - 1))
            newValue = Math.min(Math.min(newValue, lastValue),
              costs[j]) + 1;
          costs[j - 1] = lastValue;
          lastValue = newValue;
        }
      }
    }
    if (i > 0)
      costs[s2.length] = lastValue;
  }
  return costs[s2.length];
}
function postToStatus( action, type, user, ta ) {
		var data = _( ta ).value;
    var compaare = data.toLowerCase();
    var $str1 = compaare;
    for (var i = 0; i < badwordarray.length; i++){
    var $str2 = badwordarray[i];
    var perc=Math.round(similarity($str1,$str2)*10000)/100;
    //console.log(perc);
    //console.log($str2);
    if (perc > 50){
        swal( "Your post was too similar to our swear words." , "Your post, "+$str1+ " was "+perc+"% similar to our list of swear words. As a result, you cannot continue until you make your post less vulgar.", "error" );
        return false;
    }
}

	if ( data == "" && hasImage == "" ) {
			swal( "Looks like you haven't typed anything.", " You can't post nothing. Go ahead and type something :)", "error" );
			return false;
		}
		var data2 = "";
		if ( data != "" ) {
			data2 = data.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\n/g,"<br />").replace(/\r/g,"<br />");
		}


		if ( data2 == "" && hasImage != "" ) {
			data = "||na||";
			data2 = '<img src="/v2/app/dashboard/permUploads/' + hasImage + '" />';
		} else if ( data2 != "" && hasImage != "" ) {
			data2 += '<br /><img src="/v2/app/dashboard/permUploads/' + hasImage + '" />';
		} else {
			hasImage = "na";
		}

		_( "statusBtn" ).disabled = true;
		var ajax = ajaxObj( "POST", "/v2/app/dashboard/status_system.php" );
		ajax.onreadystatechange = function () {
			data2.trim();
			if ( ajaxReturn( ajax ) == true ) {
				var datArray = ajax.responseText.split( "|" );
				if ( datArray[ 0 ] == "post_ok" ) {
					var sid = datArray[ 1 ];
					var currentHTML = _( "statusarea" ).innerHTML;
					_( "statusarea" ).innerHTML = '<div id="status_' + sid + '" class="col-md-4"> <div class="card tile card-post"> <div class="card-heading"><?php echo $profile_pic ?><p class="author">Yourself</p> <p class="time">Just Now</p> </div> <div class="card-body"> ' + data2 + ' <ul class="post-action"> <li><a href="#">Share</a></li> <li><a id="sdb_' + sid + '" href="#" onclick="return false;" onmousedown="deleteStatus(\'' + sid + '\',\'status_' + sid + '\');" title="DELETE THIS STATUS AND ITS REPLIES">Delete</a></li> </ul> </div> <div class="card-footer"> <ul class="card-comments" id="replies_' + sid + '"></ul> <div class="input-group"> <div class="inputer"> <div class="input-wrapper"> <textarea id="replytext_' + sid + '" class=" form-control maxlength maxlength-textarea" maxlength="250" rows="1" placeholder="Write a comment here"></textarea> </div> </div> <div class="input-group-btn"> <button id="replyBtn_' + sid + '" onclick="replyToStatus(' + sid + ',\'<?php echo $u; ?>\',\'replytext_' + sid + '\',this)" type="button" class="btn btn-default">Send</button> </div> </div> </div> </div> </div>' + currentHTML;
					_( "statusBtn" ).disabled = false;
					_( "triggerBtn_SP" ).style.display = "block";
					_( "triggerBtn_SP" ).innerHTML = "<span class='ion-android-attach'></span>";

					_( "btns_SP" ).style.display = "none";
					_( "uploadDisplay_SP" ).innerHTML = "";
					_( "fu_SP" ).value = "";
					hasImage = "";
					_( ta ).value = "";
					location.reload();
				} else {
					swal( ajax.responseText );
				}
			}
		}
		ajax.send( "action=" + action + "&type=" + type + "&user=" + user + "&data=" + data + "&image=" + hasImage );
	}

	function replyToStatus( sid, user, ta, btn ) {
		var data = _( ta ).value;
		if ( data == "" ) {
			swal( "Replies cannot be empty", "Please enter something and try again!", "error" );
			return false;
		}
    var compaare = data.toLowerCase();
    var $str1 = compaare;
    for (var i = 0; i < badwordarray.length; i++){
    var $str2 = badwordarray[i];
    var perc=Math.round(similarity($str1,$str2)*10000)/100;
    console.log(perc);
    console.log($str2);
    if (perc > 50){
        swal( "Your post was too similar to our swear words." , "Your post, "+$str1+ " was "+perc+"% similar to our list of swear words. As a result, you cannot continue until you make your post less vulgar.", "error" );
        return false;
    }
}
		_( "replyBtn_" + sid ).disabled = true;
		var ajax = ajaxObj( "POST", "/v2/app/dashboard/status_system.php" );
		ajax.onreadystatechange = function () {
			if ( ajaxReturn( ajax ) == true ) {
				var datArray = ajax.responseText.split( "|" );
				if ( datArray[ 0 ] == "reply_ok" ) {
					var rid = datArray[ 1 ];
					data = data.replace( /</g, "&lt;" ).replace( />/g, "&gt;" ).replace( /\n/g, "<br />" ).replace( /\r/g, "<br />" );
					_( "replies_" + sid ).innerHTML += '<li id="reply_' + rid + '" class="reply_boxes"><div class="user-photo"><?php echo $profile_pic ?></div><div class="comment">' + data + ' <br /> <a id="srdb_' + rid + '" href="#" onclick="return false;" onmousedown="deleteReply(\'' + rid + '\',\'reply_' + rid + '\');" title="DELETE THIS COMMENT">remove</a></div></li>';
					_( "replyBtn_" + sid ).disabled = false;
					_( ta ).value = "";
					location.reload();

				} else {
					alert( ajax.responseText );
				}
			}
		}
		ajax.send( "action=status_reply&sid=" + sid + "&user=" + user + "&data=" + data );
	}

	function deleteStatus( statusid, statusbox ) {

		swal( {
				title: "Are you sure?",
				text: "You will not be able to recover this post!",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Yes, delete it!",
				closeOnConfirm: true
			},

			function ( conf ) {
				if ( conf != true ) {
					return false;
				} else {
					var ajax = ajaxObj( "POST", "/v2/app/dashboard/status_system.php" );
					ajax.onreadystatechange = function () {
						if ( ajaxReturn( ajax ) == true ) {
							if ( ajax.responseText == "delete_ok" ) {
								_( statusbox ).style.display = 'none';
								_( "replytext_" + statusid ).style.display = 'none';
								_( "replyBtn_" + statusid ).style.display = 'none';
							} else {
								SweetAlert( ajax.responseText );
							}
						}
					}
					ajax.send( "action=delete_status&statusid=" + statusid );
				}
			} );
	}

	function deleteReply( replyid, replybox ) {
		swal( {
				title: "Are you sure?",
				text: "You will not be able to recover this reply!",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Yes, delete it!",
				closeOnConfirm: true
			},

			function ( conf ) {
				if ( conf != true ) {
					return false;
				} else {
					var ajax = ajaxObj( "POST", "/v2/app/dashboard/status_system.php" );
					ajax.onreadystatechange = function () {
						if ( ajaxReturn( ajax ) == true ) {
							if ( ajax.responseText == "delete_ok" ) {
								_( replybox ).style.display = 'none';
							} else {
								alert( ajax.responseText );
							}
						}
					}
					ajax.send( "action=delete_reply&replyid=" + replyid );
				}
			} );

	}

	function statusMax( field, maxlimit ) {
		if ( field.value.length > maxlimit ) {
			alert( maxlimit + " maximum character limit reached" );
			field.value = field.value.substring( 0, maxlimit );
		}
	}
	function shareStatus(id){
	var ajax = ajaxObj("POST", "/v2/app/dashboard/status_system.php");
	ajax.onreadystatechange = function() {
		if(ajaxReturn(ajax) == true) {
			if(ajax.responseText == "share_ok"){
				Pleasure.handleToastrSettings( /* closeButton */ false, /*postion */ 'toast-bottom-left', /*sticky*/ false, /*type*/ 'info', /*closeOthers*/ true, /* title */ '', /* notification*/ 'Post successsfully shared!' );
			} else {
				alert(ajax.responseText);
			}
		}
	}
	ajax.send("action=share&id="+id);
}
