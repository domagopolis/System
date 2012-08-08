<?php foreach( $this->og_data as $key => $value ){ ?>
<?php if( !empty( $value ) ) { ?>
<meta property="og:<?php echo $key ?>" content="<?php echo $value; ?>" />
<?php } ?>
<?php } ?>
<meta property="fb:app_id" content="<?php echo $this->app_id; ?>" />
