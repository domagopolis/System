<?php

/*
Name: url.php
Description: Classes for common url functions.
*/

class url{

   private $url;

   public function __construct( $url=NULL ){
      $this->url = $url;
      }

   public static function url_encode_name( $name=NULL ){

      //Try preg_replace( '/[^A-Za-z0-9-]+/', '-', strtolower( $name ) );
      $name = strtolower( $name );
      $name = str_replace( "-", "_", $name );
      $name = str_replace( " ", "-", $name );
      $name = str_replace( "&amp;", "and", $name );
      $name = str_replace( "&", "and", $name );
      $name = urlencode( $name );


      return $name;
      }

   public static function url_decode_name( $param=NULL ){

      $param = urldecode( $param );
      $param = str_replace( "-and-", "-&amp;-", $param );
      $param = str_replace( "-", " ", $param );
      $param = str_replace( "_", "-", $param );

      return $param;
      }
   }
?>
