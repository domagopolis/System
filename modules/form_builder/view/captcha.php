<?php global $domain; ?>
<img src="<?php echo $domain; ?>/system/modules/form_builder/view/captcha_image.php" class="captcha-image" />
<input type="text" name="captcha" value="" placeholder="Enter Code Above" autocomplete="off" />
<?php if( array_key_exists( 'captcha', $_POST ) ){ ?>
<?php if( empty( $_POST['captcha'] ) ) { ?>
<p class="error">Code not entered</p>
<?php }else if( $_POST['captcha'] !== $_SESSION['captcha'] ){ ?>
<p class="error">Code does not match</p>
<?php } ?>
<?php } ?>