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
require_once 'MediaFileReferences.class.php';
require_once 'MediaObjectFactory.class.php';
require_once 'eps_pdf_tools.inc.php';

class LaTeXScriptObject extends ScriptObject
{
	var $preamble; // LaTeX preamble
	var $packages; // semicolon separated list of packages (w/o \usepackage statement)
	var $openEnv, $closeEnv;
	var $remove;   // parts that match this regex are removed (e.g. for security reasons)
	var $message;  // latex message output

	function LaTexScriptObject ($pagename, $fname, $isfile, $preamble, $packages, $openEnv, $closeEnv, $remove) {
		ScriptObject::ScriptObject($pagename, $fname, $isfile);
		if ($preamble != '')
			$this->preamble = $preamble;
		else 
			$this->preamble = '\documentclass[12pt]{article}\pagestyle{empty}';
		$packages = explode(';', $packages);
		foreach ($packages as $p) 
			$this->packages .= preg_replace('/^\s*(.+?)(\[(.+?)\])?\s*$/', "\\usepackage\$2{\\1}\n", $p);
		$this->openEnv  = $openEnv;
		$this->closeEnv = $closeEnv;
		$this->remove = $remove;
	}

	function scripttype () {
		return 'latex';
	}
	
	function createMO ($format, $attr, $showMessages=false) {
		$code = preg_replace("/{$this->remove}/", '', file_get_contents($this->path()));
		$hash = file_strip_extension($this->fname);
		$mo = MediaObjectFactory::createMediaObject($this->pagename, "$hash.$format");
		if ($mo->filesize() == 0)
			$mo->remove();
		$error = false;
		if ($this->mustReconvert($mo)) {
			if ($showMessages)
				message("creating ".$this->scripttype()." image in ".strtoupper($format)." format");
			$latex = "{$this->preamble}\n{$this->packages}\n";
			$latex.=" \\begin{document}\\newsavebox{\\mybox}\n";
			$latex.= "\\sbox\\mybox{{$this->openEnv}$code{$this->closeEnv}}\n";
			$latex.= "\\usebox\\mybox\n";
			$latex.= "\\typeout{width=\\the\\wd\\mybox}\n";
			$latex.= "\\typeout{height=\\the\\ht\\mybox}\n";
			$latex.= "\\typeout{depth=\\the\\dp\\mybox}\n";
			$latex.= "\\end{document}\n";
			$dir = getcwd();
			chdir($this->dir());
			$texfile = $this->path();
			$f = fopen($texfile, "w");
			fputs($f, $latex);
			fclose($f);

			switch ($format) {
				case 'eps': $res = $this->tex2eps($texfile); break;
				case 'pdf': $res = $this->tex2pdf($texfile); break;
				default   : $res = $this->tex2format($texfile, $format); 
			}
//			$this->message = $this->latexErrorInMessage($output);
			if ($this->message || $res === false)
				$error = true;

			// remove temporary files
			foreach (array('aux', 'dvi', 'log', 'tex') as $ext)
				if ($format != $ext)
					@unlink("$hash.$ext");
			chdir($dir);
		}
		return $error ? false : $mo;
	}		

	// converts TeX file to DVI and writes the image extents to a .box file
	function tex2dvi ($texfile) {
		$dvifile = file_strip_extension($texfile).".dvi";
		$boxfile = file_strip_extension($texfile).".box";
		if (file_exists($dvifile) && file_exists($boxfile))
			return $dvifile;

		if (file_exists($texfile)) {
			$output = RunTool('latex', "TEX=$texfile", 'exec');  // text written to stdout
			$this->message = $this->latexErrorInMessage($output);
			if (file_exists($dvifile)) {
				// extract width, height and depth of typesetted text and put it in .box file
				// the depth value can be used for vertical adjustments to line up the created images with the surrounding text
				$str = implode("\n", $output);
				$boxinfo = preg_replace('/^.*\n(width=.*pt\nheight=.*pt\ndepth=.*pt\n).*$/Us', '$1', $str);
				$f = fopen($boxfile, 'w');
				fputs($f, $boxinfo);
				fclose($f);
				return $dvifile;
			}
		}
		return false;
	}

	// converts TeX file to EPS
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

	// converts TeX file to embedable PDF (calculates and assigns minimal bounding box)
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
	
	// Looks for error message in LaTeX output.
	function latexErrorInMessage ($message) {
		if (!is_array($message))
			return '';
		$res   = false;
		$found = false;
		foreach ($message as $line) {
			if ($line{0} == '!')  // error message?
				$found = true;
			if ($found) {
				if (preg_match('/^\\?/', $line))
					break;
				$res .= "$line\n";
			}
		}
		return $res;
	}


	function numberOfHeaderLines () {
		$lines  = count(explode("\n", $this->preamble));
		$lines += count(explode("\n", $this->packages));
		$lines += 1; // \begin{document}
		return $lines;
	}

