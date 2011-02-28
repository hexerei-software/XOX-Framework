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

	
function traceback ($fofile, $lineno, $colno) {
	$f = fopen($fofile, 'r');
	while (!feof($f) && $lineno-- > 0) 
		$line = fgets($f);
	fclose($f);
	if ($lineno != -1)
		return false;
	$line = substr($line, $colno-1);  // remove leading characters
	if (preg_match('/<fo:external-graphic src="(.+?)"/', $line, $m)) {
		$imgpath = $m[1];
		$img = basename($imgpath);
		if (!file_exists(dirname($fofile)."/source.xml"))
			return false;

		$f = fopen(dirname($fofile)."/source.xml", "r");
		$pagename = "";
		while (!feof($f)) {
			$line = fgets($f);
			if (preg_match('/^<!-- wiki page: (.+?) -->/', $line, $m))
				$pagename = $m[1];
			elseif (preg_match("/<imagedata fileref=\"[^\"]*?$img\"/", $line, $m))
				$found[$pagename] = 1;
		}
		fclose($f);
		return count(array_keys($found)) > 0 ? array_keys($found) : false;
	}
}

?>
