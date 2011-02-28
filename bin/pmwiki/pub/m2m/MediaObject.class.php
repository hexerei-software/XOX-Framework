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
require_once 'eps_pdf_tools.inc.php';

class MediaObject
{
	var $pagename;
	var $group;
	var $fname;
	var $url;
	
	function MediaObject ($pagename, $fname) {
		$fname = trim($fname);
		$this->pagename = $pagename;
		if (preg_match('#^\w+://#', $fname)) // remote file?
			$this->url = $fname;
		else {
			$fname = str_replace('../', '', $fname);
			if (preg_match('#^//(.*?)/(.*)$#', $fname, $m)) {
				$this->group = $m[1];
				$fname = $m[2];
			}
			else {
				$this->group = FmtPageName('$Group',$pagename);
				$fname = preg_replace('#^/([^/].*)$#', '$1', $fname); // remove single leading slashes
			}
			$this->fname = $this->purify_name($fname);
			recursive_mkdir($this->dir());
		}
	}

	
	function linkUploadedFile ($fname) {
		if ($this->isRemote())
			return;
		global $FarmD, $UploadFileFmt;
		$fmt = preg_replace('/\$Group\b/', '@Group@', $UploadFileFmt);

		$group = $this->group;
		if (preg_match('#^//(.*?)/(.*)$#', $fname, $m)) {
			$group = $m[1];
			$fname = $m[2];
		}
		else
			$fname = preg_replace('#^/([^/].*)$#', '$1', $fname); // remove single leading slashes

		$uplfname = realpath(preg_replace('/@Group@/', $group, FmtPageName($fmt, $this->pagename))) . "/$fname";

		if (!$this->exists() && file_exists($uplfname)) {
			recursive_mkdir($this->dir());
			@unlink($this->path());  // remove previous link @@ kann weg?
			@symlink($uplfname, $this->path());	
		}
	}

	// create a name without umlauts etc.
	function purify_name ($name) {
		if (!preg_match('#^[/a-zA-Z0-9._-]+$#', $name))
			return md5($name).".".file_extension($name);
		return $name;
	}


	function filename () {
		return basename($this->isRemote() ? $this->url : $this->fname);
	}

	function path ($relative=false) {
		if ($this->isRemote())
			return false;
		global $M2MDataDir;
		$prefix  = "$M2MDataDir/".substitute_umlauts($this->pagename);
		$relpath = "media/".$this->type()."/{$this->fname}";
		return $relative ? $relpath : "$prefix/$relpath";
	}

	function url ($relative=false) {
		if ($this->isRemote())
			return $this->url;
		global $M2MDataUrl;
		$prefix = "$M2MDataUrl/".urlencode(substitute_umlauts($this->pagename));
		$fname = '';
		foreach(explode('/', $this->fname) as $p)
			$fname .= '/'.urlencode($p);
		$relurl = "media/".$this->type().$fname;
		return $relative ? $relurl : "$prefix/$relurl";
	}

	function save () {
		if ($this->isRemote())
			return false;
		$savename = $this->path().md5($this->path());
		if ($this->exists()) {
			rename($this->path(), $savename);
			copy($savename, $this->path());   // leave a working copy of original file
		}
	}

	function restore () {
		if ($this->isRemote())
			return false;
		$savename = $this->path().md5($this->path());
		if (file_exists($savename)) {
			@unlink($this->path());
			rename($savename, $this->path());
		}
	}
		
	function dir ()         {return dirname($this->path());}
	function time ()        {return $this->exists() ? mtime_follow_link($this->path()) : 0;}
	function mimeType ()    {return get_mime_type($this->filename());}
	function format ()      {return strtolower(file_extension($this->filename()));}
	function type ()        {return false;}
	function exists ()      {return $this->isRemote() || file_exists($this->path());}
	function remove ()      {@unlink($this->path());}
	function isBinary ()    {return strpos($this->mimeTypeByFile(), 'text/') === false;}
	function htmlready ()   {return true;}
	function size ()        {return false;}
	function urlSupported (){return false;}
	function isRemote ()    {return $this->url != '';}
	function filesize ()    {return $this->exists() ? filesize($this->path()) : 0;}
	
	function olderThan ($mo){return $this->time() < $mo->time();}	

	function mimeTypeByFile () {
		if ($this->exists()) {
			$ret = pipe_execute("file -i -b -n ".$this->path());
			return $ret;
		}
		return false;
	}
	
