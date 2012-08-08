<?php
function form_format( $format=FALSE, $value=FALSE ){

   if( $value ){
      switch( $format ){
         case "currency": $value = "$".$value; break;
         case "date": $value = date( "d/m/Y", $value ); break;
         case "longdate": $value = date( "F, d/m/Y", $value ); break;
         case "datetime": $value = date( "d/m/Y H:i", $value ); break;
         case "time": $value = date( "h:iA", $value ); break;
         }
      }

   return $value;
   }
?>
