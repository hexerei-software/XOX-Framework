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


function getBoundingBox ($fname) {
	$ext = file_extension($fname);
	if ($ext == 'eps')
		return getEPSBoundingBox($fname);
	if ($ext == 'pdf')
		return getPDFBoundingBox($fname);
	return false;
}

function getEPSBoundingBox ($fname) {
	$f = fopen($fname, 'r');
	$ret = false;
	while (!feof($f)) {
		$line = fgets($f);
		if (preg_match('/^%%BoundingBox:\s*(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s*$/', $line, $m)) {
			$ret = array('x1'=>$m[1], 'y1'=>$m[2], 'x2'=>$m[3], 'y2'=>$m[4]);
			break;
		}
	}
	fclose($f);
	return $ret;
}


function getPDFBoundingBox ($fname) {
	$f = fopen($fname, 'r');
	$ret = false;
	while (!feof($f)) {
		$line = fgets($f);
		if (!$inobj && preg_match('/^\d+\s+\d+\s+obj$/', $line)) // start of new object?
			$inobj = true;
		elseif ($inobj && preg_match('/\bendobj\n/', $line))     // end of current object?
			$inobj = $inpageobj = false;
		elseif ($inobj && preg_match('#/Type\s*/Page\b#', $line))// in page object?
			$inpageobj = true;

		if ($inpageobj && preg_match('#\wMediaBox\s*\[\s*(\S+)\s+(\S+)\s+(\S+)\s+(\S+)s*(\S+)\s*\]#', $line, $m)) {
			$ret = array('x1'=>$m[1], 'y1'=>$m[2], 'x2'=>$m[3], 'y2'=>$m[4]);
			break;
		}
	}
	fclose($f);
	return $ret;
}


function addHiResBoundingBoxToEPS ($epsfile) {
	if (!file_exists($epsfile))
		return false;
	$bbox = RunTool('gs', "DEVICE=bbox IN=$epsfile", 'pipe');
	if (!preg_match('/^%%BoundingBox/', $bbox))  // ghostscript call failed?
		return false;

	$eps = file_get_contents($epsfile);
	$eps = preg_replace('/\n%%BoundingBox:.*?\n/', "\n$bbox", $eps);
	$f = fopen("$epsfile.new", 'w');
	fputs($f, $eps);
	fclose($f);
	if (file_exists("$epsfile.new")) {
		unlink($epsfile);
		rename("$epsfile.new", $epsfile);
		return file_exists($epsfile);
	}
	return false;
}


// Computes and assigns the minimal bounding box to a given pdf file.
// Returns true on success.
function assignMinimalBoundingBoxToPDF ($pdffile) {
	if (!file_exists($pdffile))
		return false;
	// compute new bounding box
	$bbox = RunTool('gs', "DEVICE=bbox IN=$pdffile", 'pipe');
	if (!preg_match('/^%%BoundingBox/', $bbox))  // ghostscript call failed?
		return false;
	$bbox = preg_replace('/^.*%%HiResBoundingBox:\s*(.*?)\n$/s', '$1', $bbox);

	// adapt pdf file
	// This is a first implementation that should work with most pdf files but 
	// can't be considered as 100% reliable.
	$f = fopen($pdffile, 'rb');
	$g = fopen("$pdffile.new", 'wb');
	$count = 0;
	$inobj = $inpageobj = $nocount = false;
	while (!feof($f)) {
		$line = fgets($f);
		if (!$inobj && preg_match('/^\d+\s+\d+\s+obj$/', $line)) // start of new object?
			$inobj = true;
		elseif ($inobj && preg_match('/\bendobj\n/', $line))     // end of current object?
			$inobj = $inpageobj = false;
		elseif ($inobj && preg_match('#/Type\s*/Page\b#', $line))// in page object?
			$inpageobj = true;
		elseif ($line == "xref\n") {  // xref section reached?
			fputs($g, $line);
			// adapt xref pointers
			while (($line = fgets($f)) != "trailer\n") {
				if (preg_match('/^(\d{10}) (\d{5} \w )/', $line, $m) && $m[1]>=$checkoffs)
					$line = sprintf("%010d %s\n", $m[1]+$diff, $m[2]);
				fputs($g, $line);
			}
			$nocount = true;  // stop byte counting
		}
		elseif ($line == "startxref\n") {
			// adapt pointer to start of xref section
			fputs($g, "startxref\n$count\n%%EOF");
			break;
		}

		if ($inpageobj && preg_match('#^(.*?/MediaBox\s*)\[(.*?)\](.*?)$#', $line, $m)) {
			// adapt bounding box
			$checkoffs = $count+strlen($line);
			$line = "$m[1][$bbox]$m[3]\n";
			$diff = strlen($bbox)-strlen($m[2]);
		}
		if (!$nocount)
			$count += strlen($line);
		fputs($g, $line);
	}
	fclose($g);
	fclose($f);
	if (file_exists("$pdffile.new")) {
		unlink($pdffile);
		rename("$pdffile.new", $pdffile);
		return file_exists($pdffile);
	}
	return false;
}

?>
