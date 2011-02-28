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
require_once "$M2MDir/FOProcessor.class.php";
require_once "$M2MDir/XSLTProcessor.class.php";

$KEEPTOKEN = "\234\234";
$KEEPARRAY = array();
$KEEPCOUNT;

class WikiConverterFactory {	
	// 'static' method
	function createConverter ($pagename, $targetFormat) {
		global $ConverterClassPrefixes;
		if (array_search($targetFormat, $ConverterClassPrefixes) === false)
			return false;
		return eval("return new Wiki{$targetFormat}Converter('$pagename');");
	}
	
	// 'static' method
	function availableTargetFormats () {
		global $ConverterClassPrefixes;
		foreach ($ConverterClassPrefixes as $prefix) {
			$descr = eval("return Wiki{$prefix}Converter::targetFormatDescr();");  // PHP identifiers are case-insensitive
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

	function outputDir ($pagename='') {
		global $M2MDataDir;
		if ($pagename == '')
			$pagename = $this->pagename;
		return "$M2MDataDir/$pagename";
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
			$trailname  = $this->pagename;
			$trailtext = $trailpage['text'];
			$trailtext = preg_replace('/\(:include\s+(.+?):\)/e', "\$this->readIncludedText('{$this->pagename}', '\\1')", $trailtext);
			// collect Wiki pages to be part of the trail
			$pageno = 0;
			global $LinkPageExistsFmt, $LinkPageExistsTitleFmt, $LinkPageCreateFmt, $LinkPageCreateSpaceFmt, $LinkPageSelfFmt;
			$savedLinkPageExistsFmt = $LinkPageExistsFmt;
			$savedLinkPageExistsTitleFmt = $LinkPageExistsTitleFmt; // for lazy web extension
			$savedLinkPageCreateFmt = $LinkPageCreateFmt;
			$savedLinkPageCreateSpaceFmt = $LinkPageCreateSpaceFmt;
			$savedLinkPageSelfFmt = $LinkPageSelfFmt;
			$LinkPageExistsFmt = "<wikipage>\$Group.\$Name</wikipage><linktext>\$LinkText</linktext>";
			$LinkPageSelfFmt = $LinkPageCreateFmt = $LinkPageCreateSpaceFmt = $LinkPageExistsTitleFmt = $LinkPageExistsFmt;
			$ignoretrail = false;
			foreach(explode("\n", $trailtext) as $line) {  // process text line by line
				if (preg_match('/^\s*\(:ignoretrail:\)\s*$/', $line))
					$ignoretrail = true;
				elseif (preg_match('/^\s*\(:endignoretrail:\)\s*$/', $line))
					$ignoretrail = false;
				elseif (!$ignoretrail && preg_match("/^T?([#*:]+)\\s*(.*)/", $line, $match)) { // found a list item?
					$depth = strlen($match[1]);  // nesting depth
					$listentry = $match[2];
					// let PmWiki do the work to resolve all the various link types to wiki pages					
					$html = MarkupToHTML($this->pagename, $listentry);
					if (preg_match('#<wikipage>(.*?)</wikipage><linktext>(.*?)</linktext>#si', $html, $m)) {
						$trailStarted = true;
						$result[$pageno] = array(
							'depth'    => $depth,
							'pagename' => $m[1],
							'title'    => $m[2]
						);
						$pageno++;
					}
				}
				elseif (!$trailStarted) { // collect text before trail
					$result['preface'] .= "$line\n";
				}
			}
		}
		$LinkPageSelfFmt = $savedLinkPageSelfFmt;
		$LinkPageExistsFmt = $savedLinkPageExistsFmt;
		$LinkPageExistsTitleFmt = $savedLinkPageExistsTitleFmt;
		$LinkPageCreateFmt = $savedLinkPageCreateFmt;
		$LinkPageCreateSpaceFmt = $savedLinkPageCreateSpaceFmt;
		return $result;
	}

	function evaluateTrail () {
		$trailpage = ReadPage($this->pagename);
		if ($trailpage) {
			global $M2MDir, $MarkupTable, $SuffixPattern;
			$html = MarkupToHTML($this->pagename, $trailpage['text']);
		}
	}

	// mode == page, trail or collection
	function convert ($mode='trail') {
		ob_end_flush();
		if (!$this->lock()) {
			error_message("page is currently processed by another user, try again later", true);
			return false;
		}
		$ret = false;
		if ($mode == 'trail' || $mode === true) { // @@ the latter is for compatibility and is deprecated
			$pageinfo = $this->collectPages();
		}
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
