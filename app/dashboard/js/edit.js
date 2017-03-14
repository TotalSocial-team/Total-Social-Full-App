	 $(document).ready(function() {
	     checkEmail();
	     //checkusername();
	 });

	 function restrict(elem) {
	     var tf = _(elem);
	     var rx = new RegExp;
	     if (elem == "username") {
	         rx = /[^a-z0-9]/gi;
	     }
	     tf.value = tf.value.replace(rx, "");
	 }

	 function emptyElement(x) {
	     _(x).innerHTML = "";
	 }

	 function enableButton(x) {
	     _(x).style.display = "block";
	 }

	 function checkEmail() {
	     var e = _("email").value;

	     if (e != "") {
	         _("estatus").innerHTML = 'checking...';
	         var ajax = ajaxObj("POST", "/v2/app/dashboard/top");
	         ajax.onreadystatechange = function() {
	             if (ajaxReturn(ajax) == true) {
	                 if (ajax.responseText == '<p style="color:#4CAF50;">This email is valid and available!</p>' || ajax.responseText == '<p style="color:#4CAF50;">Your email will stay the same.</p>') {
	                     _("editBtn").style.display = "block";
	                     _("estatus").innerHTML = ajax.responseText;
	                 } else {
	                     _("estatus").innerHTML = ajax.responseText;
	                     _("editBtn").style.display = "none";
	                 }
	             }
	         }
	         ajax.send("emailcheck=" + e)
	     }
	 }

	 function editAccountInfo() {
	     var e = _("email").value;
	     var c = _("country").value;
	     var g = _("gender").value;

	     if (e == "" || c == "" || g == "") {
	         _("status").innerHTML = "<p style='color:#F44336;'>Fill out all of the form data</p>";
	     } else {
	         _("editBtn").innerHTML = "Loading...";
	         var ajax = ajaxObj("POST", "/v2/app/dashboard/top");
	         ajax.onreadystatechange = function() {
	             if (ajaxReturn(ajax) == true) {
	                 _("editBtn").innerHTML = "Save Changes";
	                 if (ajax.responseText != "account_updated") {
	                     Pleasure.handleToastrSettings( /* closeButton */ false, /*postion */ 'toast-bottom-left', /*sticky*/ false, /*type*/ 'success', /*closeOthers*/ true, /* title */ '', /* notification*/ 'Account successfully updated!');
	                     _("status").innerHTML = "";
	                 }
	             }
	             ajax.send("e=" + e + "&c=" + c + "&g=" + g);
	         }
	     }

	     function editPassword() {
	         var p1 = _("password1").value;
	         var p2 = _("password2").value;
	         if (p1 == "" || p2 == "") {
	             _("status_pass").innerHTML = "<p style='color:#F44336;'>Fill out all of the form data</p>";
	         } else if (p1 != p2) {
	             _("status_pass").innerHTML = "<p style='color:#F44336;'>Your passwords don't match!</p>";
	         } else {
	             _("editPass").innerHTML = "Loading...";
	             var ajax = ajaxObj("POST", "/v2/app/dashboard/top");
	             ajax.onreadystatechange = function() {
	                 if (ajaxReturn(ajax) == true) {
	                     _("editPass").innerHTML = "Change Passwords";
	                     if (ajax.responseText != "password_updated") {

	                         Pleasure.handleToastrSettings( /* closeButton */ false, /*postion */ 'toast-bottom-left', /*sticky*/ false, /*type*/ 'success', /*closeOthers*/ true, /* title */ '', /* notification*/ 'Password successfully updated!');
	                         //alert("Password Updated!");	
	                         _("status_pass").innerHTML = "";
	                         _("password1").value = "";
	                         _("password2").value = "";
	                     }
	                 }
	             }
	             ajax.send("p=" + p1)
	         }
	     }
	 }