<?php

/*
Name: foursquare.php
Description: Classes to retrieve foursquare API information.
*/

include("foursquare_model.php");

class foursquare{

   private $url;
   private $client_id;
   private $client_secret;
   private $access_token;
   private $type;
   private $result;

   public $venue;
   public $venues;
   
   public function __construct( $client_id=NULL, $client_secret=NULL, $type=NULL ){
      $this->client_id = $client_id;
      $this->client_secret = $client_secret;
      $this->url = "https://api.foursquare.com/v2/";

      $this->venue = false;
      $this->venues = array();

      if( $type ){
         $this->type = $type;
      }else{
         $this->type = "json";
         }
      }

   public function __get( $property ){
      if( property_exists( $this, $property ) ){
         return $this->$property;
      }else{
         return FALSE;
         }
      }
      
   public function find_venues( $lat=FALSE, $lng=FALSE, $options=array() ){
      $this->url .= "venues/search?ll=".$lat.",".$lng."&client_id=".$this->client_id."&client_secret=".$this->client_secret."&v=".date('Ymd');
         
      foreach( $options as $key => $option ){
        if( is_array( $option ) ){
          $this->url .= '&'.$key.'='.implode(',', $option );
        }else{
          $this->url .= '&'.$key.'='.$option;
        }
      }

      $this->result = $this->process();

      foreach( $this->result->response->venues as $venue ){
        $this->venues[] = new foursquare_venue( $venue );
      }

      return TRUE;
      }

  public function get_venue( $venue_id=FALSE ){
      $this->url .= "venues/".$venue_id."?client_id=".$this->client_id."&client_secret=".$this->client_secret."&v=".date('Ymd');
 
      $this->result = $this->process();

      $this->venue = new foursquare_venue( $this->result->response->venue );

      return TRUE;
      }
      
   // Calculate the distance between two coords in lat/long as the crow flies
   public function calculate_distance( $lat1=FALSE, $long1=FALSE, $lat2=FALSE, $long2=FALSE ){
      $R = 6371; // km
      $dLat = deg2rad( $lat2 - $lat1 );
      $dLon = deg2rad( $lon2 - $lon1 );
      
      $a = sin( $dLat/2 ) * sin( $dLat/2 ) +
        cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) *
        sin( $dLon/2 ) * sin( $dLon/2 );
      $c = 2 * atan2( sqrt( $a ), sqrt( 1-$a ) );
      $d = $R * $c;
      
      return $d;
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
  public function auth(){
    $url = "https://foursquare.com/oauth2/access_token";
    $url .= "?client_id=".$this->client_id;
    $url .= "&client_secret=".$this->client_secret;
    $url .= "&grant_type=authorization_code";
    $url .= "&redirect_uri=YOUR_REGISTERED_REDIRECT_URI";
    $url .= "&code=CODE";

    var_dump($url);
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