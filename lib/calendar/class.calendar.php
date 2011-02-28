<?php

/*
 * class.calendar.php - based on simple calendarclass from mlemos
 * extended by daniel vorhauer - hexerei software creations
 *
 */

class cBaseCalendar
{
	var $year;
	var $month;
	var $day;
	var $timestamp;

	var $week_day_names;
	var $month_names;
	var $month_days;
	var $month_week_day;

	var $calendar_rows;

	var $cell_padding;
	var $cell_spacing;

	var $show_caption;
	var $show_left_nav;
	var $show_right_nav;
	var $show_date;
	var $show_today;
	var $show_months;
	var $months_per_row;

	var $navbar_bottom;
	var $label_today;
	var $date_format;

	var $clickHandler;
	var $error;

	var $base;

	function cBaseCalendar($y=0,$m=0,$d=0) {
		$this->reset();
		$this->setDate($y,$m,$d);
	}

	function setDate($year,$month,$day) {
		if ( $month == 0 ) {
			if ( $year <= 0 || $year > 15631200 ) {
				if ( $year <= 0 ) {
 					$aDate = explode('-',date('Y-n-j'));
				} else {
 					$aDate = explode('-',date('Y-n-j',$year));
				}
 				$this->year=intval($aDate[0]);
 				$this->month=intval($aDate[1]);
 				$this->day=intval($aDate[2]);
			} else {
				if ( preg_match('/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/',$year,$aDate)
					|| preg_match('/^(\d{4})-(\d{2})-(\d{2})$/',$year,$aDate) ) {
 					$this->year=intval($aDate[1]);
 					$this->month=intval($aDate[2]);
 					$this->day=intval($aDate[3]);
				} elseif ( preg_match('/^(\d{1,2}).(\d{1,2}).(\d{4})$/',$year,$aDate) ) {
 					$this->year=intval($aDate[3]);
 					$this->month=intval($aDate[2]);
 					$this->day=intval($aDate[1]);
				} else {
 					$aDate = explode('-',date('Y-n-j'));
 					$this->year=intval($aDate[0]);
 					$this->month=intval($aDate[1]);
 					$this->day=intval($aDate[2]);
				}
			}
		} else {
 			$this->year=intval($year);
 			$this->month=intval($month);
 			$this->day=intval($day);
		}
		$this->timestamp = mktime(0,0,0,$month,$day,$year);
	}


	// check if given year is leapyear
	function isleapyear($year)
	{
 		return (intval($year % 4)==0 && (intval($year % 100)!=0 || intval($year % 400)==0));
	}

 	function weekday($year,$month,$day)
 	{
  	$month_year_days = array(0,0,31,59,90,120,151,181,212,243,273,304,334);
		$corrected_year = $year;
  	if ($month<3) $corrected_year--;
  	$leap_years = (intval($corrected_year/4));
		$month_year_day = isset($month_year_days[$month])?$month_year_days[$month]:0;
  	return (
			intval((intval((-473+365*($year-1970)+$leap_years-intval($leap_years/25)+((intval($leap_years % 25)<0) ? 1 : 0)
		+ intval((intval($leap_years/25))/4)+$month_year_day+$day-1) % 7)+7) % 7)
		);
	}

	function getStyle() {
 		$style = '';
		if ( !empty($this->style) ) {
 			$style = '<style type="text/css"><!--'."\n";
 			$style.= "$this->style\n";
 			$style.= "\n-->\n</style>\n";
		}
		return $style;
 	}

	function getScript() {
		if (empty($this->script)) $this->script="function ".$this->clickHandler."(date) { return true; }";
		$script = '<script language="javascript"><!--'."\n";
 		$script.= "$this->script\n";
 		$script.= "\n-->\n</script>\n";
		return $script;
 	}

	function monthdays($month='',$year='') {
		if ($month=='') $month=$this->month;
		if ($year=='') $year=$this->year;
  	if ($month<1) { $month+=12; $year--; }
  	elseif ($month>12) { $month-=12; $year++; }
  	switch($month) {
			case 2:
 				return (($this->isleapyear($year)) ? 29 : 28);
 				break;
			case 4:
			case 6:
			case 9:
			case 11:
 				return 30;
			default:
 				return 31;
  	}
	}

