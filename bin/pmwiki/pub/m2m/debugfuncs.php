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


function SHOW ($x, $rule=true, $color='') {
	if ($x) {
		if ($color)
			print "<font color='$color'>";
		print "<xmp>";
		if (is_array($x) || is_object($x)) 
			print_r($x);
		else
			print $x;
		print "</xmp>";
		if ($color)
			print "</font>";
	}
	if ($rule)
		print "<hr>";
	print "\n";
}


function READ ($fname) {
	$f = fopen($fname, "r");
	while (!feof($f))
		$y .= fgets($f);
	fclose($f);
	return $y;
}


function WRITE ($fname, $x) {
	$f = fopen($fname, "w");
	if (is_array($x) || is_object($x))
		$x = print_r($x, true);
	fputs($f, "$x\n");
	fclose($f);
}


function APPEND ($fname, $x) {
	$f = fopen($fname, "a");
	if (is_array($x) || is_object($x))
		$x = print_r($x, true);
	fputs($f, "$x\n");
	fclose($f);
}

function my_error_handler ($errno, $errmsg, $errfile, $errline, $errcontext) {
	if ($errno == 2048 || $errno== 8)
		return;

	$trace = debug_backtrace();
	$dir = dirname($errfile);
	$fname = basename($errfile);
	echo "<table>";
	echo "<tr bgcolor='#ffcc00'><td colspan='3'>";
	echo "<b>$errmsg</b> ($errno)<br>";
	echo "$dir/<font color='red'>$fname</font>, line $errline</td></tr>";
	foreach ($trace as $t) {
  		if ($t['function'] != 'my_error_handler') {
			$dir = dirname($t['file']);
			$fname = basename($t['file']);
			echo "<tr bgcolor='ff9900'>";
			echo "<td>function <b>$t[function]</b></td>";
			echo "<td>$dir/<font color='blue'>$fname</font>";
			echo "<td>line $t[line]</td>";
			echo "</tr>";
		}
	}
	echo "</table>";	
}

function enable_debugging () {
	set_error_handler('my_error_handler');
}

?>
