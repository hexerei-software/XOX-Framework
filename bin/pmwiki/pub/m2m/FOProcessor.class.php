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


class FOProcessorFactory {
	function createProcessor ($name) {
		$name = strtolower($name);
		switch ($name) {
			case 'fop' : return new FopFOProcessor;
			case 'xep' : return new XepFOProcessor;
			default    : return new FopFOProcessor;
		}
	}

	function createProcessorByOption ($opt) {
		if (is_string($opt))
			$opt = new StylesheetOptions($opt);
		$conv = $opt->getValue('processor.fo');
		return FOProcessorFactory::createProcessor($conv);
	}
}


class FOProcessor {
	var $options;
	function setOption ($key, $value) {
		$options[$key] = $value;
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


class FopFOProcessor extends FOProcessor {
	var $fofile;

	function descrString () {return "Apache FOP";}
		
	// format: ps or pdf, returns true if conversion was successful
	function process ($fo, $out) {
		if (!file_exists($fo))
			return false;
		$this->fofile = $fo;
		$format = file_extension($out);
		if ($format != 'ps' && $format != 'pdf')
			$format = 'pdf';
		$ret = RunTool('fop', "FO=$fo FORMAT=$format OUT=$out", 'pipe-callback', array(&$this, 'messageCallback'));
		return ($ret == 0);
	}

	function messageCallback ($line) {
		$line = preg_replace('/^\[INFO\]\s*((Using)|(FOP \d)).*$/', '', $line);
		if (substr($line, 0, 6) == '[INFO]')
			$color = 'green';
		elseif (substr($line, 0, 7) == '[ERROR]')
			$color = 'red';
		else
			$color = 'orange';
		$line = preg_replace('/^\[.+?\]\s*/', '', $line);
		$line = preg_replace('/file:(.*?):/e', 'basename("$1").":"', $line);
		$line = str_replace('<', '&lt;', $line);
		message($line, '', $color);
		if (preg_match('/:(\d+):(\d+) No meaningful layout in block after many attempts/', $line, $m)) {
			$pages = $this->traceback($m[1], $m[2]);
			if ($pages !== false) {
				message("problem caused by <b>$pages[img]</b> (image probably too large)", 'start', 'blue');
				unset($pages['img']);
				foreach ($pages as $p) 
					message(FmtPageName("image used on page <a href='\$PageUrl' target='_blank'><b>$p</b></a>", $p), '', 'blue');
				message('', 'end');
			}
		}
	}

	function traceback ($lineno, $colno) {
		$f = fopen($this->fofile, 'r');
		while (!feof($f) && $lineno-- > 0) 
			$line = fgets($f);
		fclose($f);
		if ($lineno != -1)
			return false;
		$line = substr($line, $colno-1);  // remove leading characters
		if (preg_match('/<fo:external-graphic src="(.+?)"/', $line, $m)) {
			$imgpath = $m[1];
			$img = basename($imgpath);
			if (!file_exists(dirname($this->fofile)."/source.xml"))
				return false;

			$f = fopen(dirname($this->fofile)."/source.xml", "r");
			$pagename = "";
			while (!feof($f)) {
				$line = fgets($f);
				if (preg_match('/^<!-- wiki page: (.+?) -->/', $line, $m))
					$pagename = $m[1];
				elseif (preg_match("/<imagedata fileref=\"[^\"]*?$img\"/", $line, $m))
					$found[$pagename] = 1;
			}
			fclose($f);
			$ret = array_keys($found);
			$ret['img'] = $img;
			return $ret;
		}
		return false;
	}
}


class XepFOProcessor extends FOProcessor {
	function descrString () {return "RenderX XEP";}
	
	function process ($fo, $out) {
		if (!file_exists($fo))
			return false;
		$format = file_extension($out);
		if ($format != 'ps' && $format != 'pdf')
			$format = 'pdf';
		RunTool('xep', "FO=$fo FORMAT=$format OUT=$out", 'pipe-callback', array('XepFOProcessor', 'messageCallback'));
		return true;
	}

	function messageCallback ($line) {
		$line = trim($line);
		$line = preg_replace('/^.*?Attribute.+?cannot have a value of.*$/', '', $line);
		if (preg_match('/^\(generate\s+\[.*?\](.*?)\)+/', $line, $m))
			message($m[1], '', 'green');
		elseif ($line{0} == '(')
			message('=', '', 'black', false);
		elseif (substr($line, 0, 9) == '[warning]') {
			$line = substr($line, 9);
			message($line, '', 'orange');
		}
		elseif (preg_match('/error:|\[error\]/', $line)) {
			message($line, '', 'red');
		}
	}
}

?>
