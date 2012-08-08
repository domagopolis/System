<?php
class facebook_app{

   private $app_id;
   private $application_secret;
   private $markup_format;
   private $access_token;
   private $og_data = array();
   private $permissions = array();
   private $like_options = array();
   private $comment_options = array();
   
   public $args = array();

   public function __construct( $app_id=FALSE, $application_secret=FALSE ){
      $this->app_id = $app_id;
      $this->application_secret = $application_secret;
      $this->markup_format = 'XFBML';
      $this->access_token = FALSE;
      $this->og_data = array( 'title' => '', 'type' => '', 'image' => '', 'image:width' => '', 'image:height' => '', 'url' => '', 'site_name' => '', 'description' => '', 'locale' => '', 'audio' => '', 'video' => '' );
      $this->permissions = array( 'email' );
      $this->like_options = array( 'send' => false, 'width' => 450, 'show_faces' => false, 'font' => 'arial' );
      $this->comment_options = array( 'num_posts' => 3, 'width' => 450, 'colorscheme' => 'light' );
      
      return $this;
      }
      
   public function set_permissions( $options=array() ){
      if( $this->app_id ){
         $this->permissions = array_merge( $this->permissions, $options );
         return TRUE;
      }else{
         return FALSE;
         }
   }
      
   public function get_facebook_cookie(){
      if( !array_key_exists( 'fbsr_'.$this->app_id, $_COOKIE ) ){
         return FALSE;
         }

      list($encoded_sig, $payload) = explode('.', $_COOKIE['fbsr_'.$this->app_id], 2);

      // decode the data
      $sig = base64_decode(strtr($encoded_sig, '-_', '+/'));
      $data = json_decode( base64_decode( strtr( $payload, '-_', '+/' ) ), true );
      if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
         return FALSE;
         }

      // check sig
      $expected_sig = hash_hmac('sha256', $payload, $this->application_secret, $raw = true);
      if ($sig !== $expected_sig) {
         return FALSE;
         }

      return $this->get_access_token($data['code']);
      }

   private function get_access_token( $code=FALSE ){
      if( !$code ){
         return FALSE;
         }
         
      $response = file_get_contents('https://graph.facebook.com/oauth/access_token?client_id='.$this->app_id.'&client_secret='.$this->application_secret.'&redirect_uri=&code='.$code);
      parse_str( $response );
      $this->access_token = $access_token;

      return TRUE;
   }

   private function process( $fields=array() ){
      if( array_key_exists('oauth_access_token', $this->args ) ){
         return json_decode( file_get_contents( 'https://graph.facebook.com/me?wrap_access_token='.$this->args['oauth_access_token'] ) )->me;
      }else if( $this->access_token ){
         $url = 'https://graph.facebook.com/me?access_token='.$this->access_token.( ( sizeof( $fields ) )?'&fields='.implode( ',', $fields ):'' );
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         $response = curl_exec($ch);
         curl_close($ch);

         return json_decode($response);
      }else{
         return FALSE;
         }
      }
      
   public function get_user_details(){
      return $this->process();
      }
      
   public function get_user_connections( $connections=array() ){
      return $this->process( $connections );
      }
      
   public function get_user_friends(){
      return $this->get_user_connections( array( 'friends' ) )->friends->data;
      }
      
   public function get_image_url( $user_id=FALSE ){
      return ( $user_id !== FALSE )?'http://graph.facebook.com/'.$user_id.'/picture':FALSE;
      }
      
   public function display_login(){
      include('view/'.$this->markup_format.'/facebook_login.php');
      }
      
   public function display_like( $refering_url=FALSE, $options=array() ){
      $this->like_options = array_merge( $this->like_options, $options );
      
      foreach( $this->like_options as $key => $value ){
         if( is_bool( $value ) ){
            $this->like_options[$key] = ($value)?'true':'false';
            }
         }
      
      if( $refering_url ){
         $this->refering_url = $refering_url;
         
         include('view/'.$this->markup_format.'/facebook_like.php');
         }
      }
      
   public function display_comments( $refering_url=FALSE, $options=array() ){
      $this->comment_options = array_merge( $this->comment_options, $options );

      foreach( $this->comment_options as $key => $value ){
         if( is_bool( $value ) ){
            $this->comment_options[$key] = ($value)?'true':'false';
            }
         }

      if( $refering_url ){
         $this->refering_url = $refering_url;

         include('view/'.$this->markup_format.'/facebook_comments.php');
         }
      }
      
   public function display_opengraph( $options=array() ){
      if( $this->app_id ){
         $this->og_data = array_merge( $this->og_data, $options );
         
         include('view/facebook_opengraph.php');
         }
      }

   public function display_footer(){
      if( $this->app_id ){
         include('view/facebook_footer.php');
         }
      }
   }
?>
