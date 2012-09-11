<?php

/*
Name: campaign_monitor.php
Description: API to send and receive Campaign Monitor data.
*/

class campaign_monitor{

   private $url;
   private $api_key;
   private $type;
   
   public function __construct( $api_key=FALSE, $type='json' ){
      if( !$api_key ){
         return FALSE;
         }
   
      $this->url = 'http://api.createsend.com/api/v3/';
      $this->api_key = $api_key;
      $this->type = $type;
      
      return $this;
      }

   public function __get( $property ){
      if( property_exists( $this, $property ) ){
         return $this->$property;
      }else{
         return FALSE;
         }
      }
   
   private function process( $custom_request='GET', $request=FALSE ){
		$ch = curl_init($this->url.$this->path.'.'.$this->type);
		
        curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
        curl_setopt( $ch, CURLOPT_NOBODY, 0 );
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 15 );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
         
        if( $custom_request ){
           if( $custom_request === 'PUT' ){
              curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Length: '.strlen( $request ) ) );
              }
           curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $custom_request );
           }
        if( $request ) {
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $request);
        }else{
            curl_setopt( $ch, CURLOPT_POST, 0 );
            }
        if( $this->api_key ){
           curl_setopt( $ch, CURLOPT_USERPWD, $this->api_key.':' );
           }

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
         //libxml_use_internal_errors( TRUE );
         $obj = simplexml_load_string( $data );
         return (object) $obj;
      }else{
         return false;
         }
      }
      
   private function parse_request( $a_request=array() ){
      $request = FALSE;
      
      if( $this->type === 'json' ){
         $request = json_encode( $a_request ) ;
      }else if( $this->type === 'xml' ){
         $xml = new SimpleXMLElement( '<List/>' );
         array_walk( $a_request, array( $xml, 'addChild' ) );
         $request = $xml->asXML();
         }

      return $request;
      }
   }
?>
