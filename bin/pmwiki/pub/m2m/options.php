<?php 
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

if (!isset($M2MDir))
	$M2MDir = dirname(__FILE__);
elseif (!defined('media2mult')) 
	exit;
	
require_once "$M2MDir/debugfuncs.php";
require_once "$M2MDir/functions.php";
require_once "$M2MDir/StylesheetOptions.class.php";
require_once "$M2MDir/Converter.class.php";
?>
<html>
<head>
<title>media2mult-Options</title>
<link rel="stylesheet" type="text/css" href="<?=$M2MUrl?>/options.css">
</head>
<body background="<?="$M2MUrl/images/uosbg.jpg"?>">

<?
if ($_POST['submit'] == "OK") {
	$format  = $_POST['targetformat'];
	$datadir = $_POST['datadir'];
	$error = false;
	if ($format == "")	
		$format = "html";
	if ($_FILES['html_stylesheet']['name'] != '' && strrchr($_FILES['html_stylesheet']['name'], '.') == '.css') { // dots are automatically replaced by underscores
		move_uploaded_file($_FILES['html_stylesheet']['tmp_name'], $datadir."/".$_FILES['html_stylesheet']['name']);
		chmod($_FILES['html_stylesheet']['name'], 0644);
	}
	elseif(trim($_FILES['html_stylesheet']['name']) != '' && file_extension($_FILES['html_stylesheet']['name']) != 'css'){
		echo("<h2>Please choose an appropriate stylesheet with '.css' extension!</h2>");
		$error = true;
	}
	$opt = $_POST;
	foreach (array_keys($_FILES) as $key)
		$opt[$key] = $_FILES[$key]['name'];
	saveOptions($opt, $format, $datadir);
	
	if(!$error)
		echo("<h2>Options have successfully been updated!</h2>");
	echo("<a href='javascript:self.close()'>Close this page.</a>");
} 
elseif($_POST['reload'] == "Default laden"){	
	showOptionsForm($datadir, $_POST['targetformat'], 3, '', true);
	die();
}


function getOptions ($format, $datadir) {															
	global $M2MDir;
	
	$format = "html";											//TODO: implement Optionsformat in Method call
	$useroptxml = "$datadir/{$format}opt.xml";
	if (!file_exists($useroptxml) || (filemtime("$M2MDir/{$format}opt.xml") > filemtime($useroptxml)))
		StylesheetOptions::copyXML("$M2MDir/{$targetFormat}opt.xml", $useroptxml);
	$options = new StylesheetOptions($useroptxml);
//	$options->readXML($optfile);
	return $options;
}


function saveOptions ($optarray, $format, $outputdir) {
	global $M2MDir;
	$optfile = "$outputdir/{$format}opt.xml";
	$sso = new StylesheetOptions();
	if(file_exists($optfile))
		$sso->readXML($optfile);
	else 
		$sso->readXML("$M2MDir/{$format}opt.xml"); 
	$options =& $sso->getOptions();
	foreach ($options as $o) {
		$value = $optarray[strtr($o['name'], ".", "_")]; 
		if ($o['type'] == 'file' && $o['value'] != '' && $value == '') {
			continue;
		}

		if ($o['type'] == 'bool')          // unchecked checkboxes are not submitted by POST
			$value = $value == "" ? 0 : 1;  // only checked checkboxes submit a (former) value "false"
		$sso->setValue($o['name'], $value);
	}
	$sso->writeXML($optfile);
}


