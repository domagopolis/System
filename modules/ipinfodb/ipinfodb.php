<?php
/*
Name: ipinfodb.php
Description: Classes to define geoip from hostip api.
*/
class ipinfodb{

	//Properties
	public $host;
	private $key;
	private $url;
	private $response;
	private $format;
	private $coords; //array $coords[0] = long, $coords[1] = lat

   // $host is domain or ip address
   function __construct( $key=FALSE, $host=FALSE, $format='xml' ){

      if( !$key OR !$host ){
         return FALSE;
         }

      $this->host = $host;
      $this->key = $key;
      $this->format = $format;
      $this->response = FALSE;
      $this->coords = array();

      $this->url = 'http://api.ipinfodb.com/v3/';
      }

   private function process( $search_path = 'ip-city/' ){

      $this->url .= $search_path;
      $this->url .= '?key='.$this->key;
      $this->url .= '&ip='.$this->host;
      $this->url .= '&format='.$this->format;

      $ch = curl_init( $this->url );

      // Set custom curl options
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      // Execute post and get results
      $response = curl_exec($ch);
      curl_close ($ch);

      $this->response = simplexml_load_string( str_replace( ':', '_', $response ) );
      
      return ( $this->response AND $this->response->statusCode == 'OK' AND $this->response->ipAddress == $this->host ) ? $this->response : FALSE;
      }
      
   public function find_country(){
      if( $this->host ){
         return $this->process( 'ip-country/' );
      }else{
         return FALSE;
         }
      }
      
   public function find_city(){
      if( $this->host ){
         return $this->process( 'ip-city/' );
      }else{
         return FALSE;
         }
      }
      
   public function get_coordinates(){
      if( $this->response AND property_exists( $this->response, 'latitude' ) AND property_exists( $this->response, 'longitude' ) ){
         $this->coords['lat'] = $this->response->latitude;
         $this->coords['lon'] = $this->response->longitude;
         return $this->coords;
      }else{
         return FALSE;
         }
      }
      
   public function get_city(){
      if( $this->response AND property_exists( $this->response, 'cityName' ) ){
         return ucfirst( strtolower( (string)$this->response->cityName ) );
      }else{
         return FALSE;
         }
      }
      
   public function find_distance( $lat=0, $lng=0 ){

      if( sizeof( $this->coords ) === 0 )
      {
         return FALSE;
      }
      
      $distance = 12742 * atan2(
                  sqrt(
                  (
                  sin( ( ( $this->coords[1]/(180/pi()) ) - ( $lat/(180/pi()) ) )/2 ) *
                  sin( ( ( $this->coords[1]/(180/pi()) ) - ( $lat/(180/pi()) ) )/2 )
                  ) + (
                  cos( $lng/(180/pi()) ) *
                  cos( $this->coords[0]/(180/pi()) ) *
                  sin( ( ( $this->coords[0]/(180/pi()) ) - ( $lng/(180/pi()) ) )/2 ) *
                  sin( ( ( $this->coords[0]/(180/pi()) ) - ( $lng/(180/pi()) ) )/2 ) )
                  ),
                  sqrt(
                  1 -
                  (
                  sin( ( ( $this->coords[1]/(180/pi()) ) - ( $lat/(180/pi()) ) )/2 ) * sin( ( ( $this->coords[1]/(180/pi()) ) - ( $lat/(180/pi()) ) )/2 )
                  ) + (
                  cos( $lng/(180/pi()) ) *
                  cos( $this->coords[0]/(180/pi()) ) *
                  sin( ( ( $this->coords[0]/(180/pi()) ) - ( $lng/(180/pi()) ) )/2 ) *
                  sin( ( ( $this->coords[0]/(180/pi()) ) - ( $lng/(180/pi()) ) )/2 ) )
                  )
                  );
                  
      return $distance;
      }
   }
?>
