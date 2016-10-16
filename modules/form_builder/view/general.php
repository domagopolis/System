<?php
session_start();

include('system_general.php');

$modules = array( 'sitemap_generator', 'email', 'image', 'google_map', 'abn_lookup', 'payment', 'form_builder', 'facebook', 'datagrid', 'ipinfodb', 'social_sharing', 'rss', 'twitter', 'foursquare', 'mobile_detect' );

include($system_root_path.'system/system.php');

$settings = new setting;

$enable_accordian = FALSE;
$enable_autocomplete = FALSE;
$enable_tabs = FALSE;
$enable_time_picker = FALSE;
$enable_google_map = FALSE;
$enable_wysiwyg = FALSE;
$document_ready_script = FALSE;

$mobile_detect = new mobile_detect();
if( $mobile_detect->isMobile() AND !$mobile_detect->isTablet() ){
   $browser = 'iphone/ipod';
}else{
   $browser = 'default';
}

$params = array();
foreach( $_GET as $key => $value ){
   if( !strstr( $key, "/" ) ){
      $params[] = $key;
   }else{
      $params = explode( '/', $key );
      }
   }

$active_countries = new country;
$active_countries->where( array( 'display_enabled' => '1' ) )->find_all();

$country = new country;
if( array_key_exists( 0, $params ) ){
   $country->where( array( 'country' => url::url_decode_name( $params[0] ), 'display_enabled' => '1' ) )->find();
   
   if( !$country->country AND !strstr( $params[0], '_php' ) ){
//      header("Location: $subdomain");
      }
   }

$state = new state_province;
if( array_key_exists( 1, $params ) ){
   $state->where( array( 'country_id' => $country->country_id, 'short_name' => url::url_decode_name( $params[1] ) ) )->find();
   if( !$state->short_name AND !strstr( $params[1], '_php' ) ){
//      header("Location: $subdomain");
      }
   }

$location = new postcode;
if( array_key_exists( 2, $params ) ){
   $location->where( array( 'country_id' => $country->country_id, 'state_province_id' => $state->state_province_id, 'name' => url::url_decode_name( $params[2] ) ) )->find();

   if( !$location->name AND !strstr( $params[2], '_php' ) ){
//      header("Location: $subdomain");
      }
   }

$trades_service = new trades_service;
if( array_key_exists( 3, $params ) ){
   $trades_service->where( array( 'title' => url::url_decode_name( $params[3] ) ) )->find();

   if( !$trades_service->title AND !strstr( $params[3], '_php' ) ){
//      header("Location: $subdomain");
      }
   }
   
$facebook_app = new facebook_app( $settings->facebook_app_id, $settings->facebook_app_secret );
$facebook_app->set_permissions( array( 'user_birthday', 'user_location' ) );
?>
