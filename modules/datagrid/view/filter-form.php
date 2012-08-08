<?php
if( array_key_exists( "form_heading", $this->options ) ){
   $form_name = str_replace( " ", "_", strtolower( $this->options['form_heading'] ) );
}else{
   $form_name = "";
   }
?>
<?php if( array_key_exists( "searchable_fields", $this->options ) ){ ?>
<form class="datagrid-filter-form" id="<?php if( !empty( $form_name ) ){ echo $form_name;} ?>" name="<?php if( !empty( $form_name ) ){ echo $form_name;} ?>" method="get" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>">
<?php if( array_key_exists( "form_heading", $this->options ) ){ ?>
<h2><?php echo $this->options['form_heading']; ?></h2>
<?php } ?>
<input type="hidden" name="filter" value="<?php echo $form_name; ?>">
<ul>
<?php foreach( $this->options['searchable_fields'] as $field => $label ){ ?>
<li><label for="<?php if( !empty( $form_name ) ){ echo $form_name."_"; } ?><?php echo preg_match( '/([A-Za-z0-9]+)(\[([A-Za-z0-9]+)\])\Z/i', $field )?preg_replace( '/([A-Za-z0-9]+)(\[([A-Za-z0-9]+)\])\Z/i', '$1', $field ):$field; ?>_field"><?php echo $label; ?></label>
<?php if( preg_match( '/([A-Za-z0-9]+)(\[range\])\Z/i', $field ) ){ ?>
<?php $field = preg_replace( '/([A-Za-z0-9]+)(\[range\])\Z/i', '$1', $field ); ?>
<input type="text" class="<?php if( !empty( $form_name ) ){ echo $form_name."_"; } ?><?php echo $field; ?>_field_range_low" id="<?php if( !empty( $form_name ) ){ echo $form_name."_"; } ?><?php echo $field; ?>_field_range_low" name="<?php echo $field; ?>[field_range_low]" value="<?php if( array_key_exists( "filter", $_GET ) AND ( empty( $_GET['filter'] ) OR $_GET['filter'] === $form_name ) AND array_key_exists( $field, $_GET ) AND array_key_exists( "field_range_low", $_GET[$field] ) ){ echo $_GET[$field]['field_range_low']; } ?>">
<label for="<?php if( !empty( $form_name ) ){ echo $form_name."_"; } ?><?php echo $field; ?>_field_range_high">to</label>
<input type="text" class="<?php if( !empty( $form_name ) ){ echo $form_name."_"; } ?><?php echo $field; ?>_field_range_high" id="<?php if( !empty( $form_name ) ){ echo $form_name."_"; } ?><?php echo $field; ?>_field_range_high" name="<?php echo $field; ?>[field_range_high]" value="<?php if( array_key_exists( "filter", $_GET ) AND ( empty( $_GET['filter'] ) OR $_GET['filter'] === $form_name ) AND array_key_exists( $field, $_GET ) AND array_key_exists( "field_range_high", $_GET[$field] ) ){ echo $_GET[$field]['field_range_high']; } ?>">
<?php }else if( preg_match( '/([A-Za-z0-9_-]+)(_id)\Z/i', $field ) ){ ?>
<?php
$field = preg_replace( '/([A-Za-z0-9_-]+)(_id)\Z/i', '$1', $field );
$field_class = new $field;
$field_class->find_all();
if( sizeof( $field_class->{$field_class->object_data_arr} ) > 0 ){
?>
<select name="<?php echo $field."_id[field]"; ?>">
<option value""></option>
<?php foreach( $field_class->{$field_class->object_data_arr} as $field_class_obj ){ ?>
<option value="<?php echo $field_class_obj->{$field_class_obj->id_field}; ?>"<?php if( array_key_exists( "filter", $_GET ) AND ( empty( $_GET['filter'] ) OR $_GET['filter'] === $form_name ) AND array_key_exists( $field."_id", $_GET ) AND array_key_exists( "field", $_GET[$field."_id"] ) AND $_GET[$field."_id"]["field"] === $field_class_obj->{$field_class_obj->id_field} ){ echo " selected"; } ?>><?php echo $field_class_obj->identifier; ?></option>
<?php } ?>
</select>
<?php } ?>
<?php }else{ ?>
<input type="text" class="<?php if( !empty( $form_name ) ){ echo $form_name."_"; } ?><?php echo $field; ?>_field" id="<?php if( !empty( $form_name ) ){ echo $form_name."_"; } ?><?php echo $field; ?>_field" name="<?php echo $field; ?>[field]" value="<?php if( array_key_exists( "filter", $_GET ) AND ( empty( $_GET['filter'] ) OR $_GET['filter'] === $form_name ) AND array_key_exists( $field, $_GET ) AND array_key_exists( "field", $_GET[$field] ) ){ echo $_GET[$field]['field']; } ?>">
<?php } ?>
</li>
<?php } ?>
<?php if( array_key_exists( "search", $this->options ) ){ ?>
<li><button type="submit" name="search" value="search"><?php echo $this->options['search']; ?></button></li>
<?php } ?>
<?php if( array_key_exists( "show_all", $this->options ) ){ ?>
<li><button type="submit" name="show_all" value="show_all"><?php echo $this->options['show_all']; ?></button></li>
<?php } ?>
</ul>
</form>
<?php } ?>
