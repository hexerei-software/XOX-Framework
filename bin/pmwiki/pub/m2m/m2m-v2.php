<? if (!defined('PmWiki')) exit;
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


SDV($M2MDir, "$FarmD/pub/m2m");               // directory where m2m scripts are located
SDV($M2MDataDir, dirname($WorkDir)."/m2m.d"); // directory where m2m output is written to
if (preg_match('/\.php$/', $ScriptUrl))
	SDV($M2MDataUrl, preg_replace('#/[^/]+?\.php$#', '/m2m.d', $ScriptUrl));  // URL of m2m output directory
else
	SDV($M2MDataUrl, "$ScriptUrl/m2m.d");  // URL of m2m output directory

if (!defined('media2mult'))
	define('media2mult', 1);

include "$M2MDir/debugfuncs.php";
include "$M2MDir/functions.php";
require "$M2MDir/m2m-config.php";

// $M2MUrl must contain a valid URL of the m2m script directory
// the variable should be set in the farm's/field's config.php
if (!isset($M2MUrl)) {
	// try to locate m2m.php in pub directory
	if (is_resource($f = @fopen("$PubDirUrl/m2m/m2m-v2.php", 'r'))) {
		fclose($f);
		$M2MUrl = "$PubDirUrl/m2m";  // ok, found it
	}
	else {
		$msg = '<i>media2mult</i> is not properly configured.<br>';
		$msg.= 'Assign a valid value to <i>$M2MUrl</i> in the field\'s configuration file.';
		die("<br><table cellpadding='5'><tr><td bgcolor='black'><font color='orange'>$msg</font></td></tr></table>");
	}
}
if (!is_dir($M2MDataDir)) {
	$msg = "The <i>media2mult</i> output directory m2m.d does not exist.<br>";
	$msg.= "Please contact your server administrator.";
	die("<br><table cellpadding='5'><tr><td bgcolor='black'><font color='orange'>$msg</font></td></tr></table>");
}

// Füge einen Feature-Identifier zum SOAP-Objekt hinzu
if (is_array($OnCreateSoapObjectFunc)) {  
	function m2m_CreateSoapEvent($soap4pmwiki) {
		$soap4pmwiki->addFeature("media2mult", 1, 0, 0); // Versionsnummer
	}
	$OnCreateSoapObjectFunc[] = "m2m_CreateSoapEvent";
}

$M2MDataDir = realpath($M2MDataDir);
SDV($HandleActions['m2m-options'], 'HandleM2MOptions');
SDV($HandleActions['m2m-saveoptions'], 'HandleM2MSaveOptions');
SDV($HandleActions['m2m-publish'], 'HandleM2MPublish');
SDV($HandleActions['m2m-showfile'], 'HandleM2MShowFile');
SDV($HandleActions['check-m2m-config'], 'HandleCheckM2MConfig');
Markup('m2mpublish', 'directives', '/\(:publish:\)/e', "m2mButtons(\$pagename)");

if ($action == 'm2m-publish') {
	// create m2m output directory if necessary
	if (!is_dir($M2MDataDir))
		recursive_mkdir($M2MDataDir);
}
else {
	include "$M2MDir/html_markup.inc.php";     // load additional html markup
}

///////////////////////////////////////////////////////////



