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


class CollectionConverter
{
	var $targetFormat;
	var $pagename;	

	function CollectionConverter ($targetFormat, $pagename) {
		$this->targetFormat = $targetFormat;
		$this->pagename = $pagename;
	}

	function convertWiki ($wikitext) {
		global $SuffixPattern,$GroupPattern,$WikiWordPattern,$LinkWikiWords;
		global $SpaceWikiWords,$AsSpacedFunction,$SpaceWikiWordsFunction;
		global $HandlePublishFmt,$PublishPageNotFoundFmt;
		global $GCount,$IncludeModifiedDate,$NumberPages;
		foreach(explode("\n", $trailtext) as $line) {  // process text line by line
			if (preg_match("/^T?([#*:]+)\\s*(.*)/", $line, $match)) { // found a list item?
				$match[1] = str_replace(':#', ':', $match[1]);
				if ($LinkWikiWords) 
					$match[2] = preg_replace("/^($GroupPattern([\\/.]))?($WikiWordPattern)/e", 
						"'[[$1'.(($SpaceWikiWords)?$SpaceWikiWordsFunction('$3'):'$3').']]'", 
						$match[2]);

				$match[2] = preg_replace("/\\[\\[([^\\]]*)->([^\\]]*)\\]\\]/",'[[$2|$1]]', $match[2]);
				if (preg_match("/^(\\[\\[([^|]*?)(\\|.*?)?\\]\\]($SuffixPattern))/", $match[2], $m)) {
					$trails[] = MakePageName($this->pagename, $m[2]);
				} 
			}
		}
		$xmlfile = "$M2MDataDir/{$this->pagename}/source.xml";
		if (file_exists($xmlfile))
			unlink($xmlfile);
		foreach ($trails as $trail) {
			message("processing $trail", 'start');
			$converter = new DocBookConverter($trail); //ConverterFactory::createConverter($trail, $this->targetFormat);
			$converter->convert();
			message("done", 'end');
			message("merging documents");
			$xml = file_get_contents("$M2MDataDir/$trail/source.xml");
			$xml = preg_replace('/^(.*?\n){2}/s', '', $xml);
			$f = fopen($xmlfile, "a");
			fputs($f, $xml);
			fclose($f);
		}
		$conv = ConverterFactory::createConverter($this->pagename, $this->targetFormat);
		$conv->convertDocBook($xmlfile);
	}
}

?>