	// Returns an array containing width, height and depth of this media object in PostScript point units (72pt = 1in = 2.54cm).
	// The info is read from a .box file created by method "createMO".
	function getTeXBox () {
		if ($this->isRemote())
			return false;
		$boxfile = file_strip_extension($this->path()).".box";
		if (file_exists($boxfile)) {
			$str = file_get_contents($boxfile);
			$lines = explode("\n", $str);
			$res = false;
			// convert TeX points (1in = 72.27pt) to PostScript points (1in = 72pt)
			foreach ($lines as $l) 
				preg_replace('/^(.*?)=(.*?)pt$/e', '$res["$1"]=$2*72/72.27', $l);
			return $res;
		}
		return false;
	}


	function getWikiXML ($role, $attr) {
		$type   = $this->type();
		$format = strtolower($this->format());
		$w = isset($attr['width']) ? " width='$attr[width]'" : '';
		$h = isset($attr['height']) ? " depth='$attr[height]'" : '';
		$va= isset($attr['valign']) ? " valign='$attr[valign]'" : " valign='bottom'";
		$a = isset($attr['align']) ? " align='$attr[align]'" : " align='center'";
		$extent = $this->getTeXBox();
		$d = ($extent !== false && $extent['depth'] != 0) ? " baseline-shift='-$extent[depth]pt'" : '';
		if (isset($attr['scale']) && $role=='fo' && preg_match('/(\d+)%?/', $attr['scale'], $m)) {
			$s = " scale='$m[1]'";
		}
		$f = $role == 'html' ? $this->url() : $this->path();
		$size = $this->size();
		if ($size) {
			$ext = " media-width='$size[width]' media-height='$size[height]'";
		}
		return "<{$type}data role='$role' fileref='$f'$w$h$s$a$va$d$ext format='$format'/>";

	}

	function getDocBook ($role, $attr) {
		$type   = $this->type();
		$format = strtoupper($this->format());
		$w = isset($attr['width']) ? " width='$attr[width]' contentwidth='$attr[width]'" : '';
		$h = isset($attr['height']) ? " depth='$attr[height]' contentdepth='$attr[height]'" : '';
		$va= isset($attr['valign']) ? " valign='$attr[valign]'" : " valign='bottom'";
		$a = isset($attr['align']) ? " align='$attr[align]'" : " align='center'";
		if (isset($attr['scale']) && $role=='fo' && preg_match('/(\d+)%?/', $attr['scale'], $m)) {
			$s = " scale='$m[1]'";
		}
		$f = $role == 'html' ? $this->url() : $this->path();
		return "<{$type}object role='$role'><{$type}data fileref='$f'$w$h$s$a$va format='$format'/></{$type}object>";
	}

	function getHTML ($attr) {
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
		$control_height = 0;
		if ($this->type() == 'video') // @@
			$control_height = 20;
		elseif (!isset($attr['height']) && $this->type() == 'audio')
			$control_height = 30;	
		$objattr['height'] += $control_height;
      if (!isset($attr['autostart']))
         $attr['autostart']='false';

		$attr['src'] = $this->url();
		$attr['type'] = $this->mimeType();
		unset($attr['file']);
		unset($attr['width']);
		unset($attr['height']);
		return htmlObject($objattr, $attr);
	}
}


//////////////////////////////////////////////////////////////////////////////////////

class ImageObject extends MediaObject
{
	function ImageObject ($pagename, $fname, $attribs) {
		MediaObject::MediaObject($pagename, $fname, $attribs);
	}	
	
	function type () {return 'image';}

	function format () {
		$ext = strtolower(file_extension($this->fname));
		switch ($ext) {
			case 'jpg' :
			case 'jpeg': return 'jpeg';
			default    : return $ext;		
		}
	}

	function size () {
		switch ($f = $this->format()) {
			case 'gif' :
			case 'jpeg':
			case 'png' : 
				$size = getimagesize($this->path());
				if ($size) {
					$size = array(
						'width'  => "$size[0]px",
						'height' => "$size[1]px"
					);
					return $size;
				}
				return false;

			case 'eps':
			case 'pdf': 
				$box = getBoundingBox($this->path());
				$size = array(
					'width'  => abs($box['x2']-$box['x1']).'pt',
					'height' => abs($box['y2']-$box['y1']).'pt'
				);
				return $size;

			default:
				$info = RunTool('identify', "IN=".$this->path(), 'exec');
				$info = implode("\n", $info);
				if (preg_match('/.*?\s+([^x]+)x(\S+)\s+/', $info, $m))
					return array('width' =>$m[1], 'height'=>$m[2]);
				return false;
		}
	}

