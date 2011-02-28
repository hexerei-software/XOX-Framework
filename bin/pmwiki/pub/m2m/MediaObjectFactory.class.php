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

require_once 'functions.php';
require_once 'MediaObject.class.php';
require_once 'AudioObject.class.php';
require_once 'VideoObject.class.php';
require_once 'CodeObject.class.php';
require_once 'FigImageObject.class.php';
require_once 'SVGObject.class.php';
require_once 'ScriptObjectFactory.class.php';
require_once 'IFrameObject.class.php';

class MediaObjectFactory
{
	function createMediaObject ($pagename, $fname, $attribs=false) {
		if (trim($fname) == '')
			return false; 

		$type = MediaObjectFactory::type($fname);
		$ext = strtolower(file_extension($fname));
		// special case: RealAudio/-Video
		switch ($ext) {
			case 'smi' :
			case 'smil':
			case 'rm':
				if ($attribs->attribs['media'] == 'audio')
					return new RealAudioObject($pagename, $fname, $attribs);
				else
					return new RealVideoObject($pagename, $fname, $attribs);
			case 'htm' :
			case 'html':
				return new IFrameObject($pagename, $fname, $attribs);
		}

		switch ($type) {
			case 'image' : return MediaObjectFactory::createImageObject($pagename, $fname, $attribs);
			case 'audio' : return MediaObjectFactory::createAudioObject($pagename, $fname, $attribs);
			case 'video' : return MediaObjectFactory::createVideoObject($pagename, $fname, $attribs);
		}
		// try to create a ScriptObject
		if ($so = ScriptObjectFactory::createScriptObjectFromFilename($pagename, $fname, $attribs))
			return $so;

		if (is_object($attribs)) {
			$attr = $attribs->getAttribs();
			if (isset($attr['url']))
				return new IFrameObject($pagename, $fname, $attribs);
		}

		$co = new CodeObject($pagename, $fname, $attribs);
		return $co->isBinary() ? false : $co;
	}

	function createImageObject ($pagename, $fname, $attribs) {
		$ext = file_extension($fname);
		switch ($ext) {
			case 'fig' : return new FigImageObject($pagename, $fname, $attribs);
			case 'svg' : return new SVGObject($pagename, $fname, $attribs);
			default    : return new ImageObject($pagename, $fname, $attribs);
		}
	}

	
	function createAudioObject ($pagename, $fname, $attribs) {
		$ext = file_extension($fname);
		switch ($ext) {
			case 'rm' : return new RealAudioObject($pagename, $fname, $attribs);
			case 'mp3': return new MP3Object($pagename, $fname, $attribs);
			default:    return new AudioObject($pagename, $fname, $attribs);
		}
	}

	function createVideoObject ($pagename, $fname, $attribs) {
		$ext = file_extension($fname);
		switch ($ext) {
			case 'flv' : return new FLVObject($pagename, $fname, $attribs);
			case 'swf' : return new SWFObject($pagename, $fname, $attribs);
			case 'dcr' : return new DCRObject($pagename, $fname, $attribs);
			case 'smi' :
			case 'smil':
			case 'rm'  : return new RealVideoObject($pagename, $fname, $attribs);
			default    : return new VideoObject($pagename, $fname, $attribs);
		}
	}
	
	function type ($fname) {
		$ext = strtolower(file_extension($fname));
		$imagetypes = array("gif", "jpg", "jpeg", "png", "eps", "pdf", "svg", "fig");
		$audiotypes = array("wav", "mp3", "mid", "au");
		$videotypes = array("avi", "mpg", "mpeg", "mp4", "swf", "mov", "wrl", "flv", "dcr", "rm", "smi", "smil");
		$scripttypes = array("gpt");
		if (array_search($ext, $imagetypes) !== false)
			return "image";
		if (array_search($ext, $audiotypes) !== false)
			return "audio";
		if (array_search($ext, $videotypes) !== false)
			return "video";
		if (array_search($ext, $videotypes) !== false)
			return "script";
		return false;
	}
}

?>
