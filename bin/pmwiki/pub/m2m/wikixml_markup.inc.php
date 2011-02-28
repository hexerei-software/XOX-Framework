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

ReplaceMarkup('m2mpublish', 'directives', '/\(:publish:\)/', '');

Markup('ignoretrail', 'inline', '/\(:(end)?ignoretrail:\)/', '');
Markup('doctitle', 'block', '/\(:doctitle\s+(.+?):\)/e', "'<:block>'.Keep('<title>$1</title>')");
Markup('author', '>doctitle', '/\(:author\s+(.+?):\)/e', "'<:block>'.Keep(doAuthor(\$pagename, '$1'))");
Markup('institution', '>author', '/\(:institution\s+(.+?):\)/e', "'<:block>'.Keep('<institution>$1</institution>')");
Markup('info', '<split', '/\(:info:\)(.*?)\(:endinfo:\)/es', "doInfo(\$pagename, '$1')");
Markup('abstract', '<split', '/\(:abstract:\)(.*?)\(:endabstract:\)/s', '<abstract>$1</abstract>');
Markup('preface', '<split', '/\(:preface(\s+(.+?))?:\)(.*?)\(:endpreface:\)/se', "doPreface('$2', '$3')");

function doPreface ($attrstr, $text) {
	global $MarkupRules;
	$attribs = new Attributes($attrstr);
	$attr = $attribs->getAttribs('html');
	$title = trim($attr['title']);
	if ($title != '')
		$title = " title='$title'";
	return Keep("<preface$title>").$text.Keep('</preface>');
}

function doAuthor ($pagename, $author) {
	$authors = explode(',', $author);
	$ret = "<authors>\n";
	foreach ($authors as $a) {
		$a = trim($a);
		$ret .= "<author>$a</author>\n";
	}
	$ret .= "</authors>\n";
	return $ret;
}


function doInfo ($pagename, $text) {
	return '';
}



Markup('section', 'block', '/\(:section\s*(.*?):\)/es', "'<:block>'.Keep(doSection('$1'))");


function doSection ($attrstr) {
	$attrstr = html_entity_decode($attrstr);
	$attr = new Attributes($attrstr);
	$preface = $attr->getAttrib('preface');
	if ($preface !== false) {
/*		flush_section(0);
		if ($preface == 'start')
			$ret = "<abstract>";
		elseif ($preface == 'end')
			$ret = "</abstract>"; */
	}
	else {
		global $current_section;
		$level = $attr->getAttrib('level');
		$ret = flush_section($level) . "<section";
		$current_section = $level;
		$label = trim($attr->getAttrib('label'));
		if ($label != '') {
			$ret .= ' id="'.substitute_umlauts($label).'"';
			$ret .= ' wikipage="'.substitute_umlauts($label).'"';
		}
		$title = $attr->getAttrib('title');
		$ret.= "><title>".xmlencode($attr->getAttrib('title'))."</title>";
	}
	return $ret;
}


function flush_section($newlevel) {
	global $current_section;
	$out="";
	if (!isset($current_section)) 
		$current_section = 0;
	if ($newlevel == $current_section+1) {
		// nested section, ok
	} 
	elseif ($newlevel < $current_section) {
		// close sections
		for ($i=$current_section; ($i>=$newlevel && $i>0); $i--) 
			$out .= "</section>";
	} 
	elseif ($newlevel > $current_section) {
		// section jump - open additional sections
		for ($i = 1+max($current_section, 0); $i < $newlevel; $i++) 
			$out .= "<section><title></title>";
	} 
	elseif ($newlevel==$current_section) {
		// parallel section, close one
		$out .= "</section>";
	}
	$current_section = $newlevel;	
	return $out;
}


Markup('textlinklink', '<[[|', '/\[\[\s*\(:textlink\s+(.*?):\)\s*\|\s*(.*?)\s*\]\]/', "");
Markup('textlink', '>textlinklink', '/\(:textlink\s+(.*?):\)/e', "doTextLink(\$pagename, '$1')");


function doTextLink ($pagename, $attrstr, $linktext='') {
	$attribs = new Attributes($attrstr);
	$attr = $attribs->getAttribs('html');
	if (!isset($attr['file'])) 
		return '';
	return Keep($attr['file']);
}


// -------------------------------
// handle (:fn ... :) statement
// -------------------------------
Markup('fn1', '<split', '/\\(:fn\s+(.*?):\\)/s', "<footnote>\\1</footnote>");


