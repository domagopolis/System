<?php

/*
Name: javascript.php
Description: Classes to package javascript files
*/

class javascript{

   static protected $scripts = array();

   public static function extract_javascript_file( $javascript_path=FALSE ){
      GLOBAL $subdomain;

      $raw_js = '';
      $fp = fopen( $javascript_path, "r+" );
      if( $fp ){
         while( !feof( $fp ) ){
            $line = trim( fgets( $fp, 255 ) );

            $raw_js .= $line;
            }
         }
      @fclose( $fp );

      return $raw_js;
      }
      
   static public function add( $file ){
      if ( is_array( $file ) ){
         array_merge( self::$scripts, $file );
      }else{
         self::$scripts[] = $file;
         }

		return TRUE;
	}
	
   public static function package( $path=FALSE ){

      $minified_js = '';

      foreach ( self::$scripts as $file ){
         $minified_js .= self::minify( $file );
         }
         
      file_put_contents( $path.'combined.js', $minified_js );
      }
   
   public static function minify( $file ){
      $result = '';

      $file = new SplFileInfo( $file );
      if ($file->isFile() AND $file->isReadable()){
         $source = trim( file_get_contents( $file->getPathname() ) );

         // File comment
         $comment = '// '.$file->getFilename().' -------------------';

         if (preg_match('#/\*(?:[^*]|(?:\*(?!/)))+?\*/#m', $source, $matches)){
            // Add the comment header, JSMin will remove it
            $comment .= "\n".$matches[0];
            }

         // Minify the source and add a semicolon on the end
         $source = trim(JSMin::minify($source), ';');

         // Minify the source and add it to the output
         $result = $comment.$source.';';
         }

      return $result;
      }

   }
?>
