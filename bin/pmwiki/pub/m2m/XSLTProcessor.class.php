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

$XSLTProcessorPrefixes = array('gnome', 'saxon', 'xalan');

class XSLTProcessorFactory {
	function createProcessor ($name) {
		switch ($name) {
			case 'xsltproc' : return new GnomeXSLTProcessor;
			case 'saxon'    : return new SaxonXSLTProcessor;
			case 'xalan'    : return new XalanXSLTProcessor;
			default         : return new GnomeXSLTProcessor;
		}
	}

	function createProcessorByOption ($opt) {
		if (is_string($opt))
			$opt = new StylesheetOptions($opt);
		$conv = $opt->getValue('processor.xslt');
		return XSLTProcessorFactory::createProcessor($conv);
	}

	function availableProcessors () {
		global $XSLTProcessorPrefixes;
		foreach ($XSLTProcessorPrefixes as $prefix) {
			$descr = eval("return {$prefix}XSLTProcessor::descrString();");  // PHP identifiers are case-insensitive
			$ret[$prefix] = $descr;
		}
		return $ret;
	}
}


class XSLTProcessor2 {  // XSLTProcessor is already defined in PHP 5
	var $options = array();
	var $error = false;
	
	function setOption ($key, $value) {
		$this->options[$key] = $value;
	}

	function getOptionString ($format) {
		foreach ($this->options as $k=>$v) {
			$format = str_replace('%k', $k, $format);
			$format = str_replace('%v', $v, $format);
			$ret .= " $format";
		}
		return $ret;
	}
}


class GnomeXSLTProcessor extends XSLTProcessor2 {
	function descrString () {return "xsltproc";}
		
	function process ($xsl, $xml, $out) {
		if (!file_exists($xsl))
			die("stylesheet file '$xsl' not found");
		if (!file_exists($xml))
			die("XML file '$xml' not found");
//		global $XSLTPROC;
		$opt = $this->getOptionString('--stringparam %k %v');
		if ($opt)
			$opt = "STRINGPARAM=\"$opt\"";
		$status = RunTool('xsltproc', "XSL=$xsl XML=$xml OUT=$out $opt", 
		                  'pipe-callback', 
								array('GnomeXSLTProcessor', 'messageCallback'));
		$this->error = ($status != 0);
//		echo "<font color='red'><xmp>";
//		passthru("$XSLTPROC -o $out $opt $xsl $xml 2>&1");
//		echo "</xmp></font>";
	}

	function messageCallback ($line) {
		$color = 'green';
		$line = preg_replace('/^\s*(\^)|(\bunable to parse\b.*)\s*$/', '', $line);
		$line = preg_replace('/^(.+?)(\:\d+:.+)$/e', "basename('$1').'$2'", $line);
		if (preg_match('/(\berror\b)|(<[^<]+>)/', $line))
			$color = 'red';
		$line = str_replace('<', '&lt;', $line);
		message($line, '', $color);
	}
}


class SaxonXSLTProcessor extends XSLTProcessor2 {
	function descrString () {return "Saxon";}
	
	function process ($xsl, $xml, $out) {
		if (!file_exists($xsl))
			die("stylesheet file '$xsl' not found");
		if (!file_exists($xml))
			die("XML file '$xml' not found");
//		global $SAXON;
		RunTool('saxon', "XSL=$xsl XML=$xml OUT=$out");
//		passthru("$SAXON -o $out $xml $xsl");
	}
}


class XalanXSLTProcessor extends XSLTProcessor2 {
	function descrString () {return "Xalan-J";}
	
	function process ($xsl, $xml, $out) {
		if (!file_exists($xsl))
			die("stylesheet file '$xsl' not found");
		if (!file_exists($xml))
			die("XML file '$xml' not found");
//		global $XALAN;
		RunTool('xalan', "XSL=$xsl XML=$xml OUT=$out");
//		passthru("$XALAN -IN $xml -XSL $xsl -OUT $out");
	}
}

?>