	function getMarkedSource () {
		preg_match('/\nl.(\d+) (\.\.\.)?(.*?)\n/', $this->message, $m);
		$lineno = $m[1] - $this->numberOfHeaderLines();
		$line = preg_quote($m[3]);
		$line = str_replace('/', '\\/', $line);
		$source = explode("\n", $this->code);
		$source[$lineno-1] = preg_replace("/($line)/", '[error]\1[/error]', $source[$lineno-1]);
		return implode("\n", $source);
	}

}


class TIPAScriptObject extends LaTeXScriptObject
{
	function TIPAScriptObject ($pagename, $fname, $isfile) {
		$preamble = ""; // default
		$packages = 'inputenc[latin1]; babel[german]; tipa[tone,extra,safe]; tipx';
		$openEnv  = '\begin{IPA}';
		$closeEnv = '\end{IPA}';
		$remove   = '\\\\(begin|end)\s*{.*?}';  // disallow \begin and \end macros in tipa environment
		LaTeXScriptObject::LaTeXScriptObject($pagename, $fname, $isfile, $preamble, $packages, $openEnv, $closeEnv, $remove);
	}

	function scripttype () {
		return 'ipa';
	}
}


class MathScriptObject extends LaTeXScriptObject
{
	function MathScriptObject ($pagename, $fname, $isfile, $mode) {
		$preamble = ""; // default
		$packages = 'inputenc[latin1]; babel[german]; amssymb';
		if ($mode == 'inline') 
			$openEnv  = $closeEnv = '$'; 
		elseif ($mode == 'displaymath') {
			$openEnv = '$\displaystyle ';			
			$closeEnv = '$';
		}
		else {
			$openEnv  = "\\vbox{\\begin{".$mode."}";
			$closeEnv = "\\end{".$mode."}}";
		}
//		$remove   = '(\\\\[()\[\]])|\$';  // disallow \(, \), \[, \], $
		LaTeXScriptObject::LaTeXScriptObject($pagename, $fname, $isfile, $preamble, $packages, $openEnv, $closeEnv, $remove);
	}
	
	function scripttype () {
		return 'math';
	}

	function createMathML ($inlined) {
		$mathmark = $inlined ? '$' : '$$';  // latex source is expected to be enclosed by $...$ or $$...$$
		$source = $mathmark.trim($this->code).$mathmark;

		// header code taken from tex4ht script 'mzlatex'
		$head  =	'\makeatletter'."\n";
		$head .= '\def\HCode{\futurelet\HCode\HChar}'."\n";
		$head .= '\def\HChar{\ifx"\HCode\def\HCode"##1"{\Link##1}\expandafter\HCode\else\expandafter\Link\fi}'."\n";
		$head .= '\def\Link#1.a.b.c.{%'."\n";
		$head .= '  \g@addto@macro\@documentclasshook{\RequirePackage[#1,xhtml,mozilla]{tex4ht}}'."\n";
		$head .= '  \let\HCode\documentstyle'."\n";
		$head .= '  \def\documentstyle{%'."\n";
		$head .= '	  \let\documentstyle\HCode'."\n";
		$head .= '    \expandafter\def\csname tex4ht\endcsname{#1,xhtml,mozilla}'."\n";
		$head .= '	  \def\HCode####1{\documentstyle[tex4ht,}'."\n";
		$head .= '	  \@ifnextchar[{\HCode}{\documentstyle[tex4ht]}}}'."\n";
		$head .= '\makeatother' ."\n";
		$head .= '\HCode .a.b.c.'."\n";
		$head .= '\documentclass{article}';

		$cwd = chdir($this->outputDir);
		$fname = md5($source);
		$f = fopen("$fname.tex", 'w');
		fputs($f, "$head\n\\begin{document}\n$source\n\\end{document}\n");
		fclose($f);

		RunTool('latex', "TEX=$fname");
		RunTool('tex4ht', "TEX=$fname");
		RunTool('t4ht', "TEX=$fname");
//		execute("$LATEX $fname");
//		execute("$TEX4HT -f$fname -i$TEX4HTDIR/texmf/tex4ht/ht-fonts -cmozhtf");
//		execute("$T4HT -f$fname -cvalidate ##");

		$mml = file_get_contents("$fname.xml");
		$mml = preg_replace('#^.*(<math.*>.*</math>).*$#Usi', '\1', $mml);  // pick out MathML
		$mml = preg_replace('/<!--.*?-->/', '', $mml);  // remove comments
//		$mml = preg_replace('/\s*\n\s*(.*?)>/', '\1>', $mml);  // compact output
		// remove temporary files
		$ext = array('4ct', '4tc', 'aux', 'css', 'dvi', 'html', 'idv', 'lg', 'log', 'tex', 'tmp', 'xml', 'xref');
		foreach ($ext as $e)
			@unlink("{$this->outputDir}/$fname.$e");
		return $mml;
	}
}

?>
