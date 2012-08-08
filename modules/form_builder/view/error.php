<?php if( $input->orm_object AND $input->orm_object->{$input->name.'_error_field'} ){ ?>
<p class="error"><?php echo $input->orm_object->{$input->name.'_error_field'}; ?></p>
<?php } ?>
