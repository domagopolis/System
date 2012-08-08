<select id="time_select_hour_<?php echo $input->id; ?>" name="time_select_hour_<?php echo $input->name; ?>"<?php foreach( $input->options as $key=>$value ){ echo ( is_numeric( $key ) )?' '.$value:' '.$key.'="time_select_hour_'.$value.'"'; } ?>>
<?php for( $i=1; $i<=12; $i++ ){ ?>
<option value="<?php echo str_pad( $i, 2, '0', STR_PAD_LEFT ); ?>"<?php if( ( $input->orm_object AND $input->orm_object->{$input->name} === (string)$i ) OR ( !$input->orm_object AND (integer)date( 'g', $input->default_value ) === $i ) ){ ?> selected="selected"<?php } ?>><?php echo str_pad( $i, 2, '0', STR_PAD_LEFT ); ?></option>
<?php } ?>
</select>
<select id="time_select_minute_<?php echo $input->id; ?>" name="time_select_minute_<?php echo $input->name; ?>"<?php foreach( $input->options as $key=>$value ){ echo ( is_numeric( $key ) )?' '.$value:' '.$key.'="time_select_minute_'.$value.'"'; } ?>>
<?php for( $i=0; $i<60; $i+=5 ){ ?>
<option value="<?php echo str_pad( $i, 2, '0', STR_PAD_LEFT ); ?>"<?php if( ( $input->orm_object AND $input->orm_object->time_select_minute_{$input->name} === str_pad( $i, 2, '0', STR_PAD_LEFT ) ) OR ( !$input->orm_object AND ( (integer)date( 'i', $input->default_value ) - (integer)date( 'i', $input->default_value )%5 ) === $i ) ){ ?> selected="selected"<?php } ?>><?php echo str_pad( $i, 2, '0', STR_PAD_LEFT ); ?></option>
<?php } ?>
</select>
<select id="time_select_ampm_<?php echo $input->id; ?>" name="time_select_ampm_<?php echo $input->name; ?>"<?php foreach( $input->options as $key=>$value ){ echo ( is_numeric( $key ) )?' '.$value:' '.$key.'="time_select_ampm_'.$value.'"'; } ?>>
<option value="am"<?php if( ( $input->orm_object AND $input->orm_object->time_select_ampm_{$input->name} === 'am' ) OR ( !$input->orm_object AND date( 'a', $input->default_value ) === 'am' ) ){ ?> selected="selected"<?php } ?>>am</option>
<option value="pm"<?php if( ( $input->orm_object AND $input->orm_object->time_select_ampm_{$input->name} === 'pm' ) OR ( !$input->orm_object AND date( 'a', $input->default_value ) === 'pm' ) ){ ?> selected="selected"<?php } ?>>pm</option>
</select>
