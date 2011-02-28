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

class VideoObject extends MediaObject
{
	function VideoObject ($pagename, $fname) {
		MediaObject::MediaObject($pagename, $fname);
	}

	function type () {return 'video';}

	function convert ($format, $attr) {
		if ($this->isRemote() || $format != 'png') // @@
			return false;
		$name = file_strip_extension($this->fname);  // remove file extension
		$mo = MediaObjectFactory::createMediaObject($this->pagename, "$name.$format");
		if (!$mo->exists() || $mo->olderThan($this)) {
			@unlink($mo->path());
			RunTool('ffmpeg', sprintf("IN=%s OUT=%s TARGETFORMAT=%s TIME=2", $this->path(), $mo->path(), $format), 'pipe');
			return $mo->exists() ? $mo : false;
		}
		return $mo;
	}

	function size () {
		$output = RunTool('ffmpeg', "IN=".$this->path(), 'pipe');
		if (preg_match('/\n\s*Stream\s+.*?Video:.*?(\d+)x(\d+)/', $output, $m)) 
			return array('width'=>$m[1], 'height'=>$m[2]);
		return false;
	}
}

class SWFObject extends VideoObject
{
	function SWFObject ($pagename, $fname, $attr) {
		VideoObject::VideoObject($pagename, $fname, $attr);
	}

	function getHTML ($attr) {
		$objattr = array(
			'type' => 'application/x-shockwave-flash', 
			'data' => $this->url()
		);
		$size = $this->size();
		if (!isset($attr['width']) && !isset($attr['height']) && $size !== false) {;
			$objattr['width'] = $size['width'];
			$objattr['height'] = $size['height'];
		}
		elseif (isset($attr['width']) && $size !== false) {
			$objattr['width'] = $attr['width'];
			$objattr['height'] = round($attr['width']*$size['height']/$size['width']);
		}
		elseif (isset($attr['height']) && $size !== false) {
			$objattr['width'] = round($attr['height']*$size['width']/$size['height']);
			$objattr['height'] = $attr['height'];
		}
		else {
			if (isset($attr['width'])) 
				$objattr['width'] = $attr['width'];
			if (isset($attr['height'])) 
				$objattr['height'] = $attr['height'];
		}

		$objpar  = array(
			'allowScriptAccess' => 'sameDomain',
			'movie'             => $this->url(),
			'quality'           => 'high',
         'wmode'             => 'transparent',
		);

		return htmlObject($objattr, $objpar);
	}
}


class DCRObject extends VideoObject
{
	function DCRObject ($pagename, $fname, $attr) {
		VideoObject::VideoObject($pagename, $fname, $attr);
	}

	function getHTML ($attr) {
		$objattr = array(
			'type' => 'application/x-director',
			'codebase' => 'http://download.macromedia.com/pub/shockwave/cabs/director/sw.cab#version=10,8,5,1,0',
			'classid' => 'clsid:166B1BCA-3F9C-11CF-8075-444553540000'
		);

		$objpar  = array(
			'src' => $this->url(),
//			'data' => $this->url(),
			'quality' => 'high'
		);

		unset($attr['file']);
		$objpar = array_merge($attr, $objpar);

		if (isset($attr['width'])) $objattr['width'] = $attr['width'];
		if (isset($attr['height'])) $objattr['height'] = $attr['height'];
		return htmlObject($objattr, $objpar);
	}
}

class FLVObject extends VideoObject
{
	function FLVObject ($pagename, $fname, $attr) {
		VideoObject::VideoObject($pagename, $fname, $attr);
	}

	function urlSupported () {
		return true;
	}

	function getHTML ($attr) {
		global $M2MUrl;
		$player = "$M2MUrl/tools/flvplayer.swf";
		$objattr = array(
			'type' => 'application/x-shockwave-flash', 
			'data' => $player
		);
		if (isset($attr['width'])) $objattr['width'] = $attr['width'];
		if (isset($attr['height'])) $objattr['height'] = $attr['height'];
		$objpar  = array(
			'allowScriptAccess' => 'sameDomain',
			'src'               => $player,
			'movie'             => $player,
			'quality'           => 'high',
			'scale'             => 'noScale',
         'wmode'             => 'transparent',
			'flashvars'         => "file=".$this->url()."&autostart=false"
		);

		return htmlObject($objattr, $objpar);
	}
}


class RealVideoObject extends VideoObject
{
	function RealVideoObject ($pagename, $fname, $attr) {
		VideoObject::VideoObject($pagename, $fname, $attr);
	}

	function urlSupported () {
		return true;
	}

	function getHTML ($attr) {
		static $ccount = 0;
		$ccount++;
		$w = $h = -1;
		if (!isset($attr['width']) && !isset($attr['height']))
			$w = 400;
		else {
			$w = isset($attr['width']) ? $attr['width'] : '';
			$h = isset($attr['height']) ? $attr['height'] : '';
		}
		$objattr = array(
			'type'  => 'audio/x-pn-realaudio-plugin', 
			'classid'=>'clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA' 
		);
		if ($w >= 0) $objattr['width'] = $w;
		if ($h >= 0) $objattr['height'] = $h;

		$objpar  = array(
			'controls'  => 'ImageWindow',
			'nojava'    => 'true',
//			'src'       => $this->url(),
			'autostart' => isset($attr['autostart']) ? $attr['autostart'] : 'false',
			'console'   => "c$ccount"
		);
		$videowindow = htmlObject($objattr, $objpar);
		
		$objattr = array(
			'type'  => 'audio/x-pn-realaudio-plugin', 
			'height'=> 100,
			'classid'=>'clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA' 
		);
		if ($w >= 0) $objattr['width'] = $w;
		$objpar  = array(
			'controls'  => 'All',
			'nojava'    => 'true',
			'src'       => $this->url(),
			'autostart' => isset($attr['autostart']) ? $attr['autostart'] : 'false',
			'console'   => "c$ccount"
		);
		$controls = htmlObject($objattr, $objpar);
		return "<table cellpadding='0' cellspacing='0' border='0'><tr><td>$videowindow</td></tr><tr><td>$controls</td></tr></table>";
	}
}
?>
