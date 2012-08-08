<?php
include('define_functions.php');
include('orm.php');

if( is_array( $modules ) ){
   foreach( $modules as $module){
      include($system_root_path.'system/modules/'.$module.'/'.$module.'.php');
      }
   }

$files = array();
if( is_dir( $document_root_path.'include/models' ) ){
   $files = scandir( $document_root_path.'include/models' );
   }

foreach( $files as $file ){
   if( $file != '.' AND $file != '..' ){
      include($document_root_path.'include/models/'.$file);
      }
   }
   
if( !isset( $language_code ) ){
   $language_code = 'en_GB';
   }

$files = array();
if( is_dir( $document_root_path.'i18n/'.$language_code ) ){
   $files = scandir( $document_root_path.'i18n/'.$language_code );
   }

foreach( $files as $file ){
   if( $file != '.' AND $file != '..' ){
      include($document_root_path.'i18n/'.$language_code.'/'.$file);
      }
   }

$jquery_version = 'jquery-1.6.3.min';
$jquery_mobile_version = 'jquery.mobile-1.0.1.min';
?>
