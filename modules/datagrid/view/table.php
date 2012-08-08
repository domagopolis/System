<?php if( array_key_exists( "add-record", $this->options ) ){ ?>
<form class="datagrid-add-record" id="datagrid-add-record" name="datagrid_add_record" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<button type="submit" name="add" value="add"><?php echo $this->options['add-record']; ?></button>
</form>
<?php } ?>

<?php
   if ( $this->orm_objects->{$this->orm_objects->object_data_arr} ){

$record_id_arr = array();
$datagrid_table_errors = array();
foreach( $this->orm_objects->{$this->orm_objects->object_data_arr} as $object_item ){
   $record_id_arr[$object_item->{$object_item->id_field}] = $object_item->{$object_item->id_field};
   if( $object_item->error ){
      $datagrid_table_errors[] = $object_item->error;
      }
   }
?>
<?php if( array_key_exists( "table_form_submit", $this->options ) ){ ?>
<form class="datagrid-table-form" id="datagrid_form" name="form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<?php } ?>

<?php foreach( $datagrid_table_errors as $datagrid_table_error ){ ?>
<p class="error"><?php echo $datagrid_table_error; ?></p>
<?php } ?>

<?php $pagination->display_pagination(); ?>

<table class='datagrid-table'>
<?php
$table_heading_displayed = FALSE;
$cols = 0;
if( array_key_exists( 'totals',  $this->options ) ){
   foreach( $this->options['totals'] as $total ){
      $col_total[$total] = 0;
      }
   }
   
foreach( $this->orm_objects->{$this->orm_objects->object_data_arr} as $object_item ){
   if( !$table_heading_displayed ){
?>
<tr>
<?php
foreach( $object_item->selected_fields as $field_heading ){
   if( $object_item->id_field !== $field_heading AND preg_match( '/([A-Za-z0-9]+)(_id)\Z/i', $field_heading ) ){
      $table_heading = preg_replace( '/([A-Za-z0-9]+)(_id)\Z/i', '$1', $field_heading );
      //Get relations with the plural of table heading
      $this->orm_objects->{text::plural( $table_heading )};
   }else{
      $table_heading = $field_heading;
   }
   ?>
<?php if( $field_heading === $object_item->id_field ){ ?>
<?php if( array_key_exists( "select", $this->options ) ){ $cols++; ?>
<th>Select</th>
<?php } ?>
<?php if( array_key_exists( "update-record", $this->options ) AND is_array( $this->options['update-record'] ) ){ ?>
<?php foreach( $this->options['update-record'] as $key => $value ){ $cols++; ?>
<th><?php echo ucwords( $key ) ?></th>
<?php } ?>
<?php } ?>
<?php }else{ $cols++; ?>
<th><?php echo ucwords( str_replace( "_", " ", $table_heading ) ); ?></th>
<?php } ?>
<?php
   }
   $table_heading_displayed = TRUE;
?>
</tr>
<?php } ?>
<?php ( empty( $table_row_class ) OR $table_row_class === "even" )?$table_row_class = "odd":$table_row_class = "even";?>
<?php if( $object_item->error ){ ?>
<tr class="error">
<?php }else{ ?>
<tr class="<?php echo $table_row_class; ?>">
<?php } ?>
<?php
foreach( $object_item->selected_fields as $field_heading ){

   if( array_key_exists( 'totals',  $this->options ) ){
      foreach( $this->options['totals'] as $total ){
         if( $total === $field_heading ){
            $col_total[$total] += $object_item->$field_heading;
            }
         }
      }
?>
<?php if( $object_item->$field_heading === '' ){ //If property is empty ?>
<td>&nbsp;</td>
<?php }else if( $field_heading === $object_item->id_field ){ //If property is object id ?>
<?php if( array_key_exists( "select", $this->options ) ){ //If select checkbox required ?>
<td align="center">
<input type=checkbox id="selectGroup" name="select[<?php echo $object_item->$field_heading; ?>]" value="yes"<?php if(FALSE){ echo " checked"; } ?>>
</td>
<?php } ?>
<?php if( array_key_exists( "update-record", $this->options ) AND is_array( $this->options['update-record'] ) ){ //If update buttons for each record included ?>
<?php foreach( $this->options['update-record'] as $key => $value ){ ?>
<td>
<form class="<?php echo str_replace( " ", "_", $key ); ?>_record" id="<?php echo str_replace( " ", "_", $key ); ?>_record" name="form" method="<?php echo $value; ?>" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<input type="hidden" name="<?php echo $field_heading?>" value="<?php echo $object_item->$field_heading; ?>" />
<button type="submit" name="<?php echo str_replace( " ", "_", $key ); ?>" value="<?php echo ucwords( $key ); ?>"><?php echo ucwords( $key ); ?></button>
</form>
</td>
<?php } ?>
<?php } ?>
<?php }else if( preg_match( '/([A-Za-z0-9]+)(_id)\Z/i', $field_heading ) ){ //If property is a relation with an id ?>
<td>
<?php if( array_key_exists( "record-link", $this->options ) AND array_key_exists( $field_heading, $this->options['record-link'] ) ){ //If link is included to link to this relation ?>
<a href="<?php echo $this->options['record-link'][$field_heading]; ?>?<?php echo $field_heading; ?>=<?php echo $object_item->$field_heading; ?>">
<?php } ?>
<?php if( $object_item->{preg_replace( '/([A-Za-z0-9]+)(_id)\Z/i', '$1', $field_heading )."_arr"} ){ ?>
<?php echo $object_item->{preg_replace( '/([A-Za-z0-9]+)(_id)\Z/i', '$1', $field_heading )."_arr"}[$object_item->$field_heading]->identifier; ?>
<?php } ?>
<?php if( array_key_exists( "record-link", $this->options ) AND array_key_exists( $field_heading, $this->options['record-link'] ) ){ ?>
</a>
<?php } ?>
</td>
<?php }else if( array_key_exists( "format", $this->options ) AND array_key_exists( $field_heading, $this->options['format'] ) ){ //If a format function exists for this property ?>
<td><?php echo datagrid_format( $this->options['format'][$field_heading], $object_item->$field_heading ); ?></td>
<?php }else if( array_key_exists( "record-link", $this->options ) AND array_key_exists( $field_heading, $this->options['record-link'] ) ){ //If a link needs to be attached to this field ?>
<td><a href="<?php echo $this->options['record-link'][$field_heading]; ?>?<?php echo $object_item->id_field; ?>=<?php echo $object_item->{$object_item->id_field}; ?>"><?php echo $object_item->$field_heading; ?></a></td>
<?php }else if( array_key_exists( "editable-fields", $this->options ) AND array_key_exists( $field_heading, $this->options['editable-fields'] ) ){ //If this property is editable ?>
<td>
<form class="<?php echo $this->options['editable-fields'][$field_heading]; ?>_<?php echo $field_heading; ?>" id="<?php echo $this->options['editable-fields'][$field_heading]; ?>_<?php echo $field_heading; ?>" name="form" method="post" action="<?php if( array_key_exists( "search", $_GET ) ){ echo $_SERVER['REQUEST_URI']; }else{ echo $_SERVER['SCRIPT_NAME']."?";?><?php foreach( $record_id_arr as $key => $value ){ echo $object_item->id_field."[field_in][".$value."]=".$value."&"; } ?>search=Search<?php } ?>">
<input type="hidden" name="<?php echo $object_item->id_field; ?>" value="<?php echo $object_item->{$object_item->id_field}; ?>" />
<input type="text" name="<?php echo $field_heading; ?>" value="<?php echo $object_item->$field_heading; ?>"/>
<button type="submit" name="<?php echo $this->options['editable-fields'][$field_heading]; ?>_<?php echo $field_heading; ?>" value="<?php echo str_replace( "_", " ", $this->options['editable-fields'][$field_heading] ); ?>"><?php echo str_replace( "_", " ", $this->options['editable-fields'][$field_heading] ); ?></button>
</form>
</td>
<?php }else{ //Default output this property ?>
<td><?php echo $object_item->$field_heading; ?></td>
<?php } ?>
<?php } ?>
</tr>
<?php } ?>
<?php if( array_key_exists( 'totals',  $this->options ) ){ ?>
<?php foreach( $this->options['totals'] as $key => $total ){ ?>
<?php ( empty( $table_row_class ) OR $table_row_class === "even" )?$table_row_class = "odd":$table_row_class = "even";?>
<tr class="<?php echo $table_row_class; ?>">
<td colspan="<?php echo $cols-1; ?>"><?php echo $total; ?></td>
<td><?php echo ( array_key_exists( "format", $this->options ) AND array_key_exists( $total, $this->options['format'] ) )?$col_total[$total]:$col_total[$total]; ?></td>
</tr>
<?php } ?>
<?php } ?>
</table>
<?php if( array_key_exists( "table_form_submit", $this->options ) ){ ?>
<?php foreach ( $this->options['table_form_submit'] as $key => $value ){ ?>
<button type="submit" name="<?php echo $key; ?>" value="<?php echo $value; ?>"><?php echo $value; ?></button>
<?php } ?>
</form>
<?php } ?>
<?php }else if( array_key_exists( "search",  $_GET ) ){ ?>
<p>No Results found</p>
<?php } ?>
