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

require_once 'Converter.class.php';
require_once 'debugfuncs.php';

class PlainTextConverter extends Converter {	
	
	function convert ($pagename) {
		$wikiText = $this->collectPagesFromTOC($pagename);	

		$rules = array('/\[([=@])(.*?)\1\]/se => Keep(\'\2\')',
					      '/&lt;/ => <',
							'/\\\\\(.+\\\\\)/sU =>',
							'/\\\\\[.+\\\\\]/sU =>',
						   '/^!+(\[\[#.*?\]\])?\s*/m =>', 
						   '/\(:gnuplot.*:\).*\(:endgnuplot:\)/sU =>',
						   '/\(:code.*:\).*\(:endcode:\)/sU =>',
						   '/<:.*?>/ =>',
						   '/\(:.*?:\)\s*/ =>', 
						   '/^-+>\s*/m =>', 
						   '/^-+&lt;\s*/m =>', 
						   '/^----+/m =>',
						   '/^[*#:]+\s*/m =>',
							'/^\|\|(\s*[^|=]+=[^|=]+)+$/m =>',
							'/\|\|+!?/ =>',
				         "/(''|'''|'''''|@@|\\^|_|\\++|-+)(.+?)\\1/ => \\2",
						   '/\\{([+|-])(.+?)\1/ => \2',
						   '#\[\[.*[|/](.*)\]\]#U => \1',
						   '/\[\[.*|#.*\]\]/U =>');
		
		foreach ($rules as $rule) {
			list($match, $repl) = explode("=>", $rule);
			$match = trim($match);
			$repl  = trim($repl);
			$wikiText = preg_replace($match, $repl, $wikiText);
		}
		global $KeepToken, $KPV;
		$wikiText = preg_replace("/$KeepToken(.*?)$KeepToken/e", '$KPV[\'\1\']', $wikiText);
		return $wikiText;
	}
}

?>
