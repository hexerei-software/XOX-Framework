<? if (!defined('PmWiki')) exit();
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

	
Markup('author', '<split', '/\(:author\s+(.+?):\)/es', "doAuthor(\$pagename, '$1')");
Markup('doctitle', '<split', '/\(:doctitle\s+(.+?):\)/es', "doDocTitle(\$pagename, '$1')");
Markup('institution', '<split', '/\(:institution\s+(.+?):\)/es', "doInstitution(\$pagename, '$1')");
Markup('info', '<split', '/\(:info:\)(.*?)\(:endinfo:\)/es', "doInfo(\$pagename, '$1')");

// ignore (:newpage:) in wiki view
Markup('newpage', 'inline', '/\(:newpage:\)/', '');
Markup('ignoretrail', 'inline', '/\(:(end)?ignoretrail:\)/', '');

function doAuthor ($pagename, $author) {
	return "<h3>$author</h3>";
}

function doDocTitle ($pagename, $title) {
	return "<h2>$title</h2>";
}

function doInstitution ($pagename, $inst) {
	return "<h4>$inst</h4>";
}

function doInfo ($pagename, $text) {
	return "<h5>$text</h5>";
}

Markup('abstract', '<split', '/\(:abstract:\)(.*?)\(:endabstract:\)/se', "doAbstract('$1')");

function doAbstract ($text) {
	return Keep("<div style='margin-top:10px;margin-left:5%;margin-right:5%'><p><b>Abstract</b></p><p>").$text.Keep("</p></div>");
}

Markup('preface', '<split', '/\(:preface(\s*(.+?))?:\)(.*?)\(:endpreface:\)/se', "doPreface('$2', '$3')");

function doPreface ($attrstr, $text) {
	$attribs = new Attributes($attrstr);
	$attr = $attribs->getAttribs('html');
	$title = trim($attr['title']);
	if ($title == '')
		$title = 'Vorwort';
	return Keep("<div style='margin-top:10px;margin-left:5%;margin-right:5%'><p><b>$title</b></p><p>").$text.Keep("</p></div>");
}


## inline math formula
Markup('\(', 'inline', '/\\\\\\((.*?)\\\\\\)/es', 'doMath(\$pagename, html_entity_decode("$1"), "class=inline")');

## "display" math formula
Markup('\[', '<split', '/\\\\\\[(.*?)\\\\\\]/es', 'doMath(\$pagename, html_entity_decode("$1"), "class=displaymath")');

Markup('math', '<split', '/\(:math(\s+.*?):\)(.*?)\(:endmath:\)/es', 'doMath(\$pagename, html_entity_decode("$2"), "$1")');

require_once('ScriptObjectFactory.class.php');
function doMath ($pagename, $source, $attrstr) {
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
	$so = ScriptObjectFactory::createScriptObjectFromCode($pagename, 'math', $source, $mode);
	if ($so === false)
		return '';	
	$mo = $so->convert('png', false);
	if ($mo === false) { // LaTeX error?
		$msource = htmlentities($so->getMarkedSource());
		$msource = preg_replace('#\[error\](\s*)(.*?)\[/error\]#s', '$1<font style="color:blue;background-color:yellow">$2</font>', $msource);
		$ret  = "<pre><font color='red'>LaTeX error in math formula: ";
		$ret .= "<b>".preg_replace('/^(!\s*)?(.+?)\n.*$/s', '\2', $so->message)."</b>";
		$ret .= "</font><br><font color=\"blue\">$msource</font></pre><p>";
	}
	else {
//		M2MMarkReference($pagename, $mo->fname, $mo->type(), "generated math image");
//		$url = $mo->url();
/*		$shift = '';
		if ($inlined) {
			// if possible, compute vertical (baseline) shift of math image
			$extent = $mo->getExtent();
			if ($extent !== false) {
				list($img_w, $img_h) = getimagesize($mo->path());
				$shift = round($img_h * $extent['depth' ] / ($extent['height']+$extent['depth']));
				$shift = " style='vertical-align:-{$shift}px'";
			}
		}
		$ret = "<img class='latexmath' src='$url' border='0'$shift>"; */
		$ret = $mo->getHTML(false);
	}
	if ($mode != 'inline')
		$ret = "<p>$ret</p>";
	return Keep($ret);
}
		

// ------------------------------
// handle (:code ... :) statement
// ------------------------------
Markup('programcode1', '<split', '/\(:code\s+((\S+\s+)?file\s*=.*):\)/Ues', "doCode(\$pagename, '', '\\1')");
Markup('programcode2', '>programcode1', '/\(:code(\s+.*)?:\)(.*)\(:endcode:\)/Ues', "doCode(\$pagename, '\\2', '\\1')");
//Markup('programcodef', '<programcode', '/\\(:code (file=.+):\\)/Ues', "doCode('\\2', '\\1', false)");

require_once 'CodeObject.class.php';

