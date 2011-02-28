<?php
/*
 * Created on 19.10.2009
 *
 * (c) 2009 hexerei software creations
 * daniel vorhauer <daniel@hexerei.net>
 */

 	echo makeDateRange(getvar('start','1999-01'),getvar('ende','2002-12'),'b.belegdatum');

	function getvar($v,$d=''){return (isset($_GET[$v]))?$_GET[$v]:$d; }

	function maketime($year,$month=0,$day=0,$hour=0,$minutes=0,$seconds=0) {
	    if ( $month == 0 ) {
	      if ( is_numeric($year) && ($year <= 0 || $year > 15631200) ) {
	        $aDate = ( $year <= 0 ) ? explode('-',date('Y-n-j')) : explode('-',date('Y-n-j',$year));
	    	$year=intval($aDate[0]); $month=intval($aDate[1]); $day=intval($aDate[2]);
		} else {
		    if ( preg_match('/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})(:(\d{2}))?$/',$year,$aDate) ) {
		      $year=intval($aDate[1]); $month=intval($aDate[2]); $day=intval($aDate[3]);
		      $hour=intval($aDate[4]); $minutes=intval($aDate[5]); $seconds=intval($aDate[7]);
		    } elseif ( preg_match('/^(\d{4})-(\d{2})-(\d{2})$/',$year,$aDate) ) {
		      $year=intval($aDate[1]); $month=intval($aDate[2]); $day=intval($aDate[3]);
		    } elseif ( preg_match('/^(\d{1,2}).(\d{1,2}).(\d{4})$/',$year,$aDate) ) {
		      $year=intval($aDate[3]); $month=intval($aDate[2]); $day=intval($aDate[1]);
		    } else {
		      $aDate = explode('-',date('Y-n-j'));
	          $year=intval($aDate[0]); $month=intval($aDate[1]); $day=intval($aDate[2]);
	        }
	      }
	    }
	    return mktime($hour,$minutes,$seconds,$month,$day,$year);
	}

	function makeDateRange($start='',$end='',$field='') {
		$st = maketime($start.'-01');
		$et = maketime($end.'-01');
		$start = date('Y-m-d',mktime(0,0,0,date('n',$st),1,date('Y',$st)));
		$end = date('Y-m-d',mktime(0,0,0,date('n',$et)+1,0,date('Y',$et)));
	 	return ($field=='') ? array('start'=>$start,'end'=>$end) : "($field >= '$start' AND $field <= '$end')";
	}

?>