function showOptionsForm ($datadir, $targetFormat, $columns, $dir, $default = false) {
	global $M2MDir, $M2MUrl;
	$formats = ConverterFactory::availableTargetFormats();
	$options = new StylesheetOptions();
	$useroptxml = "{$targetFormat}opt.xml";
	if ($default || !file_exists("$datadir/$useroptxml") || (filemtime("$M2MDir/$useroptxml") > filemtime("$datadir/$useroptxml")))
		StylesheetOptions::copyXML("$M2MDir/{$targetFormat}opt.xml", "$datadir/$useroptxml");
	  	
	$options->readXML("$datadir/$useroptxml");
	$categories = $options->getCategories();
?>
	<script language='JavaScript'>
		function expand (id, val) {
			var catElem = document.getElementById('cat'+id);
			var bodyElem = document.getElementById('catbody'+id);
			if (catElem && bodyElem) {
				if (val == 'open') {
					catElem.className = 'open';
					bodyElem.style.display = '';
				}
				else if (val == 'close') {
					catElem.className = 'close';
					bodyElem.style.display = 'none';
				}
				else {
					catElem.className = (catElem.className == 'open' ? 'close' : 'open');
					bodyElem.style.display = (bodyElem.style.display == 'none' ? '' : 'none');
				}
			}
		}
		
		function expandAll (open) {
			var i;
			for (i=0; i < <?=count($categories)?>; i++)
				expand(i, open ? 'open' : 'close');
		}
	</script>
	<table class='bgtable' cellpadding="10" width="95%" align="center"><tr><td>
	<form action='<?=$M2MUrl?>/options.php' method='post' enctype='multipart/form-data'>
	<table class='cattable' cellpadding="5" width="100%">
	<tr bgcolor='#555555'>
	<td colspan='<?=$columns?>'>
	<div class='title'>Optionen für Zielformat <?=$formats[$targetFormat]?></div>
	<div class='openclose'>
		<img src='<?=$M2MUrl?>/images/plus.gif' onClick="expandAll(true)">
		<img src='<?=$M2MUrl?>/images/minus.gif' onClick="expandAll(false)">
	</div>
	</td></tr>
<?
	$catcount = 0;
	foreach ($categories as $c) {
		$col = 0;
		$optclass = 'opt1';
		if ($c['title'] == 'hidden')
	 		foreach ($c['options'] as $o)
				echo "<input type='hidden' name='$o[name]' value='$o[value]'>\n";	
		else {
			echo "<tr class='catrow'><td colspan='$columns'>";
			echo "<a id='cat$catcount' class='open' onClick='expand($catcount)'>$c[title]</a></td></tr>\n";
			echo "<tbody id='catbody$catcount'>\n";
			foreach ($c['options'] as $o) {
				if ($col == 0)
					echo "<tr class='$optclass'>";
				echo "<td>"; 
				echo getHtmlInputElement($o['name'], $o['type'], $o['description'], $o['value']) . "</td>\n";
				if ($col++ >= $columns-1) {
					$col = 0;
					$optclass = ($optclass == 'opt1' ? 'opt2' : 'opt1');
					echo "</tr>";					
				}
			}
			for ($col %= $columns; $col > 0; $col = ($col+1) % $columns)
				echo "<td class='$optclass'></td>";
			$catcount++;
			echo "</tbody>\n";
		}
	}
?>
	</table></td></tr><tr><td>
	<input type="hidden" name="targetformat" value="<?=$targetFormat?>">
	<input type="hidden" name="datadir" value="<?=$datadir?>">
	<input type="submit" name="submit" value="OK" class="button">
	<input type="reset" name="submit" value="Zur&uuml;cksetzen" class="button">
	<input type="button" value="Abbrechen" class="button" onClick="window.close()">
	</td></tr>
	</form></table>
<?
}


function getHtmlInputElement ($name, $type, $descr, $value) {
	$tag = ""; //"<input type='hidden' name='@$name' value='$value'>\n";
	$descr = umlauts($descr);
	switch (preg_replace('/^(\w+).*$/', '\1', $type)) {
		case 'bool':
			$tag .= "<input type='checkbox' name='$name' value='$value' class='checkbox'";		
			if ($value == 1)
				$tag .= ' checked="checked"';
			$tag .= "> $descr";
			break;
      case 'float':
         $tag .= "$descr: <input type='text' size='5' maxlen='6' name='$name' value='$value' class='textfield'";
         break;
		case 'file':
			$tag .= "$descr: <input type='file' name='$name' value='$value' class='file'>";
			break;
		case 'string':
			$tag .= "$descr: <input type='text' size=20' maxlength='20' name='$name' value='$value' class='textfield' align='middle'>";	
			break;
		case 'enum':
			$type = preg_replace('/enum\s*\((.*?)\)/', '\1', $type);
			$items = explode(',', $type);
			$tag .= "$descr: <select name='$name' class='dropdown'>";
			foreach ($items as $item) {
				$item = trim($item);
				$selected = ($item == $value) ? 'selected' : '';
				$tag .= "<option value='$item' $selected>$item</option>";
			}
			$tag .= '</select>';
			break;
		default:
			$tag .= "$descr: <input type='text' size='2' maxlength='2' name='$name' value='$value' class='textfield'>";	
	}
	return $tag;
}

?>
