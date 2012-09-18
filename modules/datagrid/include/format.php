<?php
function datagrid_format( $format, $value ){

   switch( $format ){
      case "currency": $value = "$".$value; break;
      case "decimal": $value = number_format( $value, 2, '.', ',' ); break;
      case "date": $value = date( "d/m/Y", $value ); break;
      case "longdate": $value = date( "F, d/m/Y", $value ); break;
      case "datetime": $value = date( "d/m/Y H:i", $value ); break;
      case "time": $value = date( "H:i", $value ); break;
      case "text_limit": $value = text::limit_words( $value, 10 ); break;
      case "image": $value = '<img src='.$value.' alt="'.$value.'" />';
      }

   return $value;
   }
?>
