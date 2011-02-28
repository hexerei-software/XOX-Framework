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


require_once "$M2MDir/debugfuncs.php";
require_once "$M2MDir/cmdlinetools.php";
require_once "$M2MDir/StylesheetOptions.class.php";
require_once "$M2MDir/FOProcessor.class.php";
require_once "$M2MDir/XSLTProcessor.class.php";

$KEEPTOKEN = "\234\234";
$KEEPARRAY = array();
$KEEPCOUNT;

class ConverterFactory {	
	// 'static' method
	function createConverter ($pagename, $targetFormat) {
		global $ConverterClassPrefixes;
		if (array_search($targetFormat, $ConverterClassPrefixes) === false)
			return false;
		return eval("return new {$targetFormat}Converter('$pagename');");
	}
	
	// 'static' method
	function availableTargetFormats () {
		global $ConverterClassPrefixes;
		foreach ($ConverterClassPrefixes as $prefix) {
			$descr = eval("return {$prefix}Converter::targetFormatDescr();");  // PHP identifiers are case-insensitive
			$ret[$prefix] = $descr;
		}
		return $ret;
	}
}


// 'abstract' base class for all wiki converters
class WikiConverter {
	var $pagename;

	function WikiConverter ($pagename) {
		$this->pagename = $pagename;
		if (!recursive_mkdir($this->outputDir()))
			error_message("unable to create output directory ".$dir, true);
	}

	function outputDir () {
		global $M2MDataDir;
		return "$M2MDataDir/{$this->pagename}";
	}

	function url ($fname) {
		global $M2MDataUrl;
		return "$M2MDataUrl/{$this->pagename}/$fname";
	}		
	
	function lock () {
		$lockfile = $this->outputDir()."/.lock";
		if (file_exists($lockfile)) {
			$dt = time() - filemtime($lockfile);
			if ($dt/60 < 15)  // lock keeps active for max. 15 minutes
				return false;
			if (!$this->unlock())
				error_message("can't unlock page {$this->pagename}", true);				
		}
		$f = fopen($lockfile, "w");
		fputs($f, getenv(REMOTE_ADDR));
		fclose($f);
		return true;
	}

	function unlock () {
		$lockfile = $this->outputDir()."/.lock";
		if (file_exists($lockfile))
			return unlink($lockfile);
		return true;
	}	

	function readIncludedText ($basepage, $includepage) {
		$page = ReadPage(MakePageName(trim($basepage), trim($includepage)));
		return $page['text'];
	}
	
	function collectPages () {
		$trailpage = ReadPage($this->pagename);
		if ($trailpage) {
			global $SuffixPattern,$GroupPattern,$WikiWordPattern,$LinkWikiWords;
			global $SpaceWikiWords,$AsSpacedFunction,$SpaceWikiWordsFunction;
			global $HandlePublishFmt,$PublishPageNotFoundFmt;
			global $GCount,$IncludeModifiedDate,$NumberPages;
			SDV($SpaceWikiWordsFunction,$AsSpacedFunction);
			$trailname  = $this->pagename;
			$trailgroup = FmtPageName('$Group',$trailname);
			$trailtext = $trailpage['text'];
			$trailtext = preg_replace('/\(:include\s+(.+?):\)/e', "\$this->readIncludedText('{$this->pagename}', '\\1')", $trailtext);
			// collect Wiki pages to be part of the trail
			$pageno = 0;
			foreach(explode("\n", $trailtext) as $line) {  // process text line by line
				if (preg_match("/^T?([#*:]+)\\s*(.*)/", $line, $match)) { // found a list item?
					$match[1] = str_replace(':#', ':', $match[1]);
					if ($LinkWikiWords) 
						$match[2] = preg_replace("/^($GroupPattern([\\/.]))?($WikiWordPattern)/e", 
							"'[[$1'.(($SpaceWikiWords)?$SpaceWikiWordsFunction('$3'):'$3').']]'", 
							$match[2]);

					$match[2] = preg_replace("/\\[\\[([^\\]]*)->([^\\]]*)\\]\\]/",'[[$2|$1]]', $match[2]);
					if (preg_match("/^(\\[\\[([^|]*?)(\\|.*?)?\\]\\]($SuffixPattern))/", $match[2], $m)) {
						$trailStarted = true;
						$result[$pageno]['depth'] = min(strlen($match[1]), 8);  // DocBook's section nesting depth is limited to 8
						$result[$pageno]['pagename'] = MakePageName($trailname, $m[2]);
						if ($m[3] != '')
							$result[$pageno]['title'] = preg_replace('/^\s*\|\s*(.*?)\s*$/', '\1', $m[3]); // remove leading pipe and spaces from page titles
						else
							$result[$pageno]['title'] = Keep(trim(preg_replace('#([^/.]+[/.])?(.*)#', '$2' ,$m[2])));
						$result[$pageno]['link'] = $m[1];
						$pageno++;
					}
				}
				elseif (!$trailStarted) {
					$result['preface'] .= "$line\n";
				}
			}
		}
		return $result;
	}

	// mode == page, trail or collection
	function convert ($mode='trail') {
		ob_end_flush();
		if (!$this->lock()) {
			error_message("page is currently processed by another user, try again later", true);
			return false;
		}
		$ret = false;
		if ($mode == 'trail' || $mode === true) // @@ the latter is for compatibility and is deprecated
			$pageinfo = $this->collectPages();
		elseif ($mode == 'collection') {
			error_message("Collections are not supported by this driver", true);
		}
		else
			$pageinfo[0] = array('depth'=>1, 'pagename'=>$this->pagename, 'title'=>$this->pagename);
			
		if (count($pageinfo) > 0) 
			$ret = $this->convertWiki($pageinfo);
		$this->unlock();
		return $ret;
	}
}


?>
