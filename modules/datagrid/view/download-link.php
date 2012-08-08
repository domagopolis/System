<?php if ( $this->orm_objects_pagination->{$this->orm_objects_pagination->object_data_arr} ){ ?>
<?php if ( array_key_exists( "download_document_path", $this->options ) AND $download_file_fp = fopen( $this->options['download_document_path'].$this->filename, "w" ) ){ ?>
<p><a href="<?php echo $this->options['download_url'].$this->filename; ?>">Download as csv file</a></p>
<?php } ?>
<?php } ?>

<?php

$download_file_fp = FALSE;
if( $this->orm_objects_pagination->{$this->orm_objects_pagination->object_data_arr} ){
   if( array_key_exists( "download_document_path", $this->options ) ){
      $download_file_fp = fopen( $this->options['download_document_path'].$this->filename, "w" );
      }
   }

$table_heading_displayed = FALSE;
foreach( $this->orm_objects_pagination->{$this->orm_objects_pagination->object_data_arr} as $object_item ){
   $line = "";
   if( !$table_heading_displayed ){
      foreach( $object_item->selected_fields as $field_heading ){
         if( $object_item->id_field !== $field_heading AND preg_match( '/([A-Za-z0-9]+)(_id)/i', $field_heading ) ){
            $table_heading = preg_replace( '/([A-Za-z0-9]+)(_id)/i', '$1', $field_heading );
            $this->orm_objects_pagination->{text::plural( $table_heading )};
         }else{
            $table_heading = $field_heading;
            }

         if( array_key_exists( "editable-fields", $this->options ) AND !array_key_exists( $table_heading, $this->options['editable-fields'] ) ){
            $line .= ucwords( str_replace( "_", " ", $table_heading ) ).",";
            }
         }
      
      if( $download_file_fp ){
         // write heading line to the CSV file
         fputs($download_file_fp, $line."\n");
         }
      $line = "";
      $table_heading_displayed = TRUE;
      }
      
   foreach( $object_item->selected_fields as $field_heading ){
      if( $object_item->$field_heading === '' ){ //If property is empty
         $line .= " ,";
      }else if( preg_match( '/([A-Za-z0-9]+)(_id)/i', $field_heading ) AND $field_heading !== $object_item->id_field ){ //If property is a relation with an id
         $line .= $object_item->{preg_replace( '/([A-Za-z0-9]+)(_id)/i', '$1', $field_heading )."_arr"}[$object_item->$field_heading]->identifier.",";
      }else if( array_key_exists( "format", $this->options ) AND array_key_exists( $field_heading, $this->options['format'] ) ){ //If a format function exists for this property
         $line .= datagrid_format( $this->options['format'][$field_heading], $object_item->$field_heading ).",";
      }else if( array_key_exists( "record-link", $this->options ) AND array_key_exists( $field_heading, $this->options['record-link'] ) ){ //If a link needs to be attached to this field
         $line .= $object_item->$field_heading.",";
      }else{ //Default output this property
         $line .= $object_item->$field_heading.",";
         }
      }
   if( $download_file_fp ){
      // write line to the CSV file
      fputs($download_file_fp, $line."\n");
      }
   }

if( $download_file_fp ){
   // close the file
   fclose( $download_file_fp );
   }
?>
