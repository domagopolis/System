<?php

/*
Name: date.php
Description: Classes for formated of date.
*/

class date_format{

   public static function formated_strtotime( $str = FALSE, $format='m/d/Y' ){
      return strtotime( date_format( date_create( str_replace( "/", "-", $str ) ), $format ) );
      }
   }
?>
