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

require_once "functions.php";
require_once "StylesheetOptions.class.php";

class OptionPage 
{
	function showForm (&$options, $formatdescr, $targeturl, $columns) {
		global $M2MDir, $M2MUrl;
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
	<form action='<?=$targeturl?>' method='post' enctype='multipart/form-data'>
	<table class='cattable' cellpadding="5" width="100%">
	<tr bgcolor='#555555'>
	<td colspan='<?=$columns?>'>
	<div class='title'>Optionen f&uuml;r Zielformat <?=$formatdescr?></div>
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
					if (is_array($o['value'])) {
						echo htmlentities($o['description']).":<br>";
						foreach ($o['value'] as $k=>$v)
							echo OptionPage::getHtmlInputElement("$o[name][$k]", $v['type'], "$o[indexdescr] $k", $v['value']) . "<br>\n";
					}
					else
						echo OptionPage::getHtmlInputElement($o['name'], $o['type'], $o['description'], $o['value']) . "</td>\n";
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
		$pre_descr = $descr != '' ? "$descr: " : '';
		switch (preg_replace('/^(\w+).*$/', '\1', $type)) {
			case 'bool':
				$tag .= "<input type='checkbox' name='$name' value='$value' class='checkbox'";		
				if ($value == 1)
					$tag .= ' checked="checked"';
				$tag .= "> $descr";
				break;
			case 'float':
				$tag .= "$pre_descr<input type='text' size='5' maxlen='6' name='$name' value='$value' class='textfield'>";
				break;
			case 'file':
				$tag .= "$pre_descr<input type='file' name='$name' value='$value' class='file'>";
				break;
			case 'length':
				$tag .= "$pre_descr<input type='text' size='5' maxlen='10' name='$name' value='$value' class='textfield'>";
				break;
			case 'string':
				$tag .= "$pre_descr<input type='text' size=20' maxlength='20' name='$name' value='$value' class='textfield' align='middle'>";	
				break;
			case 'array':
				preg_match('/array\[(.+?):(.+?)\]/', $type, $m);
//				OptionPage::$arrayLabel = $m[1];
//				OptionPage::$arrayIndex = $m[2];
				$tag .= "$pre_descr<br>";
				break;
			case 'enum':
				$type = preg_replace('/enum\s*\((.*?)\)/', '\1', $type);
				$items = explode(',', $type);
				$tag .= "$pre_descr<select name='$name' class='dropdown'>";
				foreach ($items as $item) {
					$item = trim($item);
					$selected = ($item == $value) ? 'selected' : '';
					$tag .= "<option value='$item' $selected>$item</option>";
				}
				$tag .= '</select>';
				break;
			default:
				$tag .= "$pre_descr<input type='text' size='3' maxlength='5' name='$name' value='$value' class='textfield'>";	
		}
		return $tag;
	}
}

?>