function m2mButtons ($pagename) {
	global $M2MUrl;
	$url = FmtPageName('$PageUrl', $pagename) . "?action=m2m-publish";
	$ret = '';
	$ret = <<<content
		<script language='JavaScript'>
			function selectFormat() {
				format = document.m2mform.targetformat.value;
				document.m2mform.options.disabled = (format == 'zip');
				setCookie('m2m-format', format);
			}

			function getCookie (name) {  
				var arg = name + "=";  
				var alen = arg.length;  
				var clen = document.cookie.length;  
				var i = 0;  
				while (i < clen) {
					var j = i + alen;    
					if (document.cookie.substring(i, j) == arg)      
						return getCookieVal (j);    
					i = document.cookie.indexOf(" ", i) + 1;    
					if (i == 0) break;   
				}  
				return null;
			}
			
			function setCookie (name, value) {  
				var argv = setCookie.arguments;  
				var argc = setCookie.arguments.length;  
				var expires = (argc > 2) ? argv[2] : null;  
				var path = (argc > 3) ? argv[3] : null;  
				var domain = (argc > 4) ? argv[4] : null;  
				var secure = (argc > 5) ? argv[5] : false;  
				document.cookie = name + "=" + escape (value) + 
					((expires == null) ? "" : ("; expires=" + expires.toGMTString())) + 
					((path == null) ? "" : ("; path=" + path)) +  
					((domain == null) ? "" : ("; domain=" + domain)) +    
					((secure == true) ? "; secure" : "");
			}
		</script>
		<form class='publish' name='m2mform' target='_blank' action='$url' method='post'>
content;

	$formats = WikiConverterFactory::availableTargetFormats();
	if (count($formats) > 1) {
		$ret.= '$[Target Format]:'; 
		$ret.= "<select name='targetformat' class='inputbox' onChange='selectFormat()'>";
		foreach ($formats as $f=>$d) {		
			$ret .= "<option value='$f'";		
			if ($f == $_COOKIE['m2m-format'])
				$ret .= " selected='selected'";
			$ret .= ">$d</option>";
		}
		$ret .= "</select>\n";
	}
	else {
		$keys = array_keys($formats);
		$ret .= "<input type='hidden' name='targetformat' value='$keys[0]'/>";
	}
	$ret .= <<<content
		<input type='hidden' value='$pagename' name='pagename'>
		<input type='submit' value='$[Options]' name='m2m-options' class='inputbutton'/>
		<input type='submit' value='$[Publish Page]' name='publishpage' class='inputbutton'/>
content;
	$pagetype = pagetype($pagename);
	if ($pagetype == 'trail' || $pagetype == 'collection') {
		$typestr = strtoupper($pagetype{0}).substr($pagetype, 1);
		$ret.= " <input type='submit' value='$[Publish $typestr]' name='publish$pagetype' class='inputbutton'/> ";
	}
	$ret.= "</form>";
	return Keep(FmtPageName($ret, $pagename));
}

function pagetype ($pagename) {
	global $SuffixPattern, $GroupPattern, $WikiWordPattern, $LinkWikiWords;
	global $SpaceWikiWords, $SpaceWikiWordsFunction;
	$page = ReadPage($pagename, 'read');
	$page = trim($page['text']);
	$page = preg_replace('/\(:include\s+(.+?):\)/e', "readIncludedText('$pagename', '\\1')", $page);

	// check if page is a collection
	if (preg_match('/\n\(:collection:\)\s*\n/', $page))
		return 'collection';

	// check whether current page has a trail
	$trail_found = false;
	foreach(explode("\n", $page) as $line) {  // process text line by line
		if (preg_match("/^T?([#*:]+)\\s*(.*)/", $line, $m)) { // found a list item?
			$m[1] = str_replace(':#', ':', $m[1]);
			if ($LinkWikiWords) 
				$m[2] = preg_replace("/^($GroupPattern([\\/.]))?($WikiWordPattern)/e", 
											"'[[$1'.(($SpaceWikiWords)?$SpaceWikiWordsFunction('$3'):'$3').']]'", 
										   $m[2]);

			$m[2] = preg_replace("/\\[\\[([^\\]]*)->([^\\]]*)\\]\\]/",'[[$2|$1]]', $m[2]);
			if (preg_match("/^(\\[\\[([^|]*?)(\\|.*?)?\\]\\]($SuffixPattern))/", $m[2])) {
				return 'trail';
			} 
		}
	}
	return 'page';
}

function readIncludedText ($basepage, $includepage) {
	$page = ReadPage(MakePageName(trim($basepage), trim($includepage)));
	return $page['text'];
}


/**********************************************************/

function ReplaceMarkup ($id,$cmd,$pat=NULL,$rep=NULL) {
	global $MarkupTable;
	unset($MarkupTable[$id]['pat']);
	Markup($id, $cmd, $pat, $rep);
}


require_once "$M2MDir/WikiXMLConverter.class.php";
function HandleM2MPublish ($pagename, $auth='read') {  // @@ separate publish permissions
	session_start();
	$targetformat = $_SESSION['targetformat'] = $_REQUEST['targetformat'];
	$converter = WikiConverterFactory::createConverter($pagename, $targetformat);
	if (isset($_REQUEST['m2m-options'])) 
		HandleM2MOptions($pagename, $auth);
	else {
		global $M2MDir, $M2MUrl, $M2MDataDir;
		require "$M2MDir/wikixml_markup.inc.php";  // load WikiXML markup...
		setlocale(LC_NUMERIC, "C");                // force decimal dots for number->string conversions
		if (is_object($converter)) {
			global $LinkPageCreateFmt, $M2MDataUrl;
			echo "<html><head><title>media2mult</title></head><body>";
			$mode = 'page';
			if (isset($_POST['publishtrail']))
				$mode = 'trail';
			elseif (isset($_POST['publishcollection']))
				$mode = 'collection';

			$datadir = "$M2MDataDir/$pagename";
			$options = new StylesheetOptions("$M2MDir/options/$targetformat.xml");
			if (file_exists("$datadir/{$targetformat}opt.xml"))
				$options->readXML("$datadir/{$targetformat}opt.xml");
			$options->setValue("processing.date", date('j.n.Y'));
			$options->setValue("processing.mode", $mode);
			$options->writeStylesheet("$datadir/$targetformat.xsl", "$M2MDir/xsl/{$targetformat}.xsl");

			$result = $converter->convert($mode);
			if ($result) {
				global $SingleResultOnly, $M2MUrl, $M2MDataUrl;
				$filetype = strtoupper($converter->getFileExtension());
				echo "<h2><font color='green'>conversion was successful</font></h2>";
				if ($SingleResultOnly)
					echo "<a href='$M2MDataUrl/$pagename/".basename($result)."'>download $filetype-document here</a>";
				else {
					echo "<img src='$M2MUrl/images/pdf.png' valign='middle'><a href='$M2MDataUrl/$pagename/".basename($result)."'>$filetype file</a><br>";
					echo "<img src='$M2MUrl/images/xml.png' valign='middle'><a href='$M2MDataUrl/$pagename/source.xml'>XML file</a>"; 
				}
			}
			else
				echo "<h2><font color='red'>error occured during conversion</font></h2>";
			echo "</body></html>";
		}
		exit;
	}
}

