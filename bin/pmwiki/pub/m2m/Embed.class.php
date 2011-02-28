<? if (!defined('PmWiki')) exit();
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
require_once 'functions.php';
require_once 'Attributes.class.php';
require_once 'MediaObjectFactory.class.php';

class Embed 
{
	var $pagename;
	var $attribs;	
	
	function Embed ($pagename, $attrstring, $uploaded=true) {
		$this->pagename = $pagename;
		$this->attribs = new Attributes($attrstring, array('print'=>'fo'));
		$formats = array('html', 'fo');
		foreach ($formats as $format) {
			$attr = $this->attribs->getAttribs($format);
			$locattr = (isset($attr['file'])) ? 'file' : 'url';
			$fname = $this->attribs->getAttrib($locattr, $format);
			// create directories
			$mo = MediaObjectFactory::createMediaObject($pagename, $fname);
			if ($mo !== false) {
				if ($uploaded)	{
					$mo->linkUploadedFile($fname);  // create symlink to uploaded file
				}
			}
		}
	}

	function getMediaObject ($format) {
		$location = $this->attribs->getAttrib('file', $format);
		if ($location == '')
			$location = $this->attribs->getAttrib('url', $format);
		return MediaObjectFactory::createMediaObject($this->pagename, $location);
	}
	

	function getWikiXML ($inline=false) {
		$xml = array();
		// build html part
		$attr = $this->attribs->getAttribs('html');
		if (isset($attr['file']) && $attr['file'] != 'none') {
			$mo = MediaObjectFactory::createMediaObject($this->pagename, $attr['file'], $this->attribs);
			if ($mo === false)
				message("can't embed file <i>$attr[file]</i> (unknown filetype)", '', 'orange'); 
			elseif (!$mo->exists())
				message("can't embed non-existent file <i>$attr[file]</i>", '', 'orange');
			else {
				if ($mo->htmlready()) 
					$xml[$mo->type()][] = $mo->getWikiXML('html', $attr);
				else {
					$mo_png = $mo->convert('png', $attr);
					$xml[$mo_png->type()][] = $mo_png->getWikiXML('html', $attr);
				}
			}
		}

		// build fo part
		$attr = $this->attribs->getAttribs('fo');
		if (isset($attr['file']) && $attr['file'] != 'none') {
			$mo = MediaObjectFactory::createMediaObject($this->pagename, $attr['file'], $this->attribs);
			if ($mo === false)
				message("can't embed file <i>$attr[file]</i> (unknown filetype)", '', 'orange'); 
			if ($mo !== false && $mo->exists()) {
				if ($mo->type() == 'script' || strpos("eps svg pdf fig", $mo->format()) !== false) {
					$mo_eps = $mo->convert('eps', $attr);
					$xml[$mo_eps->type()][] = $mo_eps->getWikiXML('fo', $attr);
					$mo_pdf = $mo->convert('pdf', $attr);
					$xml[$mo_pdf->type()][] = $mo_pdf->getWikiXML('fo', $attr);
				}
				elseif ($mo->type() == 'video') {
					$mo_png = $mo->convert('png', $attr);
					if ($mo_png)
						$xml[$mo_png->type()][] = $mo_png->getWikiXML('fo', $attr);
					else
						$xml[$mo->type()][] = $mo->getWikiXML('fo', $attr);
				}
				else
					$xml[$mo->type()][] = $mo->getWikiXML('fo', $attr);
			}
		}
		if (count($xml) == 0)
			return "";
			
		// put html and fo objects together
		$inline = $inline ? 'inline' : '';
		$ret = "<{$inline}mediaobject>\n";
		if (isset($attr['caption']))
			$ret .= "<caption>$attr[caption]</caption>\n";
		foreach ($xml as $type=>$x) {
			if (!$type) continue;
			$ret .= "<{$type}object>\n";
			foreach ($x as $y) $ret .= "$y\n";
			$ret .= "</{$type}object>\n";
		}
		$ret.= "</{$inline}mediaobject>\n";
		return $ret;
	}

