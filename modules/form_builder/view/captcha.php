<?php 
global $domain;
global $session_id;
$_SESSION['extids'] = array();
$ext = md5( uniqid( mt_rand(), true ) );
$_SESSION['extids'][$ext] = 1;
?>
<img src="<?php echo $domain; ?>modules/form_builder/view/captcha_image.php?session_id=<?php echo session_id().'-'.$ext; ?>" class="captcha-image" />
<input type="text" name="captcha" value="" placeholder="Enter Code Above" autocomplete="off" />
<?php if( array_key_exists( 'captcha', $_POST ) ){ ?>
<?php if( empty( $_POST['captcha'] ) ) { ?>
<p class="error">Code not entered</p>
<?php }else if( $_POST['captcha'] !== $_SESSION['captcha'] ){ ?>
<p class="error">Code does not match</p>
<?php } ?>
<?php } ?>