require_once 'OptionPage.class.php';
require_once 'StylesheetOptions.class.php';
function HandleM2MOptions ($pagename, $auth='read') {
	global $M2MDataDir, $M2MDir, $M2MUrl;
	$formats = WikiConverterFactory::availableTargetFormats();
	$targetformat = $_REQUEST['targetformat'];

	// read default options
	$options = new StylesheetOptions();
	$options->readXML("$M2MDir/options/$targetformat.xml");
	$datadir = "$M2MDataDir/$pagename";
	if (file_exists("$datadir/{$targetformat}opt.xml"))
		$options->readXML("$datadir/{$targetformat}opt.xml");

	print "<html><head><title>media2mult options</title>";
	print "<link rel='stylesheet' type='text/css' href='$M2MUrl/options/options.css'>";
	print "<body>";
	OptionPage::showForm($options, $formats[$targetformat], "$_SERVER[PHP_SELF]?n=$pagename&action=m2m-saveoptions", 3);
	print "</body></html>";
}


function HandleM2MSaveOptions ($pagename, $auth='read') {
	global $M2MDir, $M2MDataDir;
	session_start();
	$targetformat = $_SESSION['targetformat'];
	$options = new StylesheetOptions();
	$options->readXML("$M2MDir/options/$targetformat.xml");
	$options->setRequestValues($_REQUEST);
	$outdir = "$M2MDataDir/$pagename";
	recursive_mkdir($outdir);
	$options->writeShortXML("$outdir/{$targetformat}opt.xml");
	$options->writeStylesheet("$outdir/$targetformat.xsl", "$M2MDir/xsl/{$targetformat}.xsl");
	HandleBrowse($pagename, $auth);
}


function HandleM2MShowFile ($pagename, $auth='read') {
	$file = $_REQUEST['f'];
	$path = getUploadPath($pagename, $file);
	if (file_exists($path)) {
		print "<html><head><title>Content of file $file</title></head><body><pre>";
		$f = fopen($path, 'r');
		while (!feof($f)) 
			print str_replace('<', '&lt;', fgets($f));
		fclose($f);
		print "</pre></body></html>";
	}
}


function getUploadURL ($pagename, $path) {
	global $UploadFileFmt, $UploadPrefixFmt, $UploadUrlFmt, 
		    $EnableDirectDownload;
	if (preg_match('!^(.*)/([^/]+)$!', $path, $m)) {
   	$pagename = MakePageName($pagename, $m[1]);
    	$path = $m[2];
	}
   $upname = MakeUploadName($pagename, $path);
   $filepath = FmtPageName("$UploadFileFmt/$upname", $pagename);
  	$url = PUE(FmtPageName(IsEnabled($EnableDirectDownload, 1) 
  			                      ? "$UploadUrlFmt$UploadPrefixFmt/$upname"
  										 : "{\$PageUrl}?action=download&amp;upname=$upname",
      	                  	 $pagename));
	return $url;
}


function getUploadPath ($pagename, $path) {
	global $UploadFileFmt;
	if (preg_match('!^(.*)/([^/]+)$!', $path, $m)) {
   	$pagename = MakePageName($pagename, $m[1]);
    	$path = $m[2];
	}
   $upname = MakeUploadName($pagename, $path);
   $filepath = FmtPageName("$UploadFileFmt/$upname", $pagename);
	return $filepath;
}


function HandleCheckM2MConfig ($pagename, $auth='read') {
	CheckTools();
}

/////////////////////////////////////////////////////
?>