 	function outputCalendar()
 	{
		if (empty($this->base)) $this->base = isset($GLOBALS['nav']) ? $GLOBALS['nav']->pageurl : 'index.php?p='.(isset($_GET['p'])?$_GET['p']:'');
		if (!strstr($this->base,'?')) $this->base.= '?';
		$display = $this->getStyle();
		$display.= $this->getScript();
 		if ($this->show_months>1) $display.='<table cellspacing="1" cellpadding="0" border="0" class="xoxCalendar"><tr>';

		$month_per_row_count = 0;
		for ( $m=0; $m<$this->show_months; $m++ ) {
			if ( $m>0 ) {
				$this->month++;
			} else {
  			if (count($this->week_day_names)!=7) $this->week_day_names=array("Su","Mo","Tu","We","Th","Fr","Sa");
 				if (count($this->month_names)!=13) $this->month_names=array( '',
  				'January', 'February', 'March',
  				'April', 'May', 'June',
  				'July', 'August', 'September',
  				'October', 'November', 'December'
 				);
			}
			if ($this->show_months>1) {
				if ( $this->months_per_row == $month_per_row_count ) {
					$display.= '</tr><tr><td>&nbsp;</td></tr><tr>';
					$month_per_row_count=0;
				}
				$display.= '<td valign="top">';
				$month_per_row_count++;
			}
			if ($this->month<1) { $this->month+=12; $this->year--; }
  		elseif ($this->month>12) { $this->month-=12; $this->year++; }

 			$this->month_days=$this->monthdays();
  		$this->month_week_day = $this->weekday( $this->year, $this->month, 1 );
  		if ($this->calendar_rows<1) $this->calendar_rows  = 6; //(floor(($this->month_week_day+$this->month_days+6)/7))+1; //+1);

			$this->day=0;

			/*
			 * render single month
			 */

			$single = '<table border="0" cellpadding="'.$this->cell_padding.'" cellspacing="'.$this->cell_spacing.'" class="xcMonth">';

			if ((!$this->navbar_bottom) && ($this->show_date || $this->show_left_nav || $this->show_right_nav || $this->show_today)) {
				$single.= '<tr><td class="xcNavigation" id="xcWeekstart">';
				if ($this->show_left_nav) $single.='<a href="'.$this->base.'&year='.$this->year.'&month='.($this->month-1).'">&lt;&lt;</a>';
				$single.= '</td><td colspan="5" class="xcDate">';
				if ($this->show_today) $single.='<a href="'.$this->base.'&year='.date('Y').'&month='.date('n').'&day='.date('j').'">'.$this->label_today.'</a>';
				elseif ($this->show_date) $single.= date($this->date_format,mktime(0,0,0,$this->month,1,$this->year));
				$single.= '</td><td class="xcNavigation">';
				if ($this->show_right_nav) $single.='<a href="'.$this->base.'&year='.$this->year.'&month='.($this->month+1).'">&gt;&gt;</a>';
				$single.= '</td></tr>';
			}

			// caption
			if ($this->show_caption) $single.= '<tr><th class="xcCaption" colspan="7">'.$this->month_names[$this->month].' '.$this->year.'</td></tr>';

			// header
			$single.= '<tr>';
 			// TODO: make start of week variable
			for ($i=0;$i<7;$i++ ) {
				$single.= '<th class="xcHeader">'.$this->week_day_names[($i+1)%7].'</th>';
			}
			$single.= '</tr>';

			// weeks
			for ( $j=0;$j<$this->calendar_rows;$j++ ) {
				$single.= '<tr>';
				for ($i=0;$i<7;$i++ ) {
 					// TODO: make start of week variable
					$this->day=($j*7+$i+2-($this->month_week_day?$this->month_week_day:7));
  			 	if ( $this->day < 1 ) {
						$single.= '<td class="xcl"'.($i==0?' id="xcWeekstart"':'').'>';
 						$single.=strval($this->monthdays($this->month-1) + $this->day);
				 		$single.= '</td>';
  			 	} elseif ( $this->day > $this->month_days ) {
						$single.= '<td class="xcn"'.($i==0?' id="xcWeekstart"':'').'>';
 						$single.=strval($this->day - $this->month_days);
				 		$single.= '</td>';
  			 	} else {
 						$single.=$this->getDay('',($i==0?'xcWeekstart':''));
  			 	}
				}
				$single.= '</tr>';
			}
			for ( $j=0;$j<6-$this->calendar_rows;$j++ ) {
				$single.= '<tr>';
				for ( $k=0;$k<7;$k++ ) $single.='<td class="xcn"'.(($j==0)?' id="xcWeekstart"':'').'>&nbsp;</td>';
				$single.= '</tr>';
			}

			if ($this->navbar_bottom && ($this->show_date || $this->show_left_nav || $this->show_right_nav || $this->show_today)) {
				$single.= '<tr><td class="xcNavigation" id="xcWeekstart">';
				if ($this->show_left_nav) $single.='<a href="'.$this->base.'&year='.$this->year.'&month='.($this->month-1).'">&lt;&lt;</a>';
				$single.= '</td><td colspan="5" class="xcDate">';
				if ($this->show_today) $single.='<a href="'.$this->base.'&year='.date('Y').'&month='.date('n').'&day='.date('j').'">'.$this->label_today.'</a>';
				elseif ($this->show_date) $single.= date($this->date_format,mktime(0,0,0,$this->month,1,$this->year));
				$single.= '</td><td class="xcNavigation">';
				if ($this->show_right_nav) $single.='<a href="'.$this->base.'&year='.$this->year.'&month='.($this->month+1).'">&gt;&gt;</a>';
				$single.= '</td></tr>';
			}

			$single.= '</table>';

			// add calendar
			$display.=$single;

			if ($this->show_months>1 && $this->months_per_row!=$month_per_row_count) $display.= '</td><td class="xcGap">&nbsp;</td>';
		}
		if ($this->show_months>1) $display.= '</tr></table>';

		return $display;
	}

	function getDay($caption='',$class_id='') {
		$day = '<td class="xcd"'.($class_id!=''?' id="'.$class_id.'"':'').'>';
 		$day.= (empty($caption)?strval($this->day):$caption);
		$day.= '</td>';
		return $day;
	}

	function reset() {
		$this->year						= 2000;
		$this->month					= 1;
		$this->day						= 0;

		$this->week_day_names	= array();
		$this->month_names		= array();
		$this->month_days			= 0;
		$this->month_week_day	= 0;

		$this->calendar_rows	= 0;

		$this->cell_padding		= 0;
		$this->cell_spacing 	= 0;

		$this->show_caption		= TRUE;
		$this->show_left_nav	= TRUE;
		$this->show_right_nav = TRUE;
		$this->show_date			= TRUE;
		$this->show_today			= TRUE;
		$this->show_months		= 1;
		$this->months_per_row = 2;

		$this->navbar_bottom	= TRUE;
		$this->label_today		= 'Today';
		$this->date_format 		= 'Y-m-d';

		$this->clickHandler		=	'xcd';
		$this->error					= '';

		$this->base						= isset($GLOBALS['nav']) ? $GLOBALS['nav']->pageurl : 'index.php'.(isset($_GET['p'])?$_GET['p']:'');
	}
}

?>