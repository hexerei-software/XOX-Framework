<?
/******************************************************************************
** This file is part of the PMWiki extension media2mult.                     **
** Copyright (c) 2005-2008 Zentrum virtUOS, University of Osnabrück, Germany **
**                                                                           **
** This program is free software; you can redistribute it and/or             **
** modify it under the terms of the GNU General Public License               **
** as published by the Free Software Foundation; either version 2            **
** of the License, or (at your option) any later version.                    **
**                                                                           **
** This program is distributed in the hope that it will be useful,           **
** but WITHOUT ANY WARRANTY; without even the implied warranty of            **
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             **
** GNU General Public License for more details.                              **
**                                                                           **
** You should have received a copy of the GNU General Public License         **
** along with this program; if not, write to the Free Software               **
** Foundation, Inc., 51 Franklin Street, Fifth Floor,                        **
** Boston, MA 02110-1301, USA.                                               **
*******************************************************************************/


if (substr(phpversion(), 0, strpos(phpversion(), '.')) < 5) {
	function stream_get_contents ($stream) {
		while (!feof($stream))
			$ret .= fgets($stream);
		return $ret;
	}
}

function umlauts ($str) {
	$uml = array('ä'=>'auml', 'ö'=>'ouml', 'ü'=>'uuml', 'Ä'=>'Auml', 'Ö'=>'Ouml', 'Ü'=>'Uuml', 'ß'=>'szlig');
	foreach ($uml as $x=>$y)
		$str = str_replace($x, "&$y;", $str);
	return $str;
}

function substitute_umlauts ($str) {
	$uml = array('ä'=>'ae', 'ö'=>'oe', 'ü'=>'ue', 'Ä'=>'Ae', 'Ö'=>'Oe', 'Ü'=>'Ue', 'ß'=>'ss');
	foreach ($uml as $x=>$y)
		$str = str_replace($x, $y, $str);
	return $str;
}

function number2boolstr ($num) {
	if (!is_numeric($num))
		return $num;
	if ($num == 0)
		return 'false';
	return 'true';
}


function message ($msg, $blockMode=false, $color='', $newline=true) {
	static $indentLevel=0;
	static $continueLine=false;
	if ($blockMode == 'end') 
		$indentLevel = max(0, $indentLevel-1);
	if ($msg !== '') {
		if ($color)
			$msg = "<font color='$color'>$msg</font>";
		if ($continueLine && $newline) {
			$continueLine = false;
			echo "<br>";
		}
		$space = ($indentLevel > 0 && !$continueLine) ? str_repeat('&nbsp;', $indentLevel*5) : '';
		echo "$space$msg";
		if ($newline) 
			echo "<br>\n";
		else
			$continueLine = true;
		flush();
	}
	if ($blockMode == 'start')
		$indentLevel++;
}

function error_message ($msg, $halt) {
	$msg = "<font color='red'>$msg</font>";
	if ($halt)
		die($msg);
	echo $msg;
}



function recursive_mkdir ($dir) {
	$ret = true;
	if (!file_exists($dir)) {
		$ret &= recursive_mkdir(dirname($dir));
		if (!mkdir($dir, 0777))
			return false;
	}
	return $ret;
}

function chdir_mkdir ($dir) {
	recursive_mkdir($dir);
	$cwd = getcwd();
	chdir($dir);
	return $cwd;
}

function match_filenames ($dirname, $regex) {
	$d = opendir($dirname);
	if ($d === false)
		return false;

	$ret = array();
	while (($fname = readdir($d)) !== false)
		if (preg_match($regex, $fname))
			$ret[] = $fname;
	closedir($d);
	return $ret;
}


/* converts a string like "foo1=bar1 foo2="bar2 baz2" foo3=bar3"  
   to an array('foo1'=>'bar1', 'foo2'=>'bar2 baz2', 'foo3'=>'bar3') */
function makeAttribArray ($str) {
	$ret = array();
	$str = trim($str);
	if ($str != '') {
		while (preg_match('/^((?:\w|-)+)\s*=\s*(\\\\?"?)(.+?)\2(\s+(.*))?$/', $str, $m)) {		
			if ($m[1] && $m[3])
				$ret[$m[1]] = $m[3];
			$str = count($m) > 4 ? $m[5] : '';
		}
	}
	return $ret;
}


function redir_exec ($command, $stdin) {
	$spec = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'));
	$proc = proc_open($command, $spec, $pipes);
	if (is_resource($proc)) {
		fputs($pipes[0], $in);
		fclose($pipes[0]);
		$stdout = fgets($pipes[1]);
		fclose($pipes[1]);
		proc_close($proc);
		return $stdout;
	}
	return false;
}


function file_extension ($fname) {
	$dotpos = strrpos($fname, '.');
	if ($dotpos === false)
		return '';
	return substr($fname, $dotpos+1);
}


function file_strip_extension ($path) {
	$dir = dirname($path);
	$fname = basename($path);
	$dotpos = strrpos($fname, '.');
	if ($dotpos === false)
		return $path;
	return "$dir/".substr($fname, 0, $dotpos);
}


function file_replace_extension ($fname, $ext) {
	return file_strip_extension($fname) . ".$ext";
}


function filetime_diff ($fname1, $fname2) {
	$fname1 = is_link($fname1) ? readlink($fname1) : $fname1;
	$fname2 = is_link($fname2) ? readlink($fname2) : $fname2;
	$time1  = $fname1 != 0 ? filemtime($fname1) : 0;
	$time2  = $fname2 != 0 ? filemtime($fname2) : 0;
	return $time1-$time2;
}

function mtime_follow_link ($fname) {
	if (is_link($fname))
		$fname = readlink($fname);
	return filemtime($fname);
}


function pipe_execute ($cmd, $stdin='') {
	$pipedescr = array(0=>array('pipe', 'r'), 1=>array('pipe', 'w'));	
	$proc = proc_open($cmd, $pipedescr, $pipes);
	if (is_resource($proc)) {
		if ($stdin != '')
			fwrite($pipes[0], $stdin);
		fclose($pipes[0]);
		$ret = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		proc_close($proc);
		return $ret;
	}
	return false;
}



function get_mime_type ($fname) {
	switch (file_extension($fname)) {
		// text types
		case 'txt' : return 'text/plain';
		case 'htm' :
		case 'html': return 'text/html';
		case 'css' : return 'text/css';						 
						 
		// image types
		case 'jpg' :
		case 'jpeg': return 'image/jpeg';
		case 'bmp' : return 'image/x-ms-bmp';
		case 'png' : return 'image/png';
		case 'gif' : return 'image/gif';
		case 'svg' : return 'image/svg+xml';
		case 'wrl' : return 'x-world/x-vrml';
						 
		// audio types
		case 'wav' : return 'audio/x-wav';
		case 'mp3' : return 'audio/mpeg';
		case 'mid' : return 'audio/x-midi';
						 
		
		// video types				
      case "mpe" :
      case "mpeg":
      case "mpg" : return "video/mpeg";
      case "mp4" : return "video/mp4";
      case "mov" :
      case "qt"  : return "video/quicktime";
//      case "mp4"  : return "video/quicktime";
      case "avi" : return "video/x-msvideo";

		// application types
		case 'ra'  :
		case 'ram' : return 'application/x-pn-realaudio-plugin';
		case 'rtf' : return 'application/rtf';
		case 'pdf' : return 'application/pdf';
		case 'ps'  : return 'application/postscript';
		case 'doc' : return 'application/msword';
		case 'xls' : return 'application/ms-excel';
		case 'ppt' : return 'application/ms-powerpoint';
		case 'gz'  :
		case 'tgz' : return 'application/x-gzip';
		case 'bz2' : return 'application/x-bzip2';				 
		case 'zip' : return 'application/zip';				 
		case 'swf' : return 'application/x-shockwave-flash';
						 
		default    : return 'application/octet-stream';				 
	}
}

function xmlencode ($text) {
	$text = str_replace('&', '&amp;', $text);
	$text = str_replace('<', '&lt;', $text);
	$text = str_replace('"', '&quot;', $text);
	return $text;
}


function errorHTML ($msg) {
	$ret = "<table cellpadding='5' style='border-width:thin; border-style:solid'><tr><td align='center' bgcolor='#ffffa0'>";
	$ret.= $msg;
	$ret.= "</td></tr></table>";
	return $ret;
}

function htmlObject ($attribs, $params) {
	$ret = "<object";
	foreach ($attribs as $a=>$v)
		$ret .= " $a='$v'";
	$ret .= ">\n";
	foreach ($params as $name=>$value)
		$ret .= "<param name=\"$name\" value=\"$value\"/>\n";
	$ret .= "<embed ";
	unset($attribs['data']);
	foreach ($attribs as $name=>$value)
		$ret .= " $name=\"$value\"";
	foreach ($params as $name=>$value)
		$ret .= " $name=\"$value\"";
	$ret .= "/>\n</object>";
	return $ret;
}

?>