function doCode ($pagename, $code, $attrstr) {
	$code = trim($code);
	$attribs = new Attributes($attrstr);
	$attr = $attribs->getAttribs('html');
	if ($code == '') {
  		if ($attr['file'] != '') {
			$co = new CodeObject($pagename, $attr['file']);
			$co->linkUploadedFile($attr['file']);
			if ($co->exists())
				$code = $co->getCode();
			else {
				global $ScriptUrl;
				$msg = "code file <i>$attr[file]</i> not found<br>";
				$msg.= "<a href='$ScriptUrl?n=$pagename/?action=upload&upname=$attr[file]'>upload now</a>";
				return Keep(errorHTML($msg));
			}
		}
		else return '';
	}
	$tabwidth = (isset($attr['tab']) && $attr['tab'] >= 0 && $attr['tab'] <= 10) ? $attr['tab'] : 4;
	$code = str_replace('\"', '"', html_entity_decode($code)); // replace already created entities
	$code = str_replace('<:vspace>', '', $code);	
	$code = str_replace("\t", substr('          ', 0, $tabwidth), $code);
	$co = new CodeObject($pagename, $code, false);		
	$html = $co->getHTML($attr);
	$co->cleanup();   // remove temporary files 
	return Keep($html);
}

Markup('textlinklink', '<[[|', '/\[\[\s*\(:textlink\s+(.*?):\)\s*\|\s*(.*?)\s*\]\]/e', "doTextLink(\$pagename, '$1', '$2')");
Markup('textlink', '>textlinklink', '/\(:textlink\s+(.*?):\)/e', "doTextLink(\$pagename, '$1')");

function doTextLink ($pagename, $attrstr, $linktext='') {
	$attribs = new Attributes($attrstr);
	$attr = $attribs->getAttribs('html');
	if (!isset($attr['file'])) 
		return '';
	if (!file_exists(getUploadPath($pagename, $attr['file']))) {
		global $ScriptUrl;
		$msg = "textlink file <i>$attr[file]</i> not found<br>";
		$msg.= "<a href='$ScriptUrl?n=$pagename/?action=upload&upname=$attr[file]'>upload now</a>";
		return Keep(errorHTML($msg));
	}
	$url = FmtPageName("\$PageUrl?action=m2m-showfile&amp;f=$attr[file]", $pagename);
	if ($linktext == '')
		return Keep("<a href='$url'>$attr[file]</a>");
	return Keep("<a href='$url'>").$linktext.Keep("</a>");
}

// ----------------------------------------------
// handle (:gnuplot ... :) and related statements
// ----------------------------------------------
Markup('script', '<split', 
	'/\(:script(\s+.*)?:\)(.*)\(:endscript:\)/Ues', 
	"doScript(\$pagename, '', '\\2', '\\1')");
Markup('gplot', '<split', 
	'/\(:gnuplot(\s+.*)?:\)(.*)\(:endgnuplot:\)/Ues', 
	"doScript(\$pagename, 'gnuplot', '\\2', '\\1')");

Markup('gploti', '<gplot', '/\(:gnuplot\s+((\S+\s+)?file\s*=.*):\)/Ues', "doScript(\$pagename, 'gnuplot', '', '\\1')");

//Markup('metapost', '<split', 
//	'/\\(:metapost(\s+.*)?:\\)(.*)\\(:endmetapost:\\)/Ues', 
//	"doScript(\$pagename, 'mpost', '\\2', '\\1')");

// ----------------------------------------------
// handle (:tipa ... :) statement
// ----------------------------------------------
Markup('tipa', '<split', 
	'/\(:ipa(\s+.*)?:\)(.*)\(:endipa:\)/Ues', 
	"doScript(\$pagename, 'tipa', '\\2', '\\1')");

// ----------------------------------------------
// handle (:pec ... :) statement
// ----------------------------------------------
Markup('pec', '<split', 
	'/\(:pec(\s+.*)?:\)(.*)\(:endpec:\)/Ues', 
	"doScript(\$pagename, 'pec', '\\2', '\\1')");

// ----------------------------------------------
// handle (:fig ... :) statement
// ----------------------------------------------
/*Markup('fig', '<split', '/\(:fig\s+((\S+\s+)?file\s*=.*):\)/Ues', "doScript(\$pagename, 'fig', '', '\\1')");
Markup('fig', '<split', 
	'/\(:fig(\s+.*)?:\)(.*)\(:endfig:\)/Ues', 
	"doScript(\$pagename, 'fig', '\\2', '\\1')");*/

require_once 'ScriptObjectFactory.class.php';
function doScript ($pagename, $scriptFormat, $code, $attrstr) {
	$attribs = new Attributes($attrstr);
	$attr = $attribs->getAttribs('html');
	if ($scriptFormat == '')  // (:script ...:) => get script type from attributes
		$scriptFormat = $attr['type'];
	if ($code == '') 
		$so = ScriptObjectFactory::createScriptObjectFromFile($pagename, $scriptFormat, $attr['file']);
	else {
		$code = str_replace("\\'", "'", $code);
		$code = str_replace("\\\"", '"', $code);
		$so = ScriptObjectFactory::createScriptObjectFromCode($pagename, $scriptFormat, $code);
	}
	if ($so !== false) {
		$mo = $so->convert('png', $attr);
	//	M2MMarkReference($pagename, $mo->fname, $mo->type(), "generated $scriptFormat image");
		if (is_object($mo))
			return Keep($mo->getHTML($attr));
	}
	return '';
}

