<?php

/*
Name: validation.php
Description: Classes for common validation functions.
*/

class validation{

   public static function validate_email( $email=NULL ){

      $regex = '/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';

      if ( preg_match( $regex, $email ) ){
         return true;
      }else{
         return false;
         }
         
      }

   public static function validate_username( $username=NULL ){

      $regex = '/^([.0-9a-z_-]+)$/i';

      if ( preg_match( $regex, $username ) ){
         return true;
      }else{
         return false;
         }

      }
      
   public static function validate_url( $url=NULL ){

        $regex = '/^(https?:\/\/)'
        ."?(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?" //user@
        ."(([0-9]{1,3}\.){3}[0-9]{1,3}" // IP- 199.194.52.184
        ."|" // allows either IP or domain
        ."([0-9a-z_!~*'()-]+\.)*" // tertiary domain(s)- www.
        ."([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\." // second level domain
        ."[a-z]{2,6})" // first level domain- .com or .museum
        ."(:[0-9]{1,4})?" // port number- :80
        ."((\/?)|" // a slash isn't required if there is no file name
        ."(\/[0-9a-z_!~*'().;?:@&=+$,%#-]+)+\/?)$/i";

      if ( preg_match( $regex, $url ) ){
         return true;
      }else{
         return false;
         }
      }
   }
?>
