<html>
<head>
</head>
<body>
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


$FieldDir = '/www/wikifarm/fields';

include 'debugfuncs.php';

if (!isset($_POST['submit'])) {
	echo "<form action='<?=$_SERVER[PHP_SELF]?>' method='post'>";
	echo "Wiki-Field: ".makeSelectBox(gatherFields($FieldDir))."<br>";
	echo "<input type='submit' value='OK'>";
	echo "</form>";
	exit;
}
SHOW($_POST);

?>
</body>
</html>

<?
function gatherFields ($dir) {
	$d = @opendir($dir);
	if ($d === false)
		return false;

	while (($f = readdir($d)) !== false) {
		if ($f{0} != '.' && is_dir("$dir/$f"))
			$fields[] = $f;
	}
	closedir($d);
	sort($fields);
	return $fields;
}

function makeSelectBox ($entries) {
	if (!is_array($entries) || count($entries) == 0)
		return '';
	$ret = "<select>\n";
	foreach ($entries as $e) {
		$ret .= "<option value='$e'>$e</option>\n";
	}
	$ret .= "</select>\n";
	return $ret;
}

?>
