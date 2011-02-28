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

class SVGObject extends ImageObject
{
	function SVGObject ($pagename, $fname, $attr) {
		ImageObject::ImageObject($pagename, $fname, $attr);
	}

	function getHTML ($attr) {
		$objattr = array('type' => $this->mimeType(), 'data'=>$this->url());
		if (isset($attr['width'])) 
			$objattr['width'] = $attr['width'];
		if (isset($attr['height'])) 
			$objattr['height'] = $attr['height'];

		$attr['src'] = $this->url();
		$attr['type'] = $this->mimeType();
		unset($attr['file']);
		unset($attr['width']);
		unset($attr['height']);
		return htmlObject($objattr, $attr);
	}

}

?>
