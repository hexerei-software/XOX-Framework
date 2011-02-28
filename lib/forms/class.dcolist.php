<?php
	/*****************************************************************
	 *****************************************************************

	 Helper class to generate automatic lists from collections
	 of instances of cdbobjectbase objects (DCO Data Container Object)

	 @file xox/lib/forms/class.dcolist.php

	 @created 25.08.2006 17:07
	 @version 16.08.2009 13:31

	 @see xox/lib/database/class.cdbobject.php
	 @see xox/lib/forms/class.dcoform.php

	 @author dvorhauer

	 *****************************************************************
	 *****************************************************************

	 XOX PHP Library 2.0
	 (c) 1997-2006 hexerei software creations

	 This library is not yet free software. If you are a member of the
	 hexerei software development team, you have a non-exclusive right
	 to use this library for projects that are either for the hexerei
	 or one of it's customers under the label of hexerei. You may also
	 use the library for your own purposes if hexerei has granted you
	 a license to do so.

	 If you have received a copy of this source without explicit right
	 or licence from hexerei, then you may not modify it or reuse it
	 in any form without prior notice to hexerei. Most likely you have
	 received a copy along with an implemented application from the
	 hexerei or one of its licensees and herby have no right to reuse,
	 modify or publish this code under any terms other than the rights
	 you have acquired for the given application.

	 This library is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
	 MERCHANTABILITY of FITNESS FOR A PARTICULAR PURPOSE.

	 Under no circumstances may you remove this header and or any
	 copyright notice which marks hexerei software creations as the
	 owner and author of this library and its sources.

	 Daniel Vorhauer
	 daniel@hexerei.net

	*/

	require_once(XOX_LIB.'/forms/class.dcodata.php');
	require_once(XOX_LIB.'/template/class.compound_template.php');

	/*****************************************************************
	*****************************************************************/
	class dcoColumn extends dcoData
	{
		/** width of the column - 0:autosize */
		var $width;
		var $fn_render;

		/**
		 * constructs a single column used for the rows in dco list
		 * @param name the name of the column
		 * @param type one of the predefined dco column types
		 * @param width set 0 to autosize
		 * @param title column title for international lists
		 */
		function dcoColumn($name,$type=DCO_DATA_TEXT,$width=0,$title='',$fn_render='',$encoding='') {
			parent::dcoData($name,$type,false,$title);
			$this->width 		= $width;
			$this->fn_render	= $fn_render;
			$this->charset_encoding = $encoding;
		}

		function get() {
			$value = parent::get();
			if (!empty($this->fn_render)) {
				call_user_func_array($this->fn_render,array(&$value));
			}
			return $value;
		}
	}



	/*****************************************************************
	 This helper class can be used to generate automatic paging
	 lists using a collection of instances of cdbobjectbase objects
	*****************************************************************/
	class dcoList
	{
		var $dco = 0;
		var $conf = array();

		var $linkurl;
		var $linktarget;
		var $linktooltip;

		var $paging = false;
		var $num_rows = 0;
		
		var $charset_encoding = '';
		var $allow_wrap = false;

		// constructor
		function dcoList($conf='') {
			$this->reset();
			if (is_array($conf)) {
				$this->conf = array_merge($this->conf,$conf);
				if (isset($conf['dco'])) $this->setDCO($conf['dco']);
				if (isset($conf['paging'])) $this->paging = $conf['paging'];
				if (isset($conf['charset_encoding'])) $this->charset_encoding = $conf['charset_encoding'];
				if (isset($conf['allow_wrap'])) $this->allow_wrap = $conf['allow_wrap'];
			}
		}

		// set collection of data container objects
		function setDCO($dco) {
			$this->dco = is_object($dco) ? $dco : 0;
			return is_object($this->dco);
		}

		// add display column for table
		function addColumn($name,$type=DCO_DATA_TEXT,$width=0,$title='',$fn_render='') {
			if ( !isset($this->conf['columns']) || !is_array($this->conf['columns']) )
				$this->conf['columns'] = array();
			$key = (preg_match('/^[\^\*\+\-]/',$name)) ? substr($name,1) : $name;
			$this->conf['columns'][$key] = new dcoColumn($name,$type,$width,$title,$fn_render,$this->charset_encoding);
		}

		// add result from query to data
		function query($sql) {
			$this->num_rows = countQuery($sql);
			$data = array();
			if ($this->paging) {
				$GLOBALS['num_results'] = $this->num_rows;
				require(XOX_LIB.'/database/result_navigation.php');
				$sql.=$GLOBALS['result_navigation'];
			}
			if ($rs = executeQuery($sql)) {
				while ($row = $rs->getrow()) {
					$data[] = $row;
				}
				$rs->free();
			}
			$this->setData($data);
		}

		// show the list
		function show($return_string=false) {

			// make sure we have a columns array
			if ( !isset($this->conf['columns']) || !is_array($this->conf['columns']) )
				$this->conf['columns'] = array();

			if ( !isset($this->conf['template']) ) $this->conf['template'] = '';
			if ( !isset($this->conf['rowtemplate']) ) $this->conf['rowtemplate'] = '';

			// get compound template class
			$_list = new xoxCompoundTemplate($this->conf['template'],$this->conf['rowtemplate'],'');

			// build default master template if none is set
			if (empty($this->conf['template'])) {
				$_master = '<style type="text/css">
				td { background-color: #fff; }
				td.odd { background-color: #eee; }
				td a { text-decoration:none; display:block; }
				td a:hover { background-color: #ffe; }
				</style><table>';
				if (count($this->conf['columns'])>0) {
					$_master.= '<tr>';
					foreach($this->conf['columns'] as $name=>$column) {
						if (!preg_match('/^\^/',$column->name)) {
							$_caption = empty($column->title) ? $name : $column->title;
							$_width = ($column->width>0) ? ' width="'.$column->width.'"' : '';
							$_master.= '<th'.$_width.'>'.$_caption.'</th>';
							$_list->setVar('ls_'.$name,$_caption,0);
						}
					}
					$_master.= '</tr>';
				}
				$_master.= '%SUB0001%</table>';
				$_list->templates[0]->parsed_template = $_master;
			}

			// build default sub template if none is set
			if (empty($this->conf['rowtemplate'])) {
				if (count($this->conf['columns'])>0) {
					$_sub = '';
					$_link = '';
					$_unlink = '';
					foreach($this->conf['columns'] as $name=>$column) {
						if (preg_match('/^\^/',$column->name)) {
							$_link = '<a href="'.$this->linkurl.$name.'=%'.$name.'%" target="'.$this->linktarget.'" title="'.$this->linktooltip.'">';
							$_unlink = '</a>';
						} else {
							$_align = ($column->type==DCO_DATA_INTEGER) ? ' align="right"' : '';
						$_sub.= '<td%_odd_class%'.$_align.'>'.$_link.'%'.$name.'%'.$_unlink.'</td>';
						}
					}
					$_sub = $_link.'<tr>'.$_sub.'</tr>'.$_unlink;
				}
				$_list->templates[1]->parsed_template = $_sub;
			}

			$_i = 0;
			foreach($this->conf['data'] as $rowid=>$row) {
				$_list->setVar('_odd_class',(($_i++%2==0) ? ' class="odd"' : ''),1,$rowid);
				foreach($row as $key=>$value) {
					if ( isset($this->conf['columns'][$key]) ) {
						$this->conf['columns'][$key]->set($value);
						#echo "Setting $key for row $rowid to ".$this->conf['columns'][$key]->get()."<br />";
						$sval = $this->conf['columns'][$key]->get();
						if ($this->allow_wrap) $sval = str_replace('<br>','<br />',$sval);
						else  $sval = str_replace('<br','<span',$sval);
						$_list->setVar($key,$sval,1,$rowid);
					}
				}
			}

			if ( $return_string ) {
				$return_string = '';
				if ($this->paging) $return_string.=showResultNavigation().'<br />';
				$return_string.=$_list->show(true);
				if ($this->paging) $return_string.='<br />'.showResultNavigation();
				return $return_string;
			} else {
				if ($this->paging) echo showResultNavigation().'<br />';
				$_list->show();
				if ($this->paging) echo '<br />'.showResultNavigation();
			}

			return true;
		}

		// check if data was passed and add it to our data
		function setData($data) {
			if ($data!='') {
				if ( is_array($this->conf['data']) ) {
					if ( is_array($data) ) {
						$this->conf['data'] = array_merge($this->conf['data'],$data);
					} else if (isset($GLOBALS[$data])) {
						$this->conf['data'][$data] = $GLOBALS[$data];
					}
				} else {
					$this->conf['data'] = $data;
				}
			}
		}

		// set configuration defaults
		function reset() {
			$this->dco = '';
			$this->conf = array(
				'template'	=> '',
				'rowtempl'	=> '',
				'width' 		=> 0,
				'columns'		=> array(),
				'data' 			=> array(),
			);
			$this->linkurl = '';
			$this->linktarget = '';
			$this->linktooltip = '';
		}

	}	// finish class dcoList

?>
