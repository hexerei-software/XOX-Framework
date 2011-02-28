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


require_once 'MediaObject.class.php';
require_once 'MediaObjectFactory.class.php';

// class converting Gnuplot scripts
class FigImageObject extends ImageObject {

	function FigImageObject ($pagename, $fname, $attribs) {
		ImageObject::ImageObject ($pagename, $fname, $attribs);
	}

	function htmlready () {
		return false;
	}

	function convert ($format, $attr) {
		$currentFormat = $this->format();
		$format = strtolower(trim($format));
		if ($format == '')
			return false;
		if ($currentFormat == $format)
			return $this;
			
		$name = file_strip_extension($this->fname);  // remove file extension
		$newMO = MediaObjectFactory::createMediaObject($this->pagename, "$name.$format");
		if (!$newMO->exists() || $newMO->olderThan($this)) {
			if ($showMessages)
				message("creating FIG image in ".strtoupper($format)." format", 'start');
			RunTool('fig2dev', "IN=".$this->path()." OUT=".$mo->path()." FORMAT=$format");
		}
		return $newMO;
	}
}
?>


