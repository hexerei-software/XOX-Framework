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

require_once 'VideoObject.class.php';

class AppletObject extends MediaObject
{
	var $attribs = array();
	var $params = array();

	function AppletObject ($pagename, $fname, $attr) {
		MediaObject::MediaObject($pagename, $fname, $attr);
		$this->linkUploadedFile($fname);
	}

	// type of MediaObject
	function type () {
		return 'applet';
	}

	function getHTML ($attr) {
		if ($this->fname == '')
			return '';
		$ext = strtolower(file_extension($this->fname));
		$appletdir = $this->dir()."/".file_strip_extension($this->fname);
		$appleturl = dirname($this->url())."/".file_strip_extension($this->fname);
		recursive_mkdir($appletdir);
		if ($ext == 'jar' || $ext == 'zip') {
			$cwd = getcwd();
			chdir($appletdir);
			if ($ext == 'zip')
				RunTool('unzip', "IN=".$this->path(), 'pipe');
			else
				RunTool('unzip', "IN=".$this->path()." FILE=index.html", 'pipe');
			$ok = $this->readHTMLFile('index.html');
			chdir($cwd);
			if (!$ok)
				return '';
		}
		if (!isset($this->attribs['archive']))
			return '';

		// generate html file containing the applet
		$ret = "<html><body>\n";
		$ret.= '<div style="margin:0;padding:0"><applet';
		foreach ($this->attribs as $k=>$v)
			$ret .= " $k=\"$v\"";
		$ret .= ">\n";
		foreach ($this->params as $k=>$v)
			$ret .= "<param name=\"$k\" value=\"$v\"/>\n";
		$ret .= "</applet></div>";
		$ret .= "</body></html>";
		$f = fopen("$appletdir/applet.html", "w");
		fputs($f, $ret);
		fclose($f);

		// return iframe with above applet page
		$ret = "<iframe src='$appleturl/applet.html'";
		$ret.= " scrolling='no' frameborder='0' marginwidth='0' marginheight='0'";
		$ret.= "	width='{$this->attribs['width']}' height='{$this->attribs['height']}'>\n";
		$ret.= "<p>Your browser doesn't support embedded frames</p>\n";
		$ret.= "</iframe>";
		return $ret;
	}

	function getWikiXML ($role, $attr) {
		return '';
	}

	function readHTMLFile ($fname) {
		if (file_exists($fname)) {
			$html = file_get_contents($fname);
			preg_replace('#<applet\s+(.*?)>(.*?)</applet>#sie', "\$this->addApplet('$1', '$2')", $html, 1);
			return true;
		}
		return false;
	}

	function addApplet ($appattr, $param) {
		$appattr = str_replace('\"', '"', $appattr);
		$param = str_replace('\"', '"', $param);
		$attr = new Attributes($appattr);
		$this->attribs = $attr->getAttribs();
		preg_replace('#\s*<param\s+(.*?)\s*/?>#sie', "\$this->addParam('$1')", $param);
	}

	function addParam ($param) {
		$param = str_replace('\"', '"', $param);
		$attr = new Attributes($param);
		$attr = $attr->getAttribs();
		if (isset($attr['name']))
			$this->params[$attr['name']] = $attr['value'];
	}
}

?>