// -------------------------------
// handle (:newpage:) statement
// -------------------------------
Markup('pagebreak', '<block', '/\(:newpage:\)/', '<newpage/>');


// ------------------------------------------------
// handle LaTeX math statements \(...\) and \[...\]
// ------------------------------------------------

## inline math formula
ReplaceMarkup('\(', 'inline', '/\\\\\\((.*?)\\\\\\)/es', 'doMath(\$pagename, "$1", "class=inline")');

## "display" math formula
ReplaceMarkup('\[', '<split', '/\\\\\\[(.*?)\\\\\\]/es', 'doMath(\$pagename, html_entity_decode("$1"), "class=displaymath")');

Markup('math', '<split', '/\(:math(\s+.*?):\)(.*?)\(:endmath:\)/es', 'doMath(\$pagename, html_entity_decode("$2"), "$1")');

require_once 'LatexScriptObject.class.php';
function doMath ($pagename, $source, $attrstr) {
	global $CreateMathML, $UsedMathML;
	static $mathno;
	if (trim($source) == '')
		return '';
	$source = str_replace("\\'", "'", $source);
	$source = str_replace("\\\"", '"', $source);

	$mode = '';
	if (trim($attrstr) != '') {
		$attribs = new Attributes($attrstr);
		$attr = $attribs->getAttribs('html');
		$mode = trim($attr['class']);
	}
	if ($mode == '')
		$mode = 'displaymath';
	$mso = new MathScriptObject($pagename, $source, false, $mode);
/*	if ($CreateMathML) {
		$mathno++;
		message("creating MathML of formula #$mathno");
		$UsedMathML = true;
		$xml = $mso->createMathML($inlined);
	}
	else { */
		$mo_pdf = $mso->convert('pdf', false, true);
		$mo_eps = $mso->convert('eps', false, true);
		$mo_png = $mso->convert('png', false, true);
		$mathtag = ($mode == 'inline') ? "inlinemath" : "math";
		if ($mo_pdf === false || $mo_eps === false || $mo_png === false) { // LaTeX error?
			//			$xml = "<$mediaobject><textobject><para>ERROR IN MATH FORMULA</para></textobject></$mediaobject>\n";
			$xml = "<text>ERROR IN MATH FORMULA</text>\n";
		}
		else {
			$xml = "<imageobject>\n";
			$xml.= $mo_eps->getWikiXML('fo', array()) . "\n";
			$xml.= $mo_pdf->getWikiXML('fo', array()) . "\n";
			$xml.= $mo_png->getWikiXML('html', array()) ."\n";
			$xml.= "</imageobject>\n";
		}
//	}
	$mmode = ($mode == 'inline') ? '$' : '$$';  // @@
	$ret = "<$mathtag>\n<alt role='latex'>".htmlspecialchars($source)."</alt>\n$xml</$mathtag>";
	return Keep($ret);
}


// ------------------------------
// handle (:code ... :) statement
// ------------------------------
Markup('programcode1', '<split', '/\(:code\s+((\S+\s+)?file\s*=.*):\)/Ues', "doCode(\$pagename, '', '\\1')");
Markup('programcode2', '>programcode1', '/\(:code(\s+.*)?:\)(.*)\(:endcode:\)/Ues', "doCode(\$pagename, '\\2', '\\1')");

function doCode ($pagename, $code, $attrstr) {
	$code = trim(html_entity_decode($code));
	$attribs = new Attributes($attrstr, array('print'=>'fo'));
	$attr = $attribs->getAttribs('fo');
	if ($code == '') {
  		if ($attr['file'] != '') {
			$co = new CodeObject($pagename, $attr['file']);
			$co->linkUploadedFile($attr['file']);
			if ($co->exists())
				$code = $co->getCode();
		}
		else return '';
	}
	$tabwidth = (isset($attr['tab']) && $attr['tab'] >= 0 && $attr['tab'] <= 10) ? $attr['tab'] : 4;
	$code = str_replace('\"', '"', html_entity_decode($code)); // replace already created entities
	$code = str_replace('<:vspace>', '', $code);	
	$code = str_replace("\t", substr('          ', 0, $tabwidth), $code);
	$co = new CodeObject($pagename, $code, false);		
	$html = $co->getWikiXML('fo', $attr);
	$co->cleanup();   // remove temporary files 
	return Keep($html);
}

