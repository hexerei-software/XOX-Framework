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

	function convert ($format, $attr, $showMessages=false) {
		$currentFormat = $this->format();
		$format = strtolower(trim($format));
		if ($format == '')
			return false;
		if ($currentFormat == $format)
			return $this;
			
		$name = file_strip_extension($this->fname);  // remove file extension		
		$mo = MediaObjectFactory::createMediaObject($this->pagename, "$name.$format");
		if (!$mo->exists() || $mo->olderThan($this)) {
			if ($showMessages)
				message("creating FIG image in ".strtoupper($format)." format", 'start');
			// fig2dev doesn't like DOS/Win newlines => convert newlines to UNIX format
			// if $this->path() is a symbolic link we convert the actual fig file (link target)
			$path = $this->path();
			if (is_link($path))
				$path = readlink($path);
			RunTool('dos2unix', "IN=$path");
			$resfile = $this->fig2format($this->path(), $format);
			return $resfile ? $mo : false;

		}
		return $mo;
	}

	function fig2format ($path, $format) {
		$resfile = file_strip_extension($path).".$format";
		if (file_exists($resfile))
			return $resfile;
		$cwd = getcwd();
		chdir(dirname($path));
		$fname = basename($path);
		RunTool('fig2dev', "IN=$fname OUT=$fname.pstex FORMAT=pstex");
		RunTool('fig2dev', "IN=$fname OUT=$fname.pstex_t FORMAT=pstex_t OPT=-p$fname.pstex");

		if (!file_exists("$fname.pstex") || !file_exists("$fname.pstex_t"))
			return false;
		
		$latex = "\\documentclass{article}\n"
			    . "\\usepackage[latin1]{inputenc}\n"
			    . "\\usepackage{graphicx}\n"
			    . "\\usepackage{german}\n"
			    . "\\usepackage{amssymb}\n"
				 . "\\pagestyle{empty}\n"
				 . "\\setlength{\\paperwidth}{50cm}\\setlength{\\paperheight}{50cm}\n"
				 . "\\setlength{\\textwidth}{\\paperwidth}\\setlength{\\textheight}{\\paperheight}\n"
				 . "\\setlength{\\oddsidemargin}{-1in}\\setlength{\\topmargin}{-1in}\n"
				 . "\\begin{document}\n"
				 . "\\input $fname.pstex_t\n"
				 . "\\end{document}\n";
		file_put_contents("$fname.tex", $latex);

		$res = $this->tex2format("$fname.tex", $format);
		if ($res)
			rename($res, file_strip_extension($fname).".$format");

		foreach (array('aux', 'dvi', 'log', 'tex', 'pstex', 'pstex_t') as $ext)
			if (file_exists("$fname.$ext"))
				unlink("$fname.$ext"); 
		chdir($cwd);
		return $res;
	}


	function tex2dvi ($texfile) {
		$dvifile = file_strip_extension($texfile).".dvi";
		if (file_exists($dvifile))
			return $dvifile;

		if (file_exists($texfile)) {
			$output = RunTool('latex', "TEX=$texfile", 'exec');  // text written to stdout
			if (file_exists($dvifile))
				return $dvifile;
		}
		return false;
	}


	function tex2eps ($texfile) {
		$epsfile = file_strip_extension($texfile).'.eps';
		if (file_exists($epsfile))
			return $epsfile;

		$dvifile = $this->tex2dvi($texfile);
		if ($dvifile) {
			RunTool('dvips', "IN=$dvifile OUT=$epsfile EPS=");
			addHiResBoundingBoxToEPS($epsfile);  // add high resolution bounding box to eps (is more exact)
			return file_exists($epsfile) ? $epsfile : false;
		}
		return false;
	}


	function tex2pdf ($texfile) {
		$pdffile = file_strip_extension($texfile).".pdf";
		if (file_exists($pdffile))
			return $pdffile;

		$epsfile = $this->tex2eps($texfile);
		if ($epsfile) {
	//		addHiResBoundingBoxToEPS($epsfile);  // add high resolution bounding box to eps (is more exact)
			RunTool('epstopdf', "IN=$epsfile OUT=$pdffile HIRES=");
			return file_exists($pdffile) ? $pdffile : false;
		}
		return false;
	}

	function tex2format ($texfile, $format) {
		if ($format == 'eps')
			return $this->tex2eps($texfile);
		if ($format == 'pdf')
			return $this->tex2pdf($texfile);

		$resfile = file_strip_extension($texfile).".$format";
		if (file_exists($resfile))
			return $resfile;

		$epsfile = $this->tex2eps($texfile);
		if ($epsfile) {
			RunTool('convert', "DENSITY=110x110 TRANSPARENT=white IN=$epsfile OUT=$resfile");
			return file_exists($resfile) ? $resfile : false;
		}
		return false;
	}
	
}
?>
