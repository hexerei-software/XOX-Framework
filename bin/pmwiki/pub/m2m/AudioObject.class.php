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


class AudioObject extends MediaObject
{
	function AudioObject ($pagename, $fname) {
		MediaObject::MediaObject($pagename, $fname);
	}	
	
	function type () {return 'audio';}

	function getDocBook ($role, $attr) {
		$format = strtoupper($this->format());
		$f = $role == 'html' ? $this->url() : $this->path();
		// no width/depth attributes defined for audiodata
		return "<audioobject role='$role'><audiodata fileref='$f' format='$format'/></audioobject>";
	}
}


class MP3Object extends AudioObject
{
	function MP3Object ($pagename, $fname, $attr) {
		AudioObject::AudioObject($pagename, $fname, $attr);
	}
	
	function urlSupported (){return true;}

	function getHTML ($attr) {
		$w = $attr['width'];
		$h = $attr['height'];
		if (isset($attr['width']) && !isset($attr['height']))
			$h = $w/100;
		elseif (!isset($attr['width']) && isset($attr['height']))
			$w = $h*100;
		else {
			$w = 200;
			$h = 20;
		}

		global $M2MUrl;
		$player = "$M2MUrl/tools/player_mp3_maxi.swf";
		$url = urlencode($this->url());
		
		$objattr = array(
			'type' => 'application/x-shockwave-flash',
			'data' => $player,
			'width' => $w,
			'height' => $h
		);
		$objpar = array(
			'movie' => $player,
			'FlashVars' => "mp3=$url&amp;showstop=1&amp;showvolume=1"
		);
		$controls = htmlObject($objattr, $objpar);
		return $controls;
	}
}


class RealAudioObject extends AudioObject
{
	function RealAudioObject ($pagename, $fname, $attr) {
		AudioObject::AudioObject($pagename, $fname, $attr);
	}

	function urlSupported () {
		return true;
	}

	function getHTML ($attr) {
		$w = $h = -1;
		if (!isset($attr['width']) && !isset($attr['height']))
			$w = 400;
		else {
			$w = isset($attr['width']) ? $attr['width'] : '';
			$h = isset($attr['height']) ? $attr['height'] : '';
		}

		$objattr = array(
			'type'  => 'audio/x-pn-realaudio-plugin', 
			'height'=> 100,
			'classid'=>'clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA' 
		);
		if ($w >= 0) $objattr['width'] = $w;
		if ($h >= 0) $objattr['height'] = $h;
		$objpar  = array(
			'controls'  => 'All',
			'nojava'    => 'true',
			'src'       => $this->url(),
			'autostart' => isset($attr['autostart']) ? $attr['autostart'] : 'false',
		);
		$controls = htmlObject($objattr, $objpar);
		return $controls;
	}
}

?>
