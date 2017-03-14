function friendToggle(type,user,elem){
				var conf = confirm("Press OK to confirm the '"+type+"' action for user <?php echo $u; ?>.");
				if(conf != true){
					return false;
				}
				alert("Please wait...");
				var ajax = ajaxObj("POST", "friend_system.php");
				ajax.onreadystatechange = function() {
					if(ajaxReturn(ajax) == true) {
						if(ajax.responseText == "friend_request_sent"){
							alert("Friend Request Sent");
						} else if(ajax.responseText == "unfriend_ok"){
							_(elem).innerHTML = '<button class="btn btn-block btn-primary bd-0 no-bd" onclick="friendToggle(\'friend\',\'<?php echo $u; ?>\',\'friendBtn\')">Add Friend</button>';
						} else {
							alert(ajax.responseText);
							alert("Try again");
						}
					}
				}
				ajax.send("type="+type+"&user="+user);
			}
			function blockToggle(type,blockee,elem){
				var conf = confirm("Press OK to confirm the '"+type+"' action on user <?php echo $u; ?>.");
				if(conf != true){
					return false;
				}
				var elem = document.getElementById(elem);
				alert('please wait ...');
				var ajax = ajaxObj("POST", "block_system.php");
				ajax.onreadystatechange = function() {
					if(ajaxReturn(ajax) == true) {
						if(ajax.responseText == "blocked_ok"){
							_(elem).innerHTML = '<button class="btn btn-defualt" onclick="blockToggle(\'unblock\',\'<?php echo $u; ?>\',\'blockBtn\')">Unblock User</button>';
						} else if(ajax.responseText == "unblocked_ok"){
							elem.innerHTML = '<button class="btn btn-defualt" onclick="blockToggle(\'block\',\'<?php echo $u; ?>\',\'blockBtn\')">Block User</button>';
						} else {
							alert(ajax.responseText);
								alert("Try again later");
						}
					}
				}
				ajax.send("type="+type+"&blockee="+blockee);
			}
			function friendReqHandler(action,reqid,user1,elem){
				var conf = confirm("Press OK to '"+action+"' this friend request.");
				if(conf != true){
					return false;
				}
				alert("processing ...");
				var ajax = ajaxObj("POST", "friend_system.php");
				ajax.onreadystatechange = function() {
					if(ajaxReturn(ajax) == true) {
						if(ajax.responseText == "accept_ok"){
							alert("Your are now friends");
						} else if(ajax.responseText == "reject_ok"){
							alert("You chose to reject friendship with this user");
						} else {
								alert(ajax.responseText);
						}
					}
				}
				ajax.send("action="+action+"&reqid="+reqid+"&user1="+user1);
			}