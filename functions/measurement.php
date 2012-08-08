<?php

/*
Name: measurement.php
Description: Classes for common measurement conversion.
*/

class measure{

   public static function get_unit( $meaurement='metric', $measuring='distance' ){

      $units = array(
         'metric' => array(
            'distance' => 'km',
            ),
         'imperial' => array(
            'distance' => 'mi',
            ),
         );

      return $units[$meaurement][$measuring];
         
      }

   public static function convert( $username=NULL ){

      }
   }
?>
