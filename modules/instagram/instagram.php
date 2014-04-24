<?php

/*
Name: instagram.php
Description: Classes to retrieve instagram information.
*/

class instagram{

   private $url;
   private $user_id;
   private $token;
   private $type;
   
   public function __construct( $user_id=NULL, $token=NULL, $type=NULL ){
      $this->user_id = $user_id;
      $this->token = $token;
      if( $type ){
         $this->type = $type;
      }else{
         $this->type = "xml";
         }
      }

   public function __get( $property ){
      if( property_exists( $this, $property ) ){
         return $this->$property;
      }else{
         return FALSE;
         }
      }
      
   public function get_images( $count=10){

      }
   
   private function process( $postargs=false ){
		$ch = curl_init($this->url);
		
		if($this->postargs !== false) {
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $this->postargs);
        }

        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        $this->responseInfo = curl_getinfo($ch);
        curl_close($ch);

        if( intval( $this->responseInfo['http_code'] ) == 200 )
			return $this->objectify( $response );
        else
            return false;
      }
      
   private function objectify($data){
      if( $this->type ==  'json' ){
         return (object) json_decode($data);
      }else if( $this->type == 'xml' ){
         $obj = simplexml_load_string( $data );
         return (object) $obj;
      }else{
         return false;
         }
      }
   }
?>
