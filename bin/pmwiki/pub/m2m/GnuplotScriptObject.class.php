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
require_once 'eps_pdf_tools.inc.php';

// class converting Gnuplot scripts
class GnuplotScriptObject extends ScriptObject {

	function GnuplotScriptObject ($pagename, $script, $isfile) {
		ScriptObject::ScriptObject ($pagename, $script, $isfile);
		if ($isfile)
			$this->linkUploadedFile($script);
	}

	function createMO ($format, $attr, $showMessages=false) {
		//      Example:
		//      set terminal gif
		//      set output "test.gif"
		//      plot x**2

		$fname = file_strip_extension($this->fname).".$format";
		$mo = MediaObjectFactory::createMediaObject($this->pagename, $fname);
		if ($this->mustReconvert($mo)) {
			if ($showMessages)
				message("creating gnuplot image in ".strtoupper($format)." format", 'start');
			$scripthead = "cd '".$mo->dir()."'\nset output '$fname'\n";
			switch ($format) {
				case "gif":	$scripthead .= "set term gif transparent crop";	break;
				case "png":	$scripthead .= "set term png truecolor crop";	break;
				case 'eps': $scripthead .= "set term postscript eps color"; break;
				case 'pdf': $scripthead .= "set term pdf color"; break;
			}
			$script = file_get_contents($this->path());
			$script = preg_replace('/^\s*set?\s+((o)|(ou\w*))/', '', $script); // remove "set output" statements
			$script = preg_replace('/^\s*set?\s+t\w/', '', $script);           // remove "set terminal" statements
			$script = "$scripthead\n$script";
			$tmpfile = $this->path().".tmp";
			$f = fopen($tmpfile, "w");
			fputs($f, $script);
			fclose($f);
			RunTool('gnuplot', "SCRIPT=$tmpfile");
//			unlink($tmpfile);
			if ($format == 'pdf') {
				// the gnuplot generated bounding box is too wide, so we
				// compute and assign a minimal box
				if ($showMessages)
					message("adapting bounding box");
//				assignMinimalBoundingBoxToPDF($mo->path());
			}
			// @@ the current gnuplot version doesn't handle transparency correctly, so we apply it seperately
			// @@ can hopefully be removed again
			elseif ($format == 'png')
				RunTool('convert', "IN=".$mo->path()." OUT=".$mo->path()." TRANSPARENT=white");

			if ($format != 'eps' && $format != 'pdf' && isset($attr['scale'])) 
				RunTool('mogrify', "SCALE=$attr[scale] FILE=".$mo->path());
			if ($showMessages)
				message("", 'end');
		}
		return $mo;
	}
}
?>
