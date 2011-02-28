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


require_once 'Attributes.class.php';
require_once 'debugfuncs.php';

function RegisterTool ($name, $path, $param='', $env=false) {
	global $CmdLineTool;
/*	if (!is_file($path) && !is_file("$path.exe"))
		return false; */
	$CmdLineTool[$name] = array(
		'path'  => $path,
		'param' => $param
	);
	if ($env !== false)
		$CmdLineTool[$name]['env'] = $env;
	return true;
}


function RunTool ($name, $params, $method='passthru', $extra=false) {
	global $CmdLineTool;
	if (!array_key_exists($name, $CmdLineTool)) 
		return false;
	$tool = &$CmdLineTool[$name];

	// set environment variables
	if (isset($tool['env'])) {
		$envvars = new Attributes($tool['env']);
		$envvars = $envvars->getAttribs();
		foreach ($envars as $var=>$val)
			putenv("$var=$val");
	}
	// get parameters
	$params = new Attributes($params);
	$varpattern = '(?<!\\\\)\$(\w+)';
	$p = preg_replace("/$varpattern/e", "(\$a=\$params->getAttrib('$1'))===false ? '$$1' : \$a", $tool['param']);
	$p = preg_replace("/\\{[^}]*?{$varpattern}[^}]*\\}/", '', $p); // remove {...$var...}
	$p = preg_replace("/\{([^}]*)\}/", '$1', $p);                  // remove braces from {...}
	$p = preg_replace("/$varpattern/", '', $p);                    // remove undefined $var
	$p = preg_replace("/\s+/", ' ', $p);
	$params = trim($p);
	//SHOW("$tool[path] $params");
	switch ($method) {
		case 'pipe': {
			$stdin = $extra;
			$ret = pipe_execute("$tool[path] $params", $stdin);
			break;
		}
		case 'pipe-callback': {
			$p = popen("$tool[path] $params", "r");
			if ($p) {
				while (!feof($p)) {
					$line = trim(fgets($p));
					call_user_func($extra, $line);
				}
				$ret = pclose($p);
			}
			else
				$ret = false;
			break;
		}
		case 'exec': {
			exec("$tool[path] $params", $ret);
			break;
		}
		default:
			passthru("$tool[path] $params");
	}

	// unset environmet variables
	if (is_array($envvars)) 
		foreach ($envvars as $var=>$val)
			putenv("$var="); 
	return $ret;
}


function CheckTools () {
	global $CmdLineTool;
	print "<h1>media2mult Tool Check</h1>";
	print "<p>The conversion component of media2mult uses various command line tools that must be properly installed on your system.<br>\n";
	print "The following table lists these tools and shows their accessability. If some of them can't be found, please\n";	
	print "install the missing tools and/or adapt the configuration file <i>m2m-config.php</i></p>";
	print "<table border='0' cellpadding='4'>\n";
	print "<tr><th bgcolor='#cccccc'>Tool Name</th><th bgcolor='#cccccc'>Command</th><th bgcolor='#cccccc'>Status</th></tr>\n";
	foreach ($CmdLineTool as $name=>$tool) {
		print "<tr><td bgcolor='#eeeeee'>$name</td>";
		$output = "";
		exec("which $tool[path]", $output, $retval);
		$name = basename($tool['path']);
		$path = dirname($output[0]);
		if ($output[0] != '' && dirname($tool['path']) != $path)
			$name = "<font color='#0000ff'>$path/</font>$name";
		else
			$name = $tool['path'];
		print "<td bgcolor='#eeeeee'>$name</td>";
		$status = ($retval != 0) ? "<font color='red'>not found</font>" : "<font color='green'>found</font>";
		print "<td align='center' bgcolor='#eeeeee'>$status</td></tr>\n";
	}
	print "</table>\n";
}

?>
