<?php

/*
Name: css_stream.php
Description: helpers for retrieving css and rendering.
*/

class css_stream{

   public static function extract_css_file( $css_path=FALSE ){
      GLOBAL $subdomain;

      $raw_html = '';
      $fp = fopen( $css_path, "r+" );
      if( $fp ){
         while( !feof( $fp ) ){
            $line = trim( fgets( $fp, 255 ) );
            
            $raw_css .= $line;
            }
         }
      @fclose( $fp );

//      $raw_css = strtolower( $raw_css );
      $raw_css = str_ireplace( "../images", $subdomain."assets/images", $raw_css );
      $raw_css = str_ireplace( "html{", "#html{", $raw_css );
      $raw_css = str_ireplace( "head{", "#head{", $raw_css );
      $raw_css = str_ireplace( "body{", "#body{", $raw_css );

      return $raw_css;
      }
      
   public static function prepend_prefix_id( $css_str=FALSE, $style_prefix=FALSE ){

      preg_match_all( '|([A-Za-z0-9#.,:-_ ]+){(.*)}|U', $css_str, $css_line_arr );

      $css_str = '';
      foreach( $css_line_arr[0] as $css_line ){
         preg_match_all( '/([A-Za-z0-9#.:-_ ]+)([^,]*)/i', substr( $css_line, 0, strpos( $css_line, '{' ) ), $css_segment_arr );

         foreach( $css_segment_arr[0] as $css_segment ){
            //match id tag
            if( preg_match( '/([A-Za-z0-9:-_]*)#([A-Za-z0-9,:-_]+)/i', $css_segment ) ){
               $css_str .= preg_replace( '/([A-Za-z0-9:-_]*)#([A-Za-z0-9,:-_]+)/i', 'div#'.$style_prefix.'body $1#'.$style_prefix.'$2', $css_segment );
            //match class tag
            }else if( preg_match( '/([A-Za-z0-9:-_]*)\.([A-Za-z0-9,:-_]+)/i', $css_segment ) ){
               $css_str .= preg_replace( '/([A-Za-z0-9:-_]*)\.([A-Za-z0-9,:-_]+)/i', 'div#'.$style_prefix.'body $1.'.$style_prefix.'$2', $css_segment );
            //match html tag
            }else if( preg_match( '/([A-Za-z0-9:-_]+)/i', $css_segment ) ){
               $css_str .= preg_replace( '/([A-Za-z0-9:-_]+)/i', 'div#'.$style_prefix.'body $1', $css_segment );
            //default
            }else{
               $css_str .= $css_line;
               }
            $css_str .= ',';
            }
            
         $css_str = rtrim( $css_str, ',' );
         $css_str .= substr( $css_line, strpos( $css_line, '{' ) );
         }

      return $css_str;
      }
      
   }
?>
