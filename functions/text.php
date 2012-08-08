<?php

/*
Name: text.php
Description: Classes for formated of text.
*/

class text{

   public static function limit_words($str, $limit = 100, $end_char = NULL){
      $limit = (int) $limit;
      $end_char = ($end_char === NULL) ? '&#8230;' : $end_char;

      if (trim($str) == '')
         return $str;

      if ($limit <= 0)
         return $end_char;

      preg_match('/^\s*+(?:\S++\s*+){1,'.$limit.'}/u', $str, $matches);

      // Only attach the end character if the matched string is shorter
      // than the starting string.
      return rtrim($matches[0]).(strlen($matches[0]) == strlen($str) ? '' : $end_char);
      }

   public static function singular( $str = FALSE ){

      if (substr($str, -3) === 'ies'){
         $str = substr($str, 0, strlen($str) - 3).'y';
      }elseif (substr($str, -4) === 'sses' OR substr($str, -3) === 'xes'){
         $str = substr($str, 0, strlen($str) - 2);
      }elseif (substr($str, -1) === 's'){
         $str = substr($str, 0, strlen($str) - 1);
         }

      return $str;
      }

   public static function plural( $str = FALSE ){
		$end = substr($str, -1);
		$low = (strcmp($end, strtolower($end)) === 0) ? TRUE : FALSE;

		if (preg_match('/[sxz]$/i', $str) OR preg_match('/[^aeioudgkprt]h$/i', $str))
		{
			$end = 'es';
			$str .= ($low == FALSE) ? strtoupper($end) : $end;
		}
		elseif (preg_match('/[^aeiou]y$/i', $str))
		{
			$end = 'ies';
			$end = ($low == FALSE) ? strtoupper($end) : $end;
			$str = substr_replace($str, $end, -1);
		}
		else
		{
			$end = 's';
			$str .= ($low == FALSE) ? strtoupper($end) : $end;
		}

      return $str;
      }
   }
?>
