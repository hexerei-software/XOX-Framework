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
require_once 'SyntaxHighlighter.class.php';

class CodeObject extends ScriptObject
{
	var $openElements = array();

	function CodeObject ($pagename, $code, $isfile=true) {
		ScriptObject::ScriptObject($pagename, $code, $isfile);
	}
	
	function type ()           {return 'code';}

	function convert ($format, $attr) {return false;}
	
	function getDocBook ($role, $attr) {
		$code = htmlspecialchars($this->getCode());
		return "<programlisting>$code</programlisting>";
	}


	function getWikiXML ($role, $attr) {
		$code = trim($this->getCode());
		$xml= SyntaxHighlighter::getXML($code, $attr['lang']);
		$lines = explode("\n", $xml);
		$ret = "";
		$xc  = '[a-zA-Z0-9.:_-]+';
		foreach ($lines as $l) {
			$ret .= "<line>$l</line>\n";
		}
		unset($this->openElements);
		return "<programlisting>$ret</programlisting>\n";
	}

	function openElement ($name, $attrstr) {
		array_unshift($this->openElements, "$name\001$attrstr");
		return '';
	}

	function closeElement ($name) {
		if (count($this->openElements) > 0) {
			list($e, $a) = explode("\001", $this->openElements[0]);
			if ($e == $name)
				array_shift($this->openElements);
		}
		return '';
	}

	function addTags ($line) {
		$xc  = '[a-zA-Z0-9.:_-]+';
		$ready = false;
		while (!$ready) {
			$line = preg_replace("/^([^<]*)<($xc)(.*?)>/e", "'$1'.\$this->openElement('$2', '$3')", $line, 1, $c);
			$ready = $ready || ($c > 0);
			$line = preg_replace("#^[^<]*</($xc)>#e", "\$this->closeElement('$1')", $line, 1, $c);
			$ready = $ready || ($c > 0);
		}
		$line = "";
		foreach ($this->openElements as $e) {
			list($e, $a) = explode("\001", $e);
			$line .= "<$e$a>";
		}
		$line .= $l;
		foreach (array_reverse($this->openElements) as $e) {
			list($e, $a) = explode("\001", $e);
			$line .= "</$e>";
		}
		return $line;
	}
	
	function getHTML ($attr) {
		$code = trim($this->getCode());
		$html = SyntaxHighlighter::getHTML($code, $attr['lang']);
		$lines = explode("\n", $html);
		$ret = "";
		$count = 1;		
		foreach ($lines as $l) {
//			$l = $this->addTags($l);
			$ret .= "<tr><td align='right'><font color='#999999'>$count&nbsp;</font></td><td>$l</td></tr>\n";
			$count++;
		}
		$this->openElements = array();
		return "<table cellpadding='3'><tr><td bgcolor='#eeeeee'><pre><table>\n$ret</table></pre></td></tr></table>\n";
	}

	function getCode () {
		$code = ($this->code == '') ? file_get_contents($this->path()) : $this->code;
		return $code;
	}
}

?>
