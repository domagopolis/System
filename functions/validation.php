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

    $parsed = parse_url( $url );

    if( array_key_exists( 'scheme', $parsed ) AND array_key_exists( 'host', $parsed ) ){
      return TRUE;
    }else{
      return FALSE;
    }
  }
}
?>