<form action="<?php echo $this->action; ?>" method="<?php echo $this->method; ?>" id="<?php echo $this->id; ?>"<?php foreach( $this->options as $key=>$value ){ echo ( is_numeric( $key ) )?" ".$value:' '.$key.'="'.$value.'"'; } ?>>
<?php include( 'form_body.php' ); ?>
</form>
