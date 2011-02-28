<?php

if(!defined("_FUNCTIONS_INCLUDED")){
  define("_FUNCTIONS_INCLUDED", 1 );

	// in case no session handling is activated
	if ( !isset($_SESSION) ) $_SESSION = array();

	// initialize global variable
	$GLOBALS['GSPG'] = '';
  function initvar($v,$d=''){
		// merge globals, session vars, post and get vars
		if ($GLOBALS['GSPG']=='') $GLOBALS['GSPG']=array_merge($GLOBALS,$_SESSION,$_POST,$_GET);
		// copy to global var
		$GLOBALS[$v]=(isset($GLOBALS['GSPG'][$v]))?$GLOBALS['GSPG'][$v]:$d;
		// return global var
		return $GLOBALS[$v];
	}

	// return post, get and session variables
  function postvar($v,$d='') { return (isset($_POST[$v]))?$_POST[$v]:$d; }
  function getvar($v,$d='') { return (isset($_GET[$v]))?$_GET[$v]:$d; }
  function sessionvar($v,$d='') { return (isset($_SESSION[$v]))?$_SESSION[$v]:$d; }
  function invar($a,$v,$d='') { return (is_array($a)&&isset($a[$v]))?$a[$v]:$d; }
  function hasvar($a,$v,$d=false) { $r=invar($a,$v,$d); return (empty($r))?$d:$r; }

  // formatting
  function numberformat($v,$d=2){ $nd = number_format(is_numeric($v)?$v:0.0,$d,',','.'); return (empty($nd)) ? '0': $nd; }
  function tinydate($v){ return (empty($v)||strpos(' '.$v,'0000-00-00')>0)?'':date('d.m.',maketime($v)); }
  function shortdate($v){ return (empty($v)||strpos(' '.$v,'0000-00-00')>0)?'':date('d.m.Y',maketime($v)); }
  function isodate($v){ return (empty($v)||strpos(' '.$v,'0000-00-00')>0)?'':date('Y-m-d',maketime($v)); }
  function maketime($year,$month=0,$day=0,$hour=0,$minutes=0,$seconds=0) {
    if ( $month == 0 ) {
	  if (empty($year)||strpos(' '.$year,'0000-00-00')>0) return 0;
      if ( is_numeric($year) && ($year <= 0 || $year > 15631200) ) {
        $aDate = ( $year <= 0 ) ? explode('-',date('Y-n-j')) : explode('-',date('Y-n-j',$year));
        $year=intval($aDate[0]); $month=intval($aDate[1]); $day=intval($aDate[2]);
      } else {
        if ( preg_match('/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})(:(\d{2}))?$/',$year,$aDate) ) {
          $year=intval($aDate[1]); $month=intval($aDate[2]); $day=intval($aDate[3]);
          $hour=intval($aDate[4]); $minutes=intval($aDate[5]); $seconds=intval($aDate[7]);
        } elseif ( preg_match('/^(\d{4})-(\d{2})-(\d{2})$/',$year,$aDate) ) {
          $year=intval($aDate[1]); $month=intval($aDate[2]); $day=intval($aDate[3]);
        } elseif ( preg_match('/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{2,4})$/',$year,$aDate) ) {
          $year=intval($aDate[3]); $month=intval($aDate[2]); $day=intval($aDate[1]);
        } else {
		  $aDate = preg_split('/[^0-9]/',$year);
		  if (count($aDate)!=3) $aDate = explode('-',date('j-n-Y'));
		  $year=intval($aDate[2]); $month=intval($aDate[1]); $day=intval($aDate[0]);
        }
      }
	  if ($year<100) $year+=2000;
    }
    return mktime($hour,$minutes,$seconds,$month,$day,$year);
  }
	function timediff($start=0,$end=0,$unit='d') {
		$retVal=($end-$start);
		switch($unit) {
			case 'm': $retVal=intval($retVal/60); break;
			case 'h': $retVal=intval($retVal/(60*60)); break;
			case 'd': $retVal=intval($retVal/(60*60*24)); break;
		}
		return $retVal;
	}



	// furious quote business
	// prepare string for html inclusion (input, option, textarea, etc.)
	function htquote($v) { return htmlentities(stripslashes($v),ENT_QUOTES); }
	// prepare string for javascript inclusion
	function jsquote($v) { return addslashes(stripslashes($v)); }
	// prepare stirng for plain text without slashes
	function dequote($v) { return stripslashes($v); }
	// prepare string for quoted printable encoding (email headers and body)
	function qpencode($text,$header_charset="",$break_lines=1) {
		$ln=strlen($text);
		if ($ln<1) return '';
		$h=(strlen($header_charset)>0);
		$lb=XOX_LOCAL_MODE?"\r\n":"\n";
		if ($h) {
			$break_lines=0;
			for ($i=0;$i<$ln;$i++) {
				switch ($text[$i]) {
					case "=":
					case "?":
					case "_":
					case "(":
					case ")":
						break 2;
					default: $o=Ord($text[$i]); if ($o<32 || $o>127) break 2;
				}
			}
			if ($i>0) return(substr($text,0,$i).qpencode(substr($text,$i),$header_charset,0));
		}
		for ($w=$e="",$l=0,$i=0;$i<$ln;$i++) {
			$c=$text[$i];
			$o=Ord($c);
			$en=0;
			switch ($o) {
				case 9: case 32: if(!$h) { $w=$c; $c=""; } else { if($o==32) $c="_"; else $en=1; } break;
				case 10: case 13: if (strlen($w)) { if ($break_lines && $l+3>75) { $e.="=".$lb; $l=0; } $e.=sprintf("=%02X",Ord($w)); $l+=3; $w=""; } $e.=$c; $l=0; continue 2;
				default: if ($o>127||$o<32||!strcmp($c,"=")||($h && (!strcmp($c,"?")||!strcmp($c,"_")||!strcmp($c,"(")||!strcmp($c,")")))) $en=1; break;
			}
			if (strlen($w)) {
				if ($break_lines && $l+1>75) {
					$e.="=".$lb;
					$l=0;
				}
				$e.=$w;
				$l++;
				$w="";
			}
			if (strlen($c)) {
				if ($en) {
					$c=sprintf("=%02X",$o);
					$el=3;
				} else $el=1;
				if($break_lines && $l+$el>75) {
					$e.="=".$lb;
					$l=0;
				}
				$e.=$c;
				$l+=$el;
			}
		}
		if (strlen($w)) {
			if ($break_lines && $l+3>75) $e.="=".$lb;
			$e.=sprintf("=%02X",Ord($w));
		}
		return (($h && strcmp($text,$e)) ? "=?$header_charset?q?$e?=" : $e);
	}

  function jumpTo($v,$echo=true) {
		if ( $v{0}=='?' ) $v=XOX_WWW_PAGE.$v;
		if (!headers_sent()) {
			header ('Location: '.$v); exit;
		} else {
			$jump = '<script language="javascript">document.location.href=\''.$v.'\';</script>';
      if (defined('LANG_XOX_BROWSER_REDIRECT')) $jump.=sprintf(LANG_XOX_BROWSER_REDIRECT,$v);
			if ( $echo ) echo $jump;
			return $jump;
		}
	}

	function is_obj( &$object, $check=null, $strict=false ) {
		if( is_object($object) ) {
			if( $check == null ) return true;
			$object_name = get_class($object);
			if( $strict === true && $object_name == $check ) return true;
			if( $strict === false && strtolower($object_name) == strtolower($check) ) return true;
		}
		return false;
	}

} // functions included

?>
