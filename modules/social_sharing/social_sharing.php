<?php
class social_sharing{

   private $url;
   private $message;
   
   public $social_links_heading;
   public $social_links_arr = array();
   public $args = array();

   public function __construct( $url=FALSE, $message=FALSE, $social_links_arr=array() ){
      $this->url = $url;
      $this->message = $message;
      $this->social_links_arr = array_merge( $this->social_links_arr, $social_links_arr );
      
      $this->social_links_heading = 'Share this link';
      
      return $this;
      }
      
   public function set_heading( $social_links_heading ){
      $this->social_links_heading = $social_links_heading;
      }

   public function display_social_nav(){
      $links_arr = $this->get_links();
      
      include('view/social_nav.php');
      }

   public function get_links( $social_links_arr=array() ){
      $links_arr = array();
      
      $this->social_links_arr = array_merge( $this->social_links_arr, $social_links_arr );
      
      foreach( $this->social_links_arr as $social_link ){
         $function = 'get_'.$social_link.'_link';
         $links_arr[$social_link] = $this->$function();
         }
         
      return $links_arr;
      }
      
   public function get_facebook_link(){
      $link = 'http://www.facebook.com/sharer.php?u='.$this->url.'&t='.$this->message;
      
      return $link;
      }

   public function get_twitter_link(){
      $link = 'http://www.twitter.com/home?status='.$this->message.' '.$this->url;
      $link = str_replace( ' ', '+', $link );
      
      return $link;
      }

   public function get_email_link(){
      $link = 'email_friend.php?url='.$this->url.'&message='.$this->message;
      
      return $link;
      }
      
   public function get_stumbleupon_link(){
      $link = 'http://www.stumbleupon.com/submit?url='.$this->url;

      return $link;
      }
      
   public function get_delicious_link(){
      $link = 'http://www.delicious.com/post?url='.$this->url;

      return $link;
      }
      
   public function get_digg_link(){
      $link = 'http://www.digg.com/sumbit?phase=2&url='.$this->url;

      return $link;
      }
      
   public function get_flickr_link(){
      $link = 'http://www.flickr.com';
      
      return $link;
      }

   public function get_blinklist_link(){
      $link = 'http://www.blinklist.com/index.php?Action=Blink/addblink&Description='.$this->url;

      return $link;
      }
      
   }
?>
