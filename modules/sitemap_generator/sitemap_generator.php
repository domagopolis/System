<?php
class sitemap_generator{

   private $root_url;
   private $changefreq;
   private $lastmod;
   private $priority;
   
   private $locations;
   
   public $args = array();

   public function __construct( $root_url=FALSE, $changefreq='None', $lastmod=FALSE, $priority='automatic' ){
      $this->root_url = $root_url;
      $this->changefreq = $changefreq;
      $this->lastmod = ( $lastmod )?date( 'Y-m-d', $lastmod ):date( 'Y-m-d' );
      $this->priority = $priority;
      
      $this->items = array();

      return $this;
      }

   public function scan_url( $url=FALSE){
   
      if( $url === FALSE ){
         $url = $this->root_url;
      }

      if( $this->priority == 'automatic' ){
         $priority = 1.0;
      }else{
         $priority = $this->priority;
      }

      $this->add_location( $url, $this->changefreq, $this->lastmod, number_format( $priority, 2 ) );

      $input = file_get_contents( $url );
      $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
      if(preg_match_all("/$regexp/siU", $input, $matches, PREG_SET_ORDER)) {
         $this->priority *= 0.8;
         foreach( $matches as $match ){
            if( !strstr( $match[2], 'http://' ) AND !strstr( $match[2], 'https://' ) AND preg_match( '/^[A-Za-z0-9-_]+\.(php|html|htm)$/', $match[2] ) ){
               $match[2] = substr( $url, 0, strrpos( $url, '/' ) ).'/'.$match[2];
            }
            if( preg_match( '/^('.$this->root_url.')(.*)$/', $match[2] ) ){
               foreach( $this->locations as $key=>$locations ){
                  if( !array_key_exists( $match[2],  $locations ) ){
                  echo $match[2].'<br>';
                     $this->scan_url( $match[2] );
                  }
               }
            }
         }
      }
   }
      
   public function add_location( $loc=FALSE, $changefreq=FALSE, $lastmod=FALSE, $priority=FALSE ){
      if( !$loc AND !$changefreq AND !$priority ){
         return FALSE;
         }

      if( $loc ){
         $this->locations[$priority][$loc] = array( 'loc' => $loc, 'changefreq' => $changefreq, 'lastmod' => $lastmod, 'priority' => $priority );

         return $loc;
         }
      }
            
   public function display_map(){
      $this->xml_map = $this->build_map();

      if( $this->xml_map ){
         include('view/xml_map.php');
         }
      }

   private function build_map(){
      $writer = new XMLWriter();

      $writer->openMemory();
      $writer->setIndent( TRUE );
      $writer->setIndentString( "    " );
      $writer->startDocument( "1.0", "UTF-8" );

      $writer->startElement( 'urlset' );

      foreach( $this->locations as $location_arr ){
         if( is_array( $location_arr ) AND sizeof( $location_arr ) > 0 ){
            foreach( $location_arr as $url=>$location ){
               $writer->startElement( 'url' );
               foreach( $location as $key=>$value ){
                  $writer->startElement( $key );
                  $writer->writeCData( $value );
                  $writer->endElement();
                  }
               $writer->endElement();
               }
            }
         }

      $writer->endElement();

      return $writer->outputMemory(false);
      }
   }
?>
