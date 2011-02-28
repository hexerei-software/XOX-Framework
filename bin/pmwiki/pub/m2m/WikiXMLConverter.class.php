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

require_once 'FOProcessor.class.php';
require_once 'WikiConverter.class.php';
require_once 'XSLTProcessor.class.php';
require_once 'Wellformer.class.php';

class WikiXMLConverter extends WikiConverter {
	function WikiXMLConverter ($pagename) {
		WikiConverter::WikiConverter($pagename);
	}

	function convert ($mode='trail') {
		if ($mode != 'collection')
			return WikiConverter::convert($mode);
		else {
			$ret = false;
			$pageinfo = $this->collectPages();
			message("building collection", start);
			$collxml = "";
			foreach ($pageinfo as $key=>$pi) {
				if ($key == 'preface')
					continue;
				message("processing ".htmlentities($pi['pagename']), 'start');
				$converter = new WikiXMLConverter($pi['pagename']);
				if ($converter->convert('trail')) {
					copy($converter->outputDir()."/source.xml", $this->outputDir()."/$pi[pagename].xml");
					$xml = file_get_contents($this->outputDir()."/$pi[pagename].xml");
					$xml = preg_replace('/^<\?xml\s+.*\?>\n*<!DOCTYPE\s+.*?>\n*/', '', $xml);
					$collxml .= "<!-- wiki page: $pi[pagename] -->\n$xml";
				}
				message('', 'end');
			}
			$head = "<?xml version='1.0'?>\n";
			$collxml = "$head<book>\n$collxml</book>";
			$f = fopen($this->outputDir().'/source.xml', 'w');
			fputs($f, $collxml);
			fclose($f);
			message('done', 'end');
//			$this->createOptionStylesheet();
			$ret = $this->convertXML($this->outputDir().'/source.xml');
			$this->unlock();
			return $ret;
		}
	}
	
	function convertWiki ($pageinfo) {
		global $M2MDir;
		
//		$this->createOptionStylesheet();
		message("creating XML document", 'start');
		// add entity references
		$xml = $this->convertPagesToXML($pageinfo);			
		$dtd = "<!DOCTYPE wikixml [\n";
		$dtd.= "<!ENTITY % HTMLlat1 PUBLIC \"-//W3C//ENTITIES Latin 1 for XHTML//EN\" \"$M2MDir/xml/xhtml-lat1.ent\">\n";
		$dtd.= "<!ENTITY % HTMLsymbol PUBLIC \"-//W3C//ENTITIES Symbols for XHTML//EN\" \"$M2MDir/xml/xhtml-symbol.ent\">\n";
		$dtd.= "<!ENTITY % HTMLspecial PUBLIC \"-//W3C//ENTITIES Special for XHTML//EN\" \"$M2MDir/xml/xhtml-special.ent\">\n";
		$dtd.= "%HTMLlat1;\n";
		$dtd.= "%HTMLsymbol;\n";
		$dtd.= "%HTMLspecial;\n";
		$dtd.= "]>\n";
		$xml = '<?xml version="1.0" encoding="iso-8859-15"?>'."\n$dtd$xml";

		// check well-formedness and fix corresponding errors
		message("checking well-formedness", 'start');
		$outputDir = $this->outputDir();
		$fname = "$outputDir/source.xml";
		@unlink($fname);
		$f = fopen("$fname.1", "w");
		fputs($f, $xml);
		fclose($f);
		unset($xml);
/*		$wf = new WellFormer();
		$wf->processFile("$fname.1", $fname);
		$msg = array();
		if ($wf->numNestingErrors > 0)
			$msg[] = sprintf('%d nesting error%s fixed', $wf->numNestingErrors, $wf->numNestingErrors>1?'s':'');
		if ($wf->numCloseErrors > 0)
			$msg[] = sprintf('%d open element%s closed', $wf->numCloseErrors, $wf->numCloseErrors>1?'s':'');
		$msg = implode(' and ', $msg);
		if ($msg)
			message($msg);*/
		RunTool('wellformer', "IN=$fname.1 OUT=$fname");
		message('', 'end'); // well-formedness check
		message('', 'end'); // XML creation
		
		chmod($fname, 0644);

		if ($this->convertXML($fname) && file_exists("$outputDir/".$this->getOutputFile()))
			return "$outputDir/".$this->getOutputFile();
		return false;
	}	

