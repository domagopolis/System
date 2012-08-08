<?php
class rss_feed{

   public $header;

   private $image;
   private $items;
   private $feed;
   
   public $args = array();

   public function __construct( $title=FALSE, $description=FALSE, $link=FALSE ){
      $this->header = array();

      $this->header['title'] = $title;
      $this->header['description'] = $description;
      $this->header['link'] = $link;

      $this->header['language'] = 'en-us';
      $this->header['pubDate'] = date( 'r' );
      $this->header['lastBuildDate'] = date( 'r' );
      
      $this->image = array();
      $this->items = array();

      return $this;
      }

   public function add_image( $url=FALSE, $title=FALSE, $link=FALSE ){
      if( !$url OR !$title OR !$link ){
         return FALSE;
      }else{
         $this->image = array( 'url' => $url, 'title' => $title, 'link' => $link );
      
         return TRUE;
         }
      }

   public function add_attr( $key=FALSE, $value=FALSE ){
      if( !$key AND !$value ){
         return FALSE;
         }

      $this->header[$key] = $value;

      return TRUE;
      }
      
   public function add_item( $title=FALSE, $description=FALSE, $link=FALSE, $guid=FALSE ){
      if( !$title AND !$description AND !$link ){
         return FALSE;
         }

      if( $guid ){
         $this->items[$guid]['title'] = $title;
         $this->items[$guid]['description'] = $description;
         $this->items[$guid]['link'] = $link;
         $this->items[$guid]['guid'] = $guid;

         return $guid;
      }else{
         $this->items[$link]['title'] = $title;
         $this->items[$link]['description'] = $description;
         $this->items[$link]['link'] = $link;

         return $link;
         }
      }

   public function add_item_field( $key=FALSE, $tag=FALSE, $value=FALSE ){
      if( array_key_exists( $key, $this->items ) ){
         if( strtolower( $tag ) === 'pubdate' ){
            $this->items[$key][$tag] = date( 'r', $value );
         }else{
            $this->items[$key][$tag] = $value;
            }
         return TRUE;
      }else{
         return FALSE;
         }
      }
            
   public function display_feed(){
      $this->feed = $this->build_feed();

      if( $this->feed ){
         include('view/feed.php');
         }
      }

   private function build_feed(){
      $writer = new XMLWriter();

      $writer->openMemory();
      $writer->setIndent( TRUE );
      $writer->setIndentString( "    " );
      $writer->startDocument( "1.0", "UTF-8" );

      $writer->startElement( 'rss' );
      $writer->writeAttribute( 'version', '2.0' );
      $writer->startElement( 'channel' );

      foreach( $this->header as $key=>$value ){
         $writer->startElement( $key );
         $writer->writeCData( $value );
         $writer->endElement();
         }

      if( sizeof( $this->image ) > 0 ){
         $writer->startElement( 'image' );
         foreach( $this->image as $key=>$value ){
            $writer->startElement( $key );
            $writer->writeCData( $value );
            $writer->endElement();
            }
         $writer->endElement();
         }

      foreach( $this->items as $item ){
      if( is_array( $item ) AND sizeof( $item ) > 0 ){
         $writer->startElement( 'item' );
         foreach( $item as $key=>$value ){
            $writer->startElement( $key );
            $writer->writeCData( $value );
            $writer->endElement();
            }
         $writer->endElement();
         }         }

      $writer->endElement();
      $writer->endElement();

      return $writer->outputMemory(false);
      }
   }
?>
