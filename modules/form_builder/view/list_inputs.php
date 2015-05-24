<?php
GLOBAL $system_root_path;
if( $input->type !== 'html' ){
   include( 'start_list_tag.php' );
   }
      
if( $input->type !== 'hidden' AND $input->type !== 'html' AND $input->type !== 'button' AND $input->type !== 'captcha' AND !( $input->type === 'select' AND sizeof( $input->select_values ) === 0 ) ){
   include( 'label.php' );
   }

if( file_exists( $system_root_path.'system/modules/form_builder/view/'.$input->type.'.php' ) ){
   include( $input->type.'.php' );
}else{
   $input->custom_type = $input->type;
   include( 'dynamic.php' );
   }

if( $input->type !== 'hidden' AND $input->type !== 'button' ){
   include( 'error.php' );
   }
      
if( $input->type !== 'html' ){
   include( 'end_list_tag.php' );
   }
?>
