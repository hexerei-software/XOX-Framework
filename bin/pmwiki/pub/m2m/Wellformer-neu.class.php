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

function my_fgetc ($f) {
	global $prev_char;
	return $prev_char = fgetc($f);
}


class WellFormer {
	var $out;
	var $elementStack;
	var $numNestingErrors;
	var $numCloseErrors;
	var $prev_char;
	var $ungot;


	function WellFormer () {
		$this->prev_char = 0;
		$this->ungot = false;
	}


	function fgetc ($f) {
		if ($this->ungot) {
			$c = $this->prev_char;
			$this->ungot = false;
		}
		else
			$c = $this->prev_char = fgetc($f);
		return $c;
	}

	function fungetc () {
		$this->ungot = true;
	}

	function processFile ($infile, $outfile) {
		$in = fopen($infile, 'r');
		$out = fopen($outfile, 'w');
		while (!feof($in))
		{
			$c = $this->fgetc($in);

			if ($c == '<') {
				$c = $this->fgetc($in);
				if ($c == '/') {
					$this->closeElement($in, $out);
				} else if ($c != '!' && $c != '?') {
					$this->fungetc();
					$this->openElement($in, $out);
				}
				else fputs("<$c");
			} else if (!feof($in)) fputs($c);

			while ( count($this->elementStack) > 0) {
				$elem = array_shift($this->elementStack);
				os << "</" << $elem << ">";
			}

		}
		fclose($in);
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
