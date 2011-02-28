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

require_once 'GnuplotScriptObject.class.php';
require_once 'LatexScriptObject.class.php';
require_once 'PECScriptObject.class.php';

class ScriptObjectFactory 
{
	function createScriptObjectFromCode ($pagename, $type, $code, $data='') {
		switch ($type) {
			case 'gnuplot' : return new GnuplotScriptObject($pagename, $code, false);
			case 'math'    : return new MathScriptObject($pagename, $code, false, $data);	// data = inline
			case 'pec'     : return new PECScriptObject($pagename, $code, false);
			case 'tipa'    : return new TIPAScriptObject($pagename, $code, false);
		}
		return false;
	}


	function createScriptObjectFromFile ($pagename, $type, $fname, $data='') {
		switch ($type) {
			case 'gnuplot': return new GnuplotScriptObject($pagename, $fname, true);
		}
		return false;
	}


	function createScriptObjectFromFilename ($pagename, $fname, $data='') {
		$ext = file_extension($fname);
		switch ($ext) {
			case 'gpt': return new GnuplotScriptObject($pagename, $fname, true);
		}
		return false;
	}
}

?>
