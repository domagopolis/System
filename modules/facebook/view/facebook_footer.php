<div id="fb-root"></div>
<script>
window.fbAsyncInit = function() {
	FB.init({appId: '<?php echo $this->app_id; ?>', cookie: true, xfbml: true, version: 'v2.1'});
};
(function() {
   var e = document.createElement('script');
   e.type = 'text/javascript';
   e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
   e.async = true;
   document.getElementById('fb-root').appendChild(e);
}());

function checkLoginState() {
	FB.getLoginStatus(function(response) {
		statusChangeCallback(response);
	});
}

function statusChangeCallback(response) {
	if (response.status === 'connected') {
		location.reload();
	} else if (response.status === 'not_authorized') {
		window.location = '';
	} else {
		window.location = 'logout.php';
	}
}
</script>
