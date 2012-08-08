<?php

/*
Name: twitter.php
Description: Classes to enter and search twitter information.
*/

class twitter{

   private $url;
   private $type;
   private $username;
   private $password;
   
   public $search_params;

   public function __construct( $username=FALSE, $password=FALSE ){
      $this->username = $username;
      $this->password = $password;

      $this->search_params['lang'] = "en";
      $this->search_params['geocode'] = NULL;
      }

   public function __get( $property ){
      if( property_exists( $this, $property ) ){
         return $this->$property;
      }else{
         return FALSE;
         }
      }
      
   public function rpp( $rpp=15 ){
      $this->search_params['rpp'] = $rpp; //int the number of tweets to return per page, max 100
      return $this;
      }
      
   public function geocode($lat, $long, $radius, $units='mi') {
      $this->search_params['geocode'] = $lat.','.$long.','.$radius.$units;
      return $this;
      }
      
   public function set_lang( $iso2 = 'en' ){
      $this->search_params['lang'] = $iso2;
      return $this;
      }
      
   public function search( $query=FALSE ){
      $this->type = 'json';
      $this->url = "http://search.twitter.com/search.".$this->type.'?';
      if( $query ){
         $this->url .= 'q='.urlencode( $query ).'&';
      }else if( $this->username ){
         $this->url .= 'from='.urlencode( $this->username ).'&';
      }else{
         return FALSE;
         }
      
      if( is_array( $this->search_params ) ){
         foreach( $this->search_params as $key => $value ){
            $this->url .= "&".$key."=".$value;
            }
         }

      return ( $results = $this->process() )? $results->results:FALSE;
      }
      
   public function trends(){
      $this->url  = 'http://search.twitter.com/trends.json';
      
      return $this->process();
      }

   public function show_tweets( $count=5 ){
      $this->type = 'xml';
      $this->url = "http://twitter.com/statuses/friends_timeline.".$this->type;
      $this->url .= "?count=".$count;

      return $this->process();
      }
   
   private function process( $postargs=false ){

		$ch = curl_init($this->url);
		
		if($this->postargs !== false){
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $this->postargs);
            }

        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if( !empty($this->username ) AND !empty( $this->password ) ){
            curl_setopt($ch, CURLOPT_USERPWD, $this->username.":".$this->password);
            }

        $response = curl_exec($ch);

        $this->responseInfo=curl_getinfo($ch);
        curl_close($ch);

        if( intval( $this->responseInfo['http_code'] ) == 200 )
			return $this->objectify( $response );
        else
            return FALSE;
      }
      
   private function objectify($data){
      if( $this->type ==  'json' ){
         return (object) json_decode($data);
      }else if( $this->type == 'xml' ){
         $obj = simplexml_load_string( $data );

         $statuses = array();
         foreach( $obj->status as $status ){
            $statuses[] = $status;
            }
         return (object) $statuses;
      }else{
         return false;
         }
      }
      
   public static function format_text( $text ){
      $text = html_entity_decode($text);
      $text = preg_replace("/http:\/\/([A-Za-z0-9_\-.?=&\/]+)/i", '<a href="http://$1" target="_blank">http://$1</a>', $text);
      $text = preg_replace("/@([A-Za-z0-9_-]+)/i", '<a href="http://www.twitter.com/$1" target="_blank">@$1</a>', $text);
      $text = preg_replace("/#([A-Za-z0-9_-]+)/i", '<a href="http://twitter.com/#!/search/%23$1" target="_blank">#$1</a>', $text);

      return $text;
      }
      
   public static function format_time( $time=0 ){

      if( is_string( $time ) ){
         $time = strtotime( $time );
         }

      if( time() - $time < 0 ){
         return 'a few seconds ago';
      }else if( time() - $time < 60 ){
         return ( time() - $time ).' sec'.( ( time() - $time > 1 )?'s':'' ).' ago';
      }else if( time() - $time < 60*60 ){
         return floor( ( time() - $time )/60 ).' min'.( ( floor( ( time() - $time )/60 ) > 1 )?'s':'' ).' ago';
      }else if( time() - $time < 24*60*60 ){
         return floor( ( time() - $time )/60/60 ).' hour'.( ( floor( ( time() - $time )/60/60 ) > 1 )?'s':'' ).' ago';
      }else if( date( "Y", $time ) === date( "Y" ) ){
         return date( "d M", $time );
      }else{
         return date( "d M Y", $time );
         }
      }
   }
?>
