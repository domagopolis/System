<textarea id="<?php echo $input->id; ?>" name="<?php echo $input->name; ?>"<?php foreach( $input->options as $key=>$value ){ echo ( is_numeric( $key ) )?' '.$value:' '.$key.'="'.$value.'"'; } ?>>
<?php echo ( $input->orm_object AND $input->orm_object->{$input->name} !== NULL )?form_format( $input->format, $input->orm_object->{$input->name} ):$input->default_value; ?>
</textarea>
