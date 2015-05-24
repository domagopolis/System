<?php

/*
Name: google_map.php
Description: Classes to retrieve google map API information.
*/

class google_map{

   private $url;
   private $google_api_key;
   private $type;
   
   public function __construct( $google_api_key=NULL, $type=NULL ){
      $this->google_api_key = $google_api_key;
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
      
   public function locate( $query=NULL ){
      $this->url = "https://maps.googleapis.com/maps/api/geocode/".$this->type;
      $this->url .= "?key=".$this->google_api_key;
      $this->url .= "&address=".urlencode( $query );
      
      if( is_array( $this->search_params ) ){
         foreach( $this->search_params as $key => $value ){
            $this->url .= "&".$key."=".$value;
            }
         }
      
      $this->result = $this->process();

      return $this->result;
      }

    public function get_coords(){
      $coords = array();
      if( $this->type === 'xml' ){
        $coords['longitude'] = current( $this->result->result->geometry->location->lng );
        $coords['latitude'] = current( $this->result->result->geometry->location->lat );
      }
      
      if( $this->type === 'json' ){
        $coords_str = $this->result->Response->Placemark->Point->coordinates;
        $coords_str = substr( $coords_str, 0, strlen( $coords_str )-2 );
        $coords['longitude'] = $this->result->results[0]->geometry->location->lng;
        $coords['latitude'] = $this->result->results[0]->geometry->location->lat;
      }

      return $coords;
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
      
   public static function sql_distance_select( $lat_origin=0, $long_origin=0, $units='km' ){

      switch( $units ){
         case 'km': $radius = 6371; break;
         case 'mi': $radius = 3959; break;
         default: $radius = 6371; break;
         }

      $d_lat = 'RADIANS( latitude - '.$lat_origin.' )';
      $d_lon = 'RADIANS( longitude - '.$long_origin.' )';
      $lat1 = 'RADIANS( '.$lat_origin.' )';
      $lat2 = 'RADIANS( latitude )';
      $a = 'SIN( '.$d_lat.'/2 ) * SIN( '.$d_lat.'/2 ) + SIN( '.$d_lon.'/2 ) * SIN( '.$d_lon.'/2 ) * COS( '.$lat1.' ) * COS( '.$lat2.' )';
      $c = '2 * ATAN2( SQRT( '.$a.' ), SQRT( 1 - '.$a.' ) )';
      $d = $radius.' * '.$c;
      
      $sql_select = $d;

      return $sql_select;
      }
      
   public static function static_map( $latitude=0, $longitude=0, $zoom=10, $height=100, $width=300, $alt='map'){
      $sensor = 'false';
      $location = $latitude.','.$longitude;
      
      ob_start();
      include('view/static_map.php');
      $string = ob_get_contents();
      ob_end_clean();
      
      return $string;
      }
      
   public static function static_map_query( $query,  $zoom=10, $height=100, $width=300, $alt='map'){
      $sensor = 'false';
      $location = url::url_encode_name( $query );
      
      ob_start();
      include('view/static_map.php');
      $string = ob_get_contents();
      ob_end_clean();
      
      return $string;
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
