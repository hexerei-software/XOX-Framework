<?php

/*****************************************************************
	a date range with start and end dates and helper functions
															 23.08.2004 14:09
*****************************************************************/
class cDateRange
{
	var $start_date = 0;
	var $start_year = 0;
	var $start_month = 0;
	var $start_day = 0;
	var $start_hour = 0;
	var $start_minutes = 0;
	var $start_seconds = 0;

	var $end_date = 0;
	var $end_year = 0;
	var $end_month = 0;
	var $end_day = 0;
	var $end_hour = 0;
	var $end_minutes = 0;
	var $end_seconds = 0;

	function cDateRange($start=0,$end=0) {
		if ( $start ) $this->setStart($start);
		if ( $end ) $this->setEnd($end);
	}
	function isValid() {
		return ($this->start_date <= $this->end_date);
	}
	function isFuture($strict=true) {
		return $strict?($this->start_date > mktime(0,0,0,date('n'),date('j'),date('Y'))):($this->start_date >= mktime(0,0,0,date('n'),date('j'),date('Y')));
	}
	function isPast($strict=true) {
		return $strict?($this->end_date < mktime(0,0,0,date('n'),date('j'),date('Y'))):($this->end_date <= mktime(0,0,0,date('n'),date('j'),date('Y')));
	}
	function isNow() {
		return $this->isInside();
	}
	function isInside($year=0,$month=0,$day=0,$hour=0,$minutes=0,$seconds=0) {
		$date = $this->getDate($year,$month,$day,$hour,$minutes,$seconds);
		return ( $date >= $this->start_date && $date <= $this->end_date );
	}
	function intercepts($range,$match=true) {
			$interception = 0;
			$inrange = false;
			if ( $match ) {
				$inrange = ($range->start_date <= $this->end_date && $this->start_date <= $range->end_date);
			} else {
				$inrange = ($range->start_date < $this->end_date && $this->start_date < $range->end_date);
			}
			if ( $inrange ) {
				$interception_start_date = ($range->start_date < $this->start_date) ? $this->start_date : $range->start_date;
				$interception_end_date = ($range->end_date > $this->end_date) ? $this->end_date : $range->end_date;
				$interception = $this->getTimediff($interception_start_date,$interception_end_date);
				#$interception = ($interception < 1) ? 0 : ($interception_end_date < $this->end_date ? $interception + 1 : $interception);
				$interception = ($interception < 1) ? 0 : $interception;
			}
			return $interception;
	}
	function setStart($year=0,$month=0,$day=0,$hour=0,$minutes=0,$seconds=0) {
		$date = $this->getDate($year,$month,$day,$hour,$minutes,$seconds);
		$this->start_date = $date;
		$this->start_year = date('Y',$date);
		$this->start_month = date('m',$date);
		$this->start_day = date('d',$date);
		$this->start_hour = date('H',$date);
		$this->start_minutes = date('i',$date);
		$this->start_seconds = date('s',$date);
	}
	function setEnd($year=0,$month=0,$day=0,$hour=0,$minutes=0,$seconds=0) {
		$date = $this->getDate($year,$month,$day,$hour,$minutes,$seconds);
		$this->end_date = $date;
		$this->end_year = date('Y',$date);
		$this->end_month = date('m',$date);
		$this->end_day = date('d',$date);
		$this->end_hour = date('H',$date);
		$this->end_minutes = date('i',$date);
		$this->end_seconds = date('s',$date);
	}
	function getDate(&$year,&$month,&$day,$hour=0,$minutes=0,$seconds=0) {
		if ( $month == 0 ) {
			if ( $year <= 0 || $year > 15631200 ) {
				if ( $year <= 0 ) {
 					$aDate = explode('-',date('Y-n-j'));
				} else {
 					$aDate = explode('-',date('Y-n-j',$year));
				}
 				$year=intval($aDate[0]);
 				$month=intval($aDate[1]);
 				$day=intval($aDate[2]);
			} else {
				if ( preg_match('/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/',$year,$aDate) ) {
 					$year=intval($aDate[1]);
 					$month=intval($aDate[2]);
 					$day=intval($aDate[3]);
					$hour=intval($aDate[4]);
					$minutes=intval($aDate[5]);
					$seconds=intval($aDate[6]);
				} elseif ( preg_match('/^(\d{4})-(\d{2})-(\d{2})$/',$year,$aDate) ) {
 					$year=intval($aDate[1]);
 					$month=intval($aDate[2]);
 					$day=intval($aDate[3]);
				} elseif ( preg_match('/^(\d{1,2}).(\d{1,2}).(\d{4})$/',$year,$aDate) ) {
 					$year=intval($aDate[3]);
 					$month=intval($aDate[2]);
 					$day=intval($aDate[1]);
				} else {
					$aDate = preg_split('/[^0-9]/',$year);
					if (count($aDate)!=3) $aDate = explode('-',date('j-n-Y'));
 					$year=intval($aDate[2]);
 					$month=intval($aDate[1]);
 					$day=intval($aDate[0]);
				}
			}
		}
		return mktime($hour,$minutes,$seconds,$month,$day,$year);
	}

	function getTimediff($start=0,$end=0,$unit='d') {
		if ($start==0) $start=$this->start_date;
		if ($end==0) $end=$this->end_date;
		$retVal=($end - $start);
		switch($unit) {
			case 'm': $retVal=ceil($retVal/60); break;
			case 'h': $retVal=ceil($retVal/(60*60)); break;
			case 'd': $retVal=round($retVal/(60*60*24)); break;
		}
		return $retVal;
	}

	function debug() {
		echo "start: $this->start_date : ".date('Y-m-d',$this->start_date)." : $this->start_year-$this->start_month-$this->start_day<br />";
		echo "end: $this->end_date : ".date('Y-m-d',$this->end_date)." : $this->end_year-$this->end_month-$this->end_day<br />";
	}

}	// finish class cDateRange

?>
