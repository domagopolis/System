<?php foreach( $input->select_values as $select_key => $select_value ){ ?>
<input type="radio" id="<?php echo $input->id; ?>" name="<?php echo $input->name; ?>" value="<?php echo $select_key; ?>"<?php foreach( $input->options as $key=>$value ){ echo ( is_numeric( $key ) )?" ".$value:" ".$key."=".$value; } ?><?php if( $input->orm_object AND $input->orm_object->{$input->name} === (string)$select_key ){ echo " checked"; } ?>><?php echo $select_value; ?><br>
<?php } ?>