	// Creates XML from the pages described by $pageinfo
	function convertPagesToXML ($pageinfo) {		
		$xml = "<!-- Frontmatter -->\n";			
		if ($pageinfo['preface'] != '') {
			// extract title and author information
			message("processing trail page ".$this->pagename, 'start');

			while (preg_match('/(\(:(\w+)(\s+.+?)?:\).*?\(:end\2:\)\s*)/s', $pageinfo['preface'], $m)) {
				$xml .= preg_replace('#^\s*<p>(.*)</p>\s*$#s', '$1', MarkupToHTML($this->pagename, trim($m[1])));
				$pageinfo['preface'] = str_replace($m[1], '', $pageinfo['preface']);
			}
			
			while (preg_match('/(\(:\w+(\s+.+?)?:\))\s*/s', $pageinfo['preface'], $m)) {
				$xml .= preg_replace('#^\s*<p>(.*)</p>\s*$#s', '$1', MarkupToHTML($this->pagename, trim($m[1])));
				$pageinfo['preface'] = str_replace($m[1], '', $pageinfo['preface']);
			}

			message('', 'end');
		}
		unset($pageinfo['preface']);
			
		foreach ($pageinfo as $pi) {
			global $WikiDir;
			$pagefile = $WikiDir->pagefile($pi['pagename']);
			$cachefile = $this->outputDir($pi['pagename'])."/cache.xml";
			$msg = "processing page ".htmlentities($pi['pagename']);
			if (0) { // @@
				message("$msg from cache", 'start');
				$localxml = file_get_contents($cachefile);
			}
			else {
				global $ProcessedPages;
				$empty = file_exists($pagefile) ? '' : ' (page empty)';
				message($msg.$empty, 'start');
				$ProcessedPages[] = $pi['pagename'];
			
				list($group) = explode('.', $pi['pagename']);
				$page = ReadPage($pi['pagename']);
				$text = trim($page['text']);
				$text = preg_replace('/^!+.*?\\n/', '', $text);  // remove title header from wiki page
				$text = html_entity_decode($text);
				$title = $pi['title'] ? $pi['title'] : $pi['pagename'];
				$sectcmd = "(:section level=$pi[depth] title=\"$pi[title]\" label=".Keep($pi[pagename]).":)";
				$text = "{$sectcmd}\n$text";  // ...then add a title with name of wiki page

				$text = str_replace(chr(128), '¤', $text);  // das "Windows" ¤-Zeichen liegt auf 128...

				$localxml = MarkupToHTML($pi['pagename'], $text);  // generate XML of current wiki page
				if (file_exists($pagefile)) {            // does wikipage exist?
					recursive_mkdir(dirname($cachefile));
					$f = fopen($cachefile, 'w');
					fputs($f, $localxml);
					fclose($f);
				}
			}
			$xml .= $localxml;
			message('', 'end');
		}
		// close all open section elements
		$xml .= flush_section(0);
		
		$xml = "<article page-width='210mm' page-height='297mm' margin='2cm'>$xml</article>";
		return $xml;
	}

	
	function getOutputFile () {
		return "result.".$this->getFileExtension();
	}
	
	// template method: converts XML file to something different (depends on converter)
	function convertXML($fname) {
		return false;
	}
}


class WikiFOConverter extends WikiXMLConverter {

	function WikiFOConverter ($pagename) {
		WikiXMLConverter::WikiXMLConverter($pagename);
	}

	function convertXML ($fname) {
		global $M2MDir;
		message("converting XML to XSL-FO", 'start');
		$outputDir = $this->outputDir();
		$fofile = "$outputDir/result.fo";
		//		$stylesheet = "$outputDir/{$targetFormat}local.xsl";
		$targetformat = strtolower(preg_replace('/^Wiki(.+?)Converter$/i', '$1', get_class($this)));
		$stylesheet = "$outputDir/$targetformat.xsl";
//		$stylesheet = WikiFOConverter::stylesheet();
		$foproc = new XepFOProcessor();
		$xsltproc = new GnomeXSLTProcessor();
		@unlink($fofile);
		$xsltproc->process($stylesheet, $fname, $fofile);
		message("done", 'end');
		if (file_exists($fofile))
			return $this->convertFO($fofile, $foproc);
		return false;
	}


	function convertFO ($fname, $foproc) {
		return false;
	}

	function stylesheet () {
		global $M2MDir;
		return "$M2MDir/xsl/fo/wikixml.xsl";
	}
}



class WikiPDFFOConverter extends WikiFOConverter {
	
	function WikiPDFFOConverter ($pagename) {
		WikiFOConverter::WikiFOConverter($pagename);
	}

	
	function convertFO ($fofile, $foproc) {		
		if (is_object($foproc)) {
			message("converting FO to PDF");
			$fname = file_strip_extension($fofile);
			@unlink("$fname.pdf");
			$ok = $foproc->process($fofile, "$fname.pdf");
			message("done", 'end');
			if ($ok && file_exists("$fname.pdf"))
				return "$fname.pdf";
			return false;
		}
		error_message("no FO processor specified", false);
		return false;
	}

	function getFileExtension () {return 'pdf';}
	function targetFormatDescr () {return 'PDF (via FO)';}
}

?>
