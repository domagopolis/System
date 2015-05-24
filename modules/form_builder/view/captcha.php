<?php global $domain; ?>
<img src="<?php echo $domain; ?>/system/modules/form_builder/view/captcha_image.php"/>
<input type="text" name="captcha-text" value="" placeholder="Enter Code Above" autocomplete="off" />
<?php if( array_key_exists( 'captcha-text', $_POST ) ){ ?>
<?php if( empty( $_POST['captcha-text'] ) ) { ?>
<p class="error">Code not entered</p>
<?php }else if( $_POST['captcha-text'] !== $_SESSION['captcha'] ){ ?>
<p class="error">Code does not match</p>
<?php } ?>
<?php } ?>