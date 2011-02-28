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

require_once 'debugfuncs.php';

class WellFormer {
	var $out;
	var $elementStack;
	var $numNestingErrors;
	var $numCloseErrors;


	function processFile ($infile, $outfile) {
		$xml = file_get_contents($infile);
		$xc  = '[a-zA-Z0-9.:_-]';
		$av  = '(("[^"]*")|(\'[^\']*\'))';
		$this->out = fopen($outfile, 'wb');
		$this->elementStack = array();		
		$this->numNestingErrors = 0;
		$this->numCloseErrors = 0;
		while ($xml) {
			$xml = preg_replace("#^<($xc+)((\\s+$xc+\\s*=\\s*$av)*)\\s*>#es", "\$this->startElement('$1', '$2')", $xml);
			$xml = preg_replace("#^</($xc+)>#es",      "\$this->endElement('$1')", $xml);
			$xml = preg_replace("#^<($xc+)((\\s+$xc+\\s*=\\s*$av)*)\\s*/>#es", "\$this->emptyElement('$1', '$2')", $xml);
			$xml = preg_replace('#^<\?(.*?)\?>#es',     "\$this->processingInstruction('$1')", $xml);
			$xml = preg_replace('#^<!--(.*?)-->#es',    "\$this->comment('$1')", $xml);
			$xml = preg_replace('#^<!(.*?)>#es',        "\$this->directive('$1')", $xml);
			$xml = preg_replace('#^([^<]+)#es',         "\$this->text('$1')", $xml);
		}
		// close open elements
		foreach ($this->elementStack as $elem) {
			fputs($this->out, "</$elem>");			
			$this->numCloseErrors++;
		}
		fclose($this->out);
	}
	
	function startElement ($name, $attribs) {
		array_unshift($this->elementStack, $name);
		$attribs = str_replace('\"', '"', trim($attribs));
		if ($attribs != '')
			$attribs = ' '.$attribs;
		fputs($this->out, "<$name$attribs>");
		return '';
	}

	function endElement ($name) {
		if (array_search($name, $this->elementStack) !== false) {
			$elem = "";
			$count = 0;
			while ($elem != $name) {
				$elem = array_shift($this->elementStack);
				fputs($this->out, "</$elem>");
				if ($elem != $name)
					$this->numNestingErrors++;
				$count++;
			}
		}
		static $elemCount;
		$elemCount = ($elemCount+1)%10;;
		if ($elemCount == 0)
			message('=', '', 'black', false);
		return '';
	}

	function emptyElement ($name, $attribs) {
		$attribs = str_replace('\"', '"', trim($attribs));
		if ($attribs != '')
			$attribs = ' '.$attribs;
		fputs($this->out, "<$name $attribs/>");
		return '';
	}

	function processingInstruction ($pi) {
		$pi = str_replace('\"', '"', $pi);
		fputs($this->out, "<?$pi?>");
		return '';
	}

	function directive ($str) {
		$str = str_replace('\"', '"', $str);
		fputs($this->out, "<!$str>");
		return '';
	}

	function comment ($str) {
		$str = str_replace('\"', '"', $str);
		fputs($this->out, "<!--$str-->");
		return '';
	}

	function text ($str) {
		$str = str_replace('\"', '"', $str);
		fputs($this->out, $str);
		return '';
	}
}


?>
