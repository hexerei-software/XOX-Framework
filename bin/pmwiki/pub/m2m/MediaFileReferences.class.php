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

class MediaFileReferences
{
	var $ref;
	
	function MediaFileReferences ($fname='') {
		if ($fname != '')
			$this->readFile($fname);
	}

	function readFile ($fname) {
		$f = fopen($fname, 'r');
		$media = '';
		while (($line = fgets($f))) {
			$line = trim($line);
			if (preg_match('/^\[(.+?)\]\[(.+?)\]\[(.*?)\]$/', $line, $m)) {
				list($dummy, $media, $type, $info) = $m;
				if (!isset($this->ref[$media]))
					$this->ref[$media] = array('*type*'=>$type, '*info*'=>$info);
				elseif ($this->ref[$media]['*type*'] == 'upload') 
					$this->ref[$media]['*type*'] = $type;
				if ($this->ref[$media]['*info*'] == '')
					$this->ref[$media]['*info*'] = $info;
			}
			elseif ($line != '' && $media != '')
				array_push($this->ref[$media], $line);
		}
		fclose($f);
	}

	function readFiles ($basedir, $dirpatterns) {
		$dirpatterns = explode('/', $dirpatterns);
		$pattern = array_shift($dirpatterns);
		$d = opendir($basedir);
		if (count($dirpatterns) == 0) {
			while (($entry = readdir($d)) !== false) {
				if (is_file("$basedir/$entry") || is_link("$basedir/$entry") && preg_match("#$pattern#", $entry)) 
					$this->readFile("$basedir/$entry");
			}
		}
		else {
			while (($entry = readdir($d)) !== false) {
				if ($entry{0} != '.' && is_dir("$basedir/$entry") && preg_match("#$pattern#", $entry)) 
					$this->readFiles("$basedir/$entry", implode('/', $dirpatterns));
			}
		}
		closedir($d);
	}

	function writeFile ($fname) {
		if (count($this->ref) == 0)
			return;
		$f = fopen($fname, 'w');
		foreach ($this->ref as $media=>$pages) {
			fputs($f, "[$media][{$pages['*type*']}][{$pages['*info*']}]\n");
			foreach ($pages as $key=>$p) {
				if ($key{0} !== '*') 
					fputs($f, "$p\n");
			}
		}
		fclose($f);
	}

	function referencesOf ($fname) {
		if (isset($this->ref[$fname]))
			return $this->ref[$fname];
		return false;
	}

	function getType ($media) {				
		return (isset($this->ref[$media]['*type*'])) ? $this->ref[$media]['*type*'] : false;			
	}
	
	function getInfo ($media) {
		return (isset($this->ref[$media]['*info*'])) ? $this->ref[$media]['*info*'] : false;			
	}

	function getReferences ($media) {
		$ret = array();
		foreach ($this->ref[$media] as $key=>$val)
			if ($key{0} != '*')
				$ret[] = $val;
		return $ret;
	}

	function addReference ($pagename, $fname, $type, $info='') {
		if ($pagename == '' || $fname == '' || $type == '')
			return;

		if (!is_array($this->ref[$fname]))
			$this->ref[$fname] = array('*type*'=>$type, '*info*'=>$info, $pagename);
		else {
			if ($this->ref[$fname]['*type*'] == 'upload')
				$this->ref[$fname]['*type*'] = $type;
			if ($this->ref[$fname]['*info*'] == '')
				$this->ref[$fname]['*info*'] = $info;
			if (array_search($pagename, $this->ref[$fname]) === false)
				array_push($this->ref[$fname], $pagename);
		}
	}
}

?>