	function convert ($format, $attr) {
		$currentFormat = $this->format();
		$format = strtolower(trim($format));
		if ($format == '')
			return false;
		if ($currentFormat == $format)
			return $this;
			
		$name = file_strip_extension($this->fname);  // remove file extension
		$newMO = MediaObjectFactory::createMediaObject($this->pagename, "$name.$format");
		if (!$newMO->exists() || $newMO->olderThan($this)) {
			if ($currentFormat == 'svg') {  // TODO move to SVGObject class
				switch ($format) {
					case 'eps': $this->svg2eps($this->path(), $newMO->path()); break;
					case 'pdf': $this->svg2pdf($this->path(), $newMO->path()); break;
					default   : $this->svg2format($this->path(), $newMO->path(), $format); break;
				}
			}
			elseif ($currentFormat == 'eps' && $format == 'pdf')
				RunTool("epstopdf", "IN=".$this->path()." OUT=".$newMO->path());
			else {			
				RunTool('convert', "IN=".$this->path()." OUT=".$newMO->path());
				$t = isset($attr['transparent']) ? " TRANSPARENT=$attr[transparent]" : '';
				// for some reason we have to call "convert" twice to get transperency
				if ($t != '')
					RunTool('convert', "IN=".$newMO->path()." OUT=".$newMO->path().$t);
			}
		}
		return $newMO;
	}


	// TODO move svg2xxx methods to SVGObject class
	
	function svg2pdf ($svgfile, $pdffile) {
		$cwd = getcwd();
		chdir(dirname($pdffile));
		RunTool('batik-rasterizer', "MIME=application/pdf IN=$svgfile", 'exec');
		$ret = file_exists($pdffile) ? $pdffile : false;
		chdir($cwd);
		return $ret;
	}


	function svg2eps ($svgfile, $epsfile) {
		$pdffile = file_strip_extension($epsfile).'.pdf';
		if ($this->svg2pdf($svgfile, $pdffile)) {
			RunTool('pdf2ps', "IN=$pdffile OUT=$epsfile.ps", 'exec');
			RunTool('ps2epsi', "IN=$epsfile.ps OUT=$epsfile", 'exec');
			unlink("$epsfile.ps");
			return file_exists($epsfile);
		}
		return false;
	}

	function svg2format ($svgfile, $targetfile, $format) {
		$pdffile = file_strip_extension($targetfile).'.pdf';
		if ($this->svg2eps($svgfile, $epsfile)) {			
			RunTool('convert', "IN=$epsfile OUT=$targetfile");
			return file_exists($targetfile);
		}
		return false;
	}

	function fileInfo () {
		if (!$this->exists())
			return false;
		$infolines = RunTool('identify', "OPT=-verbose IN=".$this->path(), 'exec');
		// collect output of identify (not really reliable yet)
		foreach ($infolines as $line)
			if (preg_match('/^  ([ a-zA-Z]+):(.+)$/', $line, $m))
				$info[trim($m[1])] = trim($m[2]);
		// add info about image width and height
		preg_match('/(\d+)x(\d+)/', $info['Geometry'], $m);
		$info['Width'] = $m[1];
		$info['Height'] = $m[2];
		preg_match('/(\d+)x(\d+)/', $info['Resolution'], $m);		
		$dpi=96;
		if($m[1] > 0){
	   	$m[2] = $dpi;
	    	$m[1] = $dpi;
		}

		if ($m[1] > 0)
			$info['AbsWidth'] = str_replace(',', '.', ($info['Width']/$m[1])."in"); // TODO: distinguish between units stored in image file
		if ($m[2] > 0)
			$info['AbsHeight'] = str_replace(',', '.', ($info['Height']/$m[2])."in");
		return $info;
	}

	function htmlready () {
		switch (file_extension($this->path())) {
			case 'eps':
			case 'png': return false;
		}
		return true;
	}

	
	function getHTML ($attr) {
		$mime   = $this->mimeType();
		$format = $this->format();
		$w = isset($attr['width']) ? " width='$attr[width]'" : '';
		$h = isset($attr['height']) ? " height='$attr[height]'" : '';
		$extent = $this->getTeXBox();
		$shift = '';
		if ($extent !== false) {
			list($img_w, $img_h) = getimagesize($this->path());
			$shift = round($img_h * $extent['depth' ] / ($extent['height']+$extent['depth']));
			$shift = " style='vertical-align:-{$shift}px'";
		}
		$f = $this->url();
		return "<img src='$f'$w$h$shift/>";
	}

	function getDocBook ($role, $attr) {
		if ($role == 'fo' && !isset($attr['width']) && !isset($attr['height'])) {
			if ($info = $this->fileInfo()) {
				$attr['width'] = $info['AbsWidth'];
				$attr['height'] = $info['AbsHeight'];
			}				
		}
		return MediaObject::getDocBook($role, $attr);
	}
}

?>
