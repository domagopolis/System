<?php
/*
Name: hostip.php
Description: Classes to define geoip from hostip api.
*/
class hostip{

	//Properties
	public $host;
	private $hostip_url;
	private $xml_response;
	private $coords; //array $coords[0] = long, $coords[1] = lat

   // $host is domain or ip address
   function __construct( $host=FALSE ){

      if( !$host ){
         return FALSE;
         }

      $this->host = $host;
      $this->xml_response = FALSE;
      $this->coords = array();

      $this->hostip_url = 'http://api.hostip.info/';
      }

   public function get_geo_location(){

      if( !$this->host ){
         return FALSE;
      }
      
      $this->hostip_url .= '?ip='.$this->host;

      $ch = curl_init( $this->hostip_url );

      // Set custom curl options
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      // Execute post and get results
      $response = curl_exec($ch);
      curl_close ($ch);

      $this->xml_response = simplexml_load_string( str_replace( ':', '_', $response ) );

      return ( $this->xml_response AND $this->xml_response->gml_featureMember->Hostip->ip == $this->host ) ? $this->xml_response : TRUE;
      }
      
   public function get_country(){
      return $this->xml_response->gml_featureMember->Hostip->countryName;
      }
      
   public function get_country_code(){
      return $this->xml_response->gml_featureMember->Hostip->countryAbbrev;
      }
      
   public function get_location_name(){
      if( property_exists( $this, 'xml_response' ) AND property_exists( $this->xml_response, 'gml_featureMember' ) ){
         return $this->xml_response->gml_featureMember->Hostip->gml_name;
      }else{
         return FALSE;
         }
      }
      
   public function get_coordinates(){
      if( property_exists( $this->xml_response->gml_featureMember->Hostip, 'ipLocation' ) ){
         $this->coords = explode( ',', $this->xml_response->gml_featureMember->Hostip->ipLocation->gml_pointProperty->gml_Point->gml_coordinates );
         return $this->coords;
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
