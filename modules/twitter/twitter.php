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

   private $oauth_access_token;
   private $oauth_access_token_secret;
   private $consumer_key;
   private $consumer_secret;
   
   public $params;

   public function __construct( $oauth_access_token=FALSE, $oauth_access_token_secret=FALSE, $consumer_key=FALSE, $consumer_secret=FALSE ){
      $this->oauth_access_token = $oauth_access_token;
      $this->oauth_access_token_secret = $oauth_access_token_secret;
      $this->consumer_key = $consumer_key;
      $this->consumer_secret = $consumer_secret;

      $this->url = 'https://api.twitter.com/1.1/';

      $this->params['lang'] = "en";
      $this->params['geocode'] = NULL;
      }

   public function __get( $property ){
      if( property_exists( $this, $property ) ){
         return $this->$property;
      }else{
         return FALSE;
         }
      }

    public function login( $username=FALSE, $password=FALSE ){
      $this->username = $username;
      $this->password = $password;
    }
      
   public function rpp( $count=15 ){
      $this->params['count'] = $count; //int the number of tweets to return per page, max 100
      return $this;
      }
      
   public function geocode($lat, $long, $radius, $units='mi') {
      $this->params['geocode'] = $lat.','.$long.','.$radius.$units;
      return $this;
      }
      
   public function set_lang( $iso2 = 'en' ){
      $this->params['lang'] = $iso2;
      return $this;
      }
      
   public function search( $query=FALSE ){
      $this->type = 'json';
      $this->url .= "search/tweets.".$this->type;
      if( $query ){
         $this->params['q'] = urlencode( $query );
      }else if( $this->username ){
         $this->params['from'] = urlencode( $this->username );
      }else{
         return FALSE;
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

  private function append_params(){
    $params_str = '';

    if( is_array( $this->params ) ){
       foreach( $this->params as $key => $value ){
          if( strstr( $params_str, '?' ) ){
            $params_str .= "&".$key."=".$value;
          }else{
            $params_str = "?".$key."=".$value;
            }
          }
       }

    return $params_str;
  }

  private function build_base_string( $method, $params ){
    $r = array();
    ksort( $params );
    foreach( $params as $key=>$value ){
      $r[] = "$key=".rawurlencode( $value );
    }
    return $method.'&'.rawurlencode( $this->url ).'&'.rawurlencode( implode( '&', $r ) );
  }

  private function build_authorization_header( $oauth ){
    $r = 'Authorization: OAuth ';
    $values = array();
    foreach( $oauth as $key => $value ){
      $values[] = "$key=\"".rawurlencode( $value )."\"";
    }
    $r .= implode( ', ', $values );
    return $r;
  }
   
   private function process( $postargs=false ){

    $oauth = array(
      'oauth_consumer_key' => $this->consumer_key,
      'oauth_nonce' => time(),
      'oauth_signiture_method' => 'HMAC-SHA1',
      'oauth_token' => $this->oauth_access_token,
      'oauth_timestamp' => time(),
      'oauth_version' => '1.0',
      );

    $base_info = $this->build_base_string( 'GET', $oauth );
    $composite_key = rawurlencode( $this->consumer_secret ).'&'.rawurlencode( $this->oauth_access_token_secret );
    $oauth_signiture = base64_encode( hash_hmac( 'sha1', $base_info, $composite_key, true ) );
    $oauth['oauth_signiture'] = $oauth_signiture;

    $header = array( $this->build_authorization_header( $oauth ), 'Expect:' );
    $options = array(
      'CURLOPT_HTTPHEADER' => $header,
      'CURLOPT_HEADER' => false,
      'CURLOPT_URL' => $this->url.$this->append_params(),
      'CURLOPT_RETURNTRANSFER' => true,
      //'CURLOPT_SSL_VERIFYPEER' => false,
      );

    if($this->postargs !== false){
      $options['CURLOPT_POSTFIELDS'] = $this->postargs;
    }

    $ch = curl_init();
    
    curl_setopt_array( $ch, $options );

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
      
   public static function link_account( $username ){
    return '<a href="http://www.twitter.com/'.$username.'" target="_blank" id="'.$username.'_twitter_link" class="twitter_link">@'.$username.'</a>';
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
