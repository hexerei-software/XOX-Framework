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

require_once 'ScriptObject.class.php';
require_once 'MediaObjectFactory.class.php';

class PECScriptObject extends ScriptObject {
	function PECScriptObject ($pagename, $script, $isfile) {
		ScriptObject::ScriptObject($pagename, $script, $isfile);
	}

	function createMO ($format, $attr, $showMessages=false) {
		$fname = file_strip_extension($this->fname).".$format";
		$mo = MediaObjectFactory::createMediaObject($this->pagename, $fname);
		if ($this->mustReconvert($mo)) {
			$in  = $this->path();
			$out = $mo->path();
			RunTool('pec2img', "IN=$in OUT=$out");
			if ($format != 'eps' && $format != 'pdf' && isset($attr['scale'])) 
				RunTool('mogrify', "SCALE=$attr[scale] FILE=".$mo->path());
//			$mo->scale($this->attribs->getAttrib('scale'));
		}
		return $mo;
	}
}

?>
