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

require_once 'cmdlinetools.php';
require_once 'functions.php';

class SyntaxHighlighter
{				
	function getHTML ($code, $lang='') {
		if (trim($code) == '')
			return '';
		if (trim($lang) == '') 
			return htmlentities($code);
		
		global $M2MDir;
		$lang = SyntaxHighlighter::checkLanguageType($lang);
		$lang = ($lang === false)? '' : "LANG=$lang";
		$code = RunTool('enscript', "OUTFMT=html $lang", 'pipe', $code);                    // colorize code
		$code = preg_replace('#^.*?<pre>\s*(.*?)\s*</pre>.*$#is', '<pre>\1</pre>', $code);  // remove header and footer
		$code = "<?xml version='1.0' encoding='iso-8859-1'?>\n$code";                       // add XML header
		$code = RunTool('xsltproc', "XSL=$M2MDir/xsl/tolower.xsl XML=-", 'pipe', $code);        // make lower case html elements
		$code = preg_replace('#^.*?<pre>(.*)</pre>.*$#s', '\1', $code);                     // remove xml-header and <pre> element
		return $code;
	}


	function getXML ($code, $lang='') {
		if (trim($code) == '')
			return '';
		if (trim($lang) == '') 
			return xmlencode($code);
		return SyntaxHighlighter::getHTML($code, $lang);
	}


	function checkLanguageType ($lang) {
		$types = array(
			'ada', 'asm', 'awk', 'bash', 'c', 'changelog', 'cpp', 'csh', 'delphi', 
			'diff', 'diffs', 'diffu', 'elisp', 'fortran', 'fortran_pp', 
			'haskell', 'html', 'idl', 'inf', 'java', 'javascript', 'ksh', 'm4', 
			'mail', 'makefile', 'maple', 'matlab', 'modula_2', 'nroff', 
			'objc', 'outline', 'pascal', 'perl', 'postscript', 'pyrex', 'python', 
			'rfc', 'scheme', 'sh', 'skill', 'sql', 'states', 'synopsys',
			'tcl', 'tcsh', 'tex', 'vba', 'verilog', 'vhdl', 'vrml', 'wmlscript', 
			'xml'=>'html', 'zsh');
		
		if ($types[$lang] != '') 
			return $types[$lang];
		if (array_search($lang, $types))
			return $lang;
		return false;
	}
}

?>