// ----------------------------------------------
// handle (:gnuplot ... :) and related statements
// ----------------------------------------------
Markup('gplot', '<split', 
	'/\\(:gnuplot(\s+.*)?:\\)(.*)\\(:endgnuplot:\\)/Ues', 
	"doScript(\$pagename, 'gnuplot', '\\2', '\\1')");

Markup('gploti', '<gplot', '/\(:gnuplot\s+((\S+\s+)?file\s*=.*):\)/Ues', "doScript(\$pagename, 'gnuplot', '', '\\1')");

Markup('tipa', '<split', 
	'/\\(:ipa(\s+.*)?:\\)(.*)\\(:endipa:\\)/Ues', 
	"doScript(\$pagename, 'tipa', '\\2', '\\1')");

// ----------------------------------------------
// handle (:pec ... :) statement
// ----------------------------------------------
Markup('pec', '<split', 
	'/\(:pec(\s+.*)?:\)(.*)\(:endpec:\)/Ues', 
	"doScript(\$pagename, 'pec', '\\2', '\\1')");


require_once('ScriptObjectFactory.class.php');
function doScript ($pagename, $scriptFormat, $code, $attrstr) {
	$attr = new Attributes($attrstr, array('print'=>'fo'));
	$attr = $attr->getAttribs('fo');
	if ($code == '')
		$so = ScriptObjectFactory::createScriptObjectFromFile($pagename, $scriptFormat, $attr['file']);
	else {
		$code = html_entity_decode($code);
		$code = str_replace("\\'", "'", $code);
		$code = str_replace("\\\"", '"', $code);
		$so = ScriptObjectFactory::createScriptObjectFromCode($pagename, $scriptFormat, $code);
	}	
	if ($so !== false) {
		$mo_png = $so->convert('png', $attr);
		$mo_eps = $so->convert('eps', $attr);
		$mo_pdf = $so->convert('pdf', $attr);
		$xml = "<imageobject>\n";
		if ($mo_png !== false)
			$xml.= $mo_png->getWikiXML('html', $attr) . "\n";
		if ($mo_eps !== false)
			$xml.= $mo_eps->getWikiXML('fo', $attr) . "\n";
		if ($mo_pdf !== false)
			$xml.= $mo_pdf->getWikiXML('fo', $attr) . "\n";
		$xml.= "</imageobject>\n";
		return Keep($xml);
	}
	return '';
}


// -------------------------------
// handle (:embed ... :) statement
// -------------------------------
Markup('embed', '<split', '/\\(:embed(.*?):\\)/es', "doEmbed(\$pagename, '\\1')");

require_once 'Embed.class.php';
function doEmbed ($pagename, $attrstr) {
	$embed = new Embed($pagename, $attrstr);
	return Keep($embed->getWikiXML());
}

// --------------------------------
// handle (:applet ... :) statement
// --------------------------------
Markup('applet', '<split', '/\(:applet\s+(.*?):\)/es', "doApplet(\$pagename, '\\1')");

function doApplet ($pagename, $attrstr) {
	$attribs = new Attributes($attrstr);
	$attr = $attribs->getAttribs('fo');
	if (isset($attr['file'])) {
		$embed = new Embed($pagename, $attrstr);
		return Keep($embed->getWikiXML());
	}
	return '';
}

// -------------------------------------
// handle (:googlevideo ... :) statement
// -------------------------------------
Markup('googlevid', '<split', '/\(:googlevideo\s+(.*?):\)/es', "doGoogleVideo(\$pagename, '\\1')");

function doGoogleVideo ($pagename, $attrstr) {
	$attr = new Attributes($attrstr, array('print'=>'fo'));
	$attr = $attr->getAttribs('fo');
	if (isset($attr['file'])) {
		$embed = new Embed($pagename, $attrstr);
		return Keep($embed->getWikiXML());
	}
	return '';
}

// ---------------------------------
// handle (:youtube ... :) statement
// ---------------------------------
Markup('youtube', '<split', '/\(:youtube\s+(.*?):\)/es', "doYouTube(\$pagename, '\\1')");
function doYouTube ($pagename, $attrstr) {
	$attr = new Attributes($attrstr, array('print'=>'fo'));
	$attr = $attr->getAttribs('fo');
	unset($attr['id']);
	if (isset($attr['file'])) {
		$embed = new Embed($pagename, $attrstr);
		return Keep($embed->getWikiXML());
	}
	return '';
}
?>
