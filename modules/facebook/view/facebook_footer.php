<div id="fb-root"></div>
<script>
window.fbAsyncInit = function() {
   FB.init({appId: '<?php echo $this->app_id; ?>', status: true, cookie: true, xfbml: true});
   FB.Event.subscribe("auth.login", function() {location.reload();});
   FB.Event.subscribe("auth.logout", function() {window.location = 'logout.php'});
};
(function() {
   var e = document.createElement('script');
   e.type = 'text/javascript';
   e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
   e.async = true;
   document.getElementById('fb-root').appendChild(e);
}());
</script>
