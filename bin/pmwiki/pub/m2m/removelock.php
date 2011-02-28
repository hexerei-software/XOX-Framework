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

require_once 'debugfuncs.php';

$pagename = $_GET['n'];

if ($pagename == 'all') {
	$basedir = dirname(__FILE__)."/../../m2m.d";
	SHOW($basedir);
	$d = opendir($basedir);
	while (($fname = readdir($d)) !== false) {
		if (is_dir("$basedir/$fname") && file_exists("$basedir/$fname/.lock")) {
			echo "remove $fname/.lock<br>";
			unlink("$basedir/$fname/.lock");
		}
	}
	closedir($d);
	echo "ready";
}
else {
	@unlink("$M2MDataDir/$pagename/.lock");
}


?>
