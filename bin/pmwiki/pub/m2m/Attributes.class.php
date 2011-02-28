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


class Attributes 
{
	var $attribs;
	var $prefixsubst;
	
	function Attributes ($attrstr, $prefixsubst='') {
		// converts a string like "foo1=bar1 foo2="bar2 baz2" foo3=bar3"  
   	// to an array('foo1'=>'bar1', 'foo2'=>'bar2 baz2', 'foo3'=>'bar3')
		$this->attribs = array();
		$this->prefixsubst = is_array($prefixsubst) ? $prefixsubst : array();
		$attrstr = trim($attrstr);
		if ($attrstr != '') {
			$attrstr = str_replace('\\"', '"', $attrstr);
			while (preg_match('/^((?:\w|-)+)\s*=\s*(\\\\?["\']?)(.*?)\2(\s+(.*))?$/s', $attrstr, $m)) {		
				if ($m[1]) {
					if (preg_match('/^(.+?)-(.*)$/', $m[1], $mm) && isset($prefixsubst[$mm[1]])) 
						$m[1] = $prefixsubst[$mm[1]]."-".$mm[2];
					$this->attribs[$m[1]] = $m[3];
				}
				$attrstr = count($m) > 4 ? $m[5] : '';
			}
		}
	}


	function getAttrib ($attrkey, $prefix='', $exact=false) {
		if (isset($this->prefixsubst[$prefix]))
			$prefix = $this->prefixsubst[$prefix];

		if (isset($this->attribs["$prefix-$attrkey"]))
			$attr = $this->attribs["$prefix-$attrkey"];
		elseif (!$exact && isset($this->attribs[$attrkey]))
			$attr = $this->attribs[$attrkey]; 
		else
			return false;
		return $attr;
	}

	
	function setAttrib ($key, $prefix, $value) {
		if ($prefix == '')
			$this->attribs[$key] = $value;
		else
			$this->attribs["$prefix-$key"] = $value;
	}
	
	
	function getAttribs ($prefix='', $exact=false) {
		if (isset($this->prefixsubst[$prefix]))
			$prefix = $this->prefixsubst[$prefix];
		
		$ret = array();
		foreach ($this->attribs as $key=>$val) {
			if (preg_match("/^$prefix-(.*?)$/", $key, $m))
				$ret[$m[1]] = $val;
			elseif (!$exact && strpos($key, '-') === false)
				$ret[$key] = $val;
		}
		return $ret;
	}

	
	function toString ($prefix='') {
		$attr = $this->getAttribs($prefix);
		$ret = '';
		foreach ($attr as $key=>$val) 
			$ret="$key=\"$val\" ";
		return trim($ret);
	}

	
	// remove prefixes from attributes, use $prefix in case of ambiguities
	function stripPrefixes ($prefix) {
		$this->attribs = $this->getAttribs($prefix);
	}
}

?>