	function getDocBook ($inline=false) {
		// build html part
		$attr = $this->attribs->getAttribs('html');
		if ($attr['file'] == '') {
			message("skipped media object without file attribute", '', 'orange');
			return '';
		}
		
		$mo = MediaObjectFactory::createMediaObject($this->pagename, $attr['file'], $this->attribs);
		if ($mo === false) {
			message("can't embed file <i>$attr[file]</i> (unknown filetype)", '', 'orange'); 
			return '';
		}			
		if (!$mo->exists()) {
			message("can't embed non-existent file <i>$attr[file]</i>", '', 'orange');
			return '';
		}
		$rethtml = $mo->getDocBook('html', $attr);
		
		// build fo part
		$attr = $this->attribs->getAttribs('fo');
		$mo = MediaObjectFactory::createMediaObject($this->pagename, $attr['file'], $this->attribs);
		if ($mo === false || !$mo->exists()) {
			$retfo = "<textobject role='fo'><phrase></phrase></textobject>\n";
			$rettex = "<textobject role='latex'><phrase></phrase></textobject>";
		}
		else {
			if ($mo->format() == 'svg') {
				message("converting $attr[file] to EPS");
				$mo = $mo->convert('eps', $attr);
			}
			$retfo = $mo->getDocBook('fo', $attr);
			$rettex = preg_replace('/\s+role=\'fo\'/', " role='latex'", $retfo);
		}
		
		// put html and fo objects together
		$retcapt = isset($attr['caption']) ? "<caption><para>$attr[caption]</para></caption>\n" : '';
		if ($rethtml != '' || $retfo != '') {
			$inline = $inline ? 'inline' : '';
			return "<{$inline}mediaobject>\n$rethtml\n$retfo\n$rettex\n$retcapt</{$inline}mediaobject>\n";
		}
		return '';
	}

	
	function getHTML ($showErrors=false) {
		if (($msg = $this->validate()) != "")
			return $showErrors ? errorHTML($msg) : "";

		$attr = $this->attribs->getAttribs('html');
		$file = $attr['file'];

		if ($attr['file']=='' && $attr['url'] != '')
			$file = $attr['url'];

		$mo = MediaObjectFactory::createMediaObject($this->pagename, $file, $this->attribs);
		if (!$mo->isRemote() && !$mo->htmlready()) {
			$mo2 = $mo->convert('png', $attr);
			if ($mo2 === false)
				return $showErrors ? errorHTML("Can't embed file '{$mo->fname}'") : '';
			$mo = $mo2;
		}	
		
		$html = $mo->getHTML($attr);
		if (isset($attr['caption'])) {
			$ha = isset($attr['align']) ? " align='$attr[align]'" : '';
			return "<table border='0'$ha><tr><td>$html</td></tr><tr><td>$attr[caption]</td></tr></table>";
		}
		$ha = isset($attr['align']) ? "text-align:$attr[align];" : '';
		if ($ha) 
			$html = "<p style='$ha'>$html</p>";
		return $html;
	}	


	function validate () {
		$attr = $this->attribs->getAttribs('html');
		$file = $attr['file'];
		$msg = "";
		if ($file == '' && $attr['url'] == '')
			return $showErrors ? errorHTML("No <i>file</i> attribute found in (:embed:) statement") : '';
		
		if ($attr['file']=='' && $attr['url'] != '')
			$file = $attr['url'];

		$mo = MediaObjectFactory::createMediaObject($this->pagename, $file, $this->attribs);
		if ($mo == false || !$mo->exists()) {
			global $ScriptUrl;
			$msg = "Embedded file <i>$file</i> not found<br>";
			$msg.= "<a href='$ScriptUrl?n={$this->pagename}/?action=upload&upname=$file'>upload now</a>";
		}
		elseif ($mo->isRemote() && !$mo->urlSupported()) {
			$msg = "<i>url</i> attribute not supported for filetype ".strtoupper($mo->format());
		}
		
		// test if print-file attribnute is given and if referenced file exists 
		$attr = $this->attribs->getAttribs('fo', true);
		if (isset($attr['file'])) {
			$file = $attr['file'];
			$mo = MediaObjectFactory::createMediaObject($this->pagename, $file, $this->attribs);
			if ($mo == false || !$mo->exists()) {
				global $ScriptUrl;
				if ($msg != "")
					$msg .= "<hr/>";
				$msg .= "Embedded print file <i>$file</i> not found<br>";
				$msg .= "<a href='$ScriptUrl?n={$this->pagename}/?action=upload&upname=$file'>upload now</a>";
			}
		}
		return $msg;
	}
}

?>