// -------------------------------
// handle (:embed ... :) statement
// -------------------------------
Markup('embed', '<split', '/\(:embed\s+(.*?):\)/es', "doEmbed(\$pagename, '\\1')");

require_once 'Embed.class.php';
function doEmbed ($pagename, $attr) {
	$embed = new Embed($pagename, $attr);
//	$mo = $embed->getMediaObject('html');
//	if ($mo !== false)
		//M2MMarkReference($pagename, $mo->fname, $mo->type());
	return Keep($embed->getHTML(true));  // true => create error messages if necessary
}


// --------------------------------
// handle (:applet ... :) statement
// --------------------------------
Markup('applet', '<split', '/\(:applet\s+(.*?):\)/es', "doApplet(\$pagename, '\\1')");

require_once 'AppletObject.class.php';
function doApplet ($pagename, $attrstr) {
	$attribs = new Attributes($attrstr);
	$attr = $attribs->getAttribs('html');
	if (isset($attr['file'])) {
		$ao = new AppletObject($pagename, $attr['file'], $attr);
		return Keep($ao->getHTML($attr));
	}
	return '';
}

// -------------------------------------
// handle (:googlevideo ... :) statement
// -------------------------------------
Markup('googlevid', '<split', '/\(:googlevideo\s+(.*?):\)/es', "doGoogleVideo(\$pagename, '\\1')");

function doGoogleVideo ($pagename, $attrstr) {
	$attribs = new Attributes($attrstr);
	$attr = $attribs->getAttribs('html');
	if (isset($attr['id'])) {
		$attr['id'] = preg_replace('/\W/', '', $attr['id']);
		$ret = '<embed style="width:400px; height:326px" id="VideoPlayback" type="application/x-shockwave-flash"'
			  . " src='http://video.google.com/googleplayer.swf?docId=$attr[id]&hl=en'></embed>";
		return Keep($ret);
	}
	return '';
}

// ---------------------------------
// handle (:youtube ... :) statement
// ---------------------------------
Markup('youtube', '<split', '/\(:youtube\s+(.*?):\)/es', "doYouTube(\$pagename, '\\1')");

function doYouTube ($pagename, $attrstr) {
	$attribs = new Attributes($attrstr);
	$attr = $attribs->getAttribs('html');
	if (isset($attr['id'])) {
		$attr['id'] = preg_replace('/\W/', '', $attr['id']);
		$w = isset($attr['width']) ? $attr['width'] : 425;
		$h = isset($attr['height']) ? $attr['height'] : 350;
		$ret = "<object width='$w' height='$h'>\n";
		$ret.= "<param name='movie' value='http://www.youtube.com/v/$attr[id]'/>\n";
	  	$ret.= "<param name='wmode' value='transparent'/>\n";
		$ret.= "<embed src='http://www.youtube.com/v/$attr[id]' type='application/x-shockwave-flash' wmode='transparent' width='$w' height='$h'/>\n";
		$ret.= "</object>";
		return Keep($ret);
	}
	return '';
}

// -------------------------------
// handle (:fn ... :) statement
// -------------------------------
Markup('fn1', '<split', '/\(:fn\s+(.*?):\)/es', "recordFootnote(\$pagename, '\\1')");
//Markup('fn2', '>_end', '/$/es', "insertFootnotes(\$pagename)");

function recordFootnote ($pagename, $fntext) {
	global $FOOTNOTES, $MarkupFrame;
	$fntext = trim($fntext);
	if ($fntext == '')
		return '';
	$FOOTNOTES[] = $fntext;
	$number = count($FOOTNOTES);
	// insert all recorded footnotes of current page after markup processing
	SDV($MarkupFrame[0]['posteval']['footnote'], "\$out.=insertFootnotes('$pagename');");
	return "<sup><a href='#fn$number'>$number</a></sup>"; // insert footnotemark in text body
}

function insertFootnotes ($pagename) {
	global $FOOTNOTES;
	if (!is_array($FOOTNOTES))  // no footnotes => no output
		return '';

	$fnotes = $FOOTNOTES;
	$FOOTNOTES = false;    // avoid infinite recursion
	$ret = "<hr>"; // align='left'>";
	foreach ($fnotes as $num=>$fntext) {
		$num++;
		$fntext = MarkupToHTML($pagename, $fntext);                  // apply formattings to footnote text
		$fntext = preg_replace('#^<p>(.*?)</p>$#s', '\1', $fntext);  // remove outer paragraph
		$ret .= "<p><a name='fn$num'><sup>$num</sup></a> $fntext</p>\n";
	}
	return $ret;
}

