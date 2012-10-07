<?php if( sizeof( $input->select_values ) > 0 ){ ?>
<select id="<?php echo $input->id; ?>" name="<?php echo $input->name; ?><?php if( in_array( 'multiple', $input->options ) ){ echo '[]'; } ?>"<?php foreach( $input->options as $key=>$value ){ echo ( is_numeric( $key ) )?' '.$value:' '.$key.'="'.$value.'"'; } ?>>
<?php foreach( $input->select_values as $key => $value ){ ?>
<?php if( $input->orm_object AND is_string( $input->orm_object->{$input->name} ) AND $input->orm_object->{$input->name} === (string)$key ){ ?>
<?php $selected = TRUE; ?>
<?php }else if( $input->orm_object AND is_array( $input->orm_object->{$input->name} ) AND in_array( (string)$key, $input->orm_object->{$input->name} ) ){ ?>
<?php $selected = TRUE; ?>
<?php }else if( (string)$input->default_value === (string)$key ){ ?>
<?php $selected = TRUE; ?>
<?php }else{ ?>
<?php $selected = FALSE; ?>
<?php } ?>
<option value="<?php echo $key; ?>"<?php if( $selected ){ ?> selected="selected"<?php } ?>><?php echo $value; ?></option>
<?php } ?>
</select>
<?php } ?>
