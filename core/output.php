<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Output {

	public static function timeago($date) {
		$timestamp = strtotime($date);	
		$str_time = array("second", "minute", "hour", "day", "month", "year");
		$length = array("60","60","24","30","12","10");
		$currentTime = time();
		if($currentTime >= $timestamp) {
			 $diff     = time()- $timestamp;
			 for($i = 0; $diff >= $length[$i] && $i < count($length)-1; $i++) {
			 	$diff = $diff / $length[$i];
			 }
			 $diff = round($diff);
			 $plural = $diff>1 ? "s" : "";
			 return $diff . " " . $str_time[$i] . "{$plural} ago";
		}
		else {
			return "In the future!";
		}
	 }



}