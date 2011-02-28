<?php

define('XOX_NLS_MODE_NL_LIST', 0);
define('XOX_NLS_MODE_NL_DETAIL', 1);
define('XOX_NLS_MODE_NL_SETTINGS', 2);
define('XOX_NLS_MODE_NL_LAYOUTS', 3);
define('XOX_NLS_MODE_NL_IMAGES', 4);
define('XOX_NLS_MODE_NL_CONTENT', 5);
define('XOX_NLS_MODE_NL_TEST', 6);
define('XOX_NLS_MODE_NL_READERS', 7);
define('XOX_NLS_MODE_NL_SEND', 8);
define('XOX_NLS_MODE_NL_STATS', 9);
define('XOX_NLS_MODE_NL_OVERVIEW', 10);

define('XOX_NLS_BTN_EDIT', 1);
define('XOX_NLS_BTN_COPY', 2);
define('XOX_NLS_BTN_DELETE', 4);
define('XOX_NLS_BTN_RELOAD', 8);
define('XOX_NLS_BTN_SAVE', 16);
define('XOX_NLS_BTN_BLOCK', 32);
define('XOX_NLS_BTN_BACK', 64);
define('XOX_NLS_BTN_PREVIEW', 128);
define('XOX_NLS_BTN_EMAIL', 256);

define('XOX_NLS_MAX_STRLEN',32);

define('XOX_NLS_ROW_COLOR','#f1e7ff'); // #fffddd

require_once(XOX_LIB.'/html/class.html2text.php');

function prepDisplayString($str='') {
	if ( preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/',$str,$amatch) ) {
		$str = "$amatch[3].$amatch[2].$amatch[1]";
	} else {
		if( strpos($str,'<') !== FALSE ) {
			$htt = new Html2Text($str,XOX_NLS_MAX_STRLEN);
			$str = $htt->convert();
		}
		if ( strlen($str)>XOX_NLS_MAX_STRLEN ) {
			$str = substr($str,0,XOX_NLS_MAX_STRLEN-3).'...';
		}
	}
	return $str;
}

/*
function drawList($type,$help,$fields=0,$entries=0,$btns=0,$actions=0) {

	if ($actions == 0)$actions = array('new'=>'#','edit'=>'#','detail'=>'#','delete'=>'#','copy'=>'#','save'=>'#');
	$num_entries = is_array($entries)?count($entries):0;

	$list = drawListHeader($type,$help,$num_entries,$actions);
	$list.= drawListEntries($fields,$entries,$btns,$actions);

	return $list;
}*/
function getResultList($sql) {
	//echo $sql."<br />";
	if (empty($GLOBALS['num_results'])) $GLOBALS['num_results'] = countQuery($sql);
	require_once (XOX_LIB.'/database/result_navigation.php');

	$add = array ();
	if ($GLOBALS['num_results'] && ($rs = executeQuery($sql.$GLOBALS['result_navigation']))) {
		while ($row = $rs->getrow()) {
			$add[] = $row;
		}
		return $add;

	}
}
function drawList($data,$showHeader=true) {
	$show_pagenav = FALSE;
	//mydump($data, "data");
	if (!is_array($data['data'])) {
		$data['data'] = getResultList($data['data']);
		$show_pagenav = TRUE;
	}
	//mydump($data, "data");

	if ($data['link'] == 0)
		$data['link'] = array ('new' => '#', 'edit' => '#', 'detail' => '#', 'delete' => '#', 'copy' => '#', 'save' => '#', 'back' => '#');
	$num_entries = $GLOBALS['num_results']; // is_array($entries)?count($entries):0;

	$list = '';
	$sortdata = empty($data['sort']) ? '' : $data['sort'];
	if($sortdata){$list .= '<form name="sort" method="POST" action="'.$data['link']['save'].'"><input type="hidden" name="p" value="" />';}
	if ($showHeader) $list .= drawListHeader($data['headline'], $data['body'], $num_entries, $data['link']);
	$list .= drawListEntries($data['tablehead'], $data['data'], $data['buttons'], $data['link'],$sortdata);
	if($sortdata){$list .= '</form>';}

	if ($show_pagenav)
		$list .= showResultNavigation();

	return $list;
}
function drawListHeader($type, $help, $num_entries = 0, $actions = 0, $cap_style='acaption', $box_style='bbox') {

	$head = '';
	if ( $type ) $head .= "<div class=\"$cap_style\"> $type </div>\n";
	$head .= "<div class=\"$box_style\">\n";

	if (is_array($help)) {
		// header
		$head .= "  <p>$help[0]</p>\n";
		if (count($help) > 1) {
			$head .= '  <table width="100%" border="0">';
			$head .= "	<tr><td><strong>Anzahl $type: $num_entries Eintr". ($num_entries == 1 ? 'ag' : 'äge').'</strong></td><td>&nbsp;</td></tr>';
			$head .= "	<tr><td align=\"left\"><strong>$help[2]</strong>&nbsp;&nbsp;<form name=\"$help[1]\" ><input type=\"text\" name=\"$help[1]\" width=\"40\" align=\"left\" align=\"absmiddle\"/>&nbsp;<a href=\"#\" title=\"Suchen\" onclick=\"if(document.location.href.indexOf('&search=')>0){document.location.href=document.location.href.replace(/search=.+/, 'search='+document.forms['$help[1]'].$help[1].value);}else{document.location.href=document.location.href + '&search='+document.forms['$help[1]'].$help[1].value} ; \"><img src=\"images/btn_search.gif\" alt=\"Suchen\" align=\"absmiddle\" /></a></form></td>\n";
			if (!empty($actions['new'])) $head .= "		<td align=\"right\"><strong>Neuer Eintrag</strong> <a href=\"".$actions['new']."\" title=\"Neuen $type Eintrag erstellen\"><img src=\"images/btn_new.gif\" alt=\"Neuen $type Eintrag erstellen\" align=\"absmiddle\" /></a></td></tr></table>\n";
			$head .= "	</tr></table>\n";
		} else {
			$head .= "  <table width=\"100%\"><tr><td><strong>Anzahl $type: $num_entries Eintr". ($num_entries == 1 ? 'ag' : 'äge')."</strong></td><td align=\"right\"><strong>Neuer Eintrag</strong> <a href=\"".$actions['new']."\" title=\"Neuen Eintrag\"><img src=\"images/btn_new.gif\" alt=\"Neuen Eintrag\" align=\"absmiddle\" /></a></td></tr></table>\n";
		}

	} else {
		// header
		if ($help) $head .= "  <p>$help</p>\n";
		if ($num_entries < 0) {
			$head .= "  <div align=\"right\"><table><tr><td>&nbsp;</td>";
			if (!empty($actions['edit'])) $head .= "  <td align=\"right\" width=\"100\"><strong>Bearbeiten</strong> <a href=\"".$actions['edit']."\" title=\"Bearbeiten\"><img src=\"images/btn_edit.gif\" alt=\"Bearbeiten\" align=\"absmiddle\" /></a></td>\n";
			if (!empty($actions['email'])) $head .= '   <td align="right" width="100"><strong>Versenden</strong>&nbsp;<a href="'.$actions['email'].'" title="Versenden" target="_blank"><img src="images/btn_email.gif" alt="Versenden" align="absmiddle" /></a></td>'."\n";
			if (!empty($actions['preview'])) $head .= '   <td align="right" width="100"><strong>Vorschau</strong>&nbsp;<a href="'.$actions['preview'].'" title="Vorschau" target="_blank"><img src="images/btn_preview.gif" alt="Vorschau" align="absmiddle" /></a></td>'."\n";
			if (!empty($actions['back'])) $head .= '   <td align="right" width="100"><strong>Zurück</strong>&nbsp;<a href="'.$actions['back'].'" title="Zurück"><img src="images/btn_back.gif" alt="Zurück" align="absmiddle" /></a></td>'."\n";
			$head .= "  </tr></table></div>\n";
		} else {
			$head .= "  <table width=\"100%\"><tr><td><strong>Anzahl $type: $num_entries Eintr". ($num_entries == 1 ? 'ag' : 'äge')."</strong></td>";
            if (!empty($actions['new'])) $head .= '   <td align="right"><strong>Neuer Eintrag </strong> <a href="'.$actions['new'].'" title="Neuen '.$type.' Eintrag erstellen"><img src="images/btn_new.gif" alt="Neuen '.$type.' Eintrag erstellen" align="absmiddle" /></a></td>'."\n";
            if (!empty($actions['edit'])) $head .= "   <td align=\"right\"><strong>Bearbeiten</strong> <a href=\"".$actions['edit']."\" title=\"$type bearbeiten\"><img src=\"images/btn_edit.gif\" alt=\" $type bearbeiten\" align=\"absmiddle\" /></a></td>\n";
						#if (!empty($actions['preview'])) $head .= '   <td align="right"><strong>Vorschau</strong>&nbsp;<a href="'.$actions['preview'].'" title="Vorschau" target="_blank"><img src="images/btn_preview.gif" alt="Vorschau" align="absmiddle" /></a></td>'."\n";
						if (!empty($actions['back'])) $head .= '   <td align="right"><strong>Zurück</strong>&nbsp;<a href="'.$actions['back'].'" title="Zurück"><img src="images/btn_back.gif" alt="Zurück" align="absmiddle" /></a></td>'."\n";
            $head .= "    </tr></table>\n";
		}
	}
	$head .= "</div>\n";

	return $head;
}

function drawListEntries($fields = 0, $entries = 0, $btns = 0, $actions = 0,$sort= false) {
	$num_entries = is_array($entries) ? count($entries) : 0;
	if (is_array($fields) && count($fields) > 0) {

		// table
		$list = '<table cellspacing="0" cellpadding="2" border="0" class="abox" width="100%">';

		// table header
		$list .= '<tr><th>&nbsp;</th>';
		foreach ($fields as $name) {
			$list .= "<th> $name </th>";
		}
		$list .= "<th>&nbsp;</th></tr>\n";

		// entries
		for ($i = 0; $i < $num_entries; $i ++) {
			if (is_array($entries[$i])) {
				$list .= '<tr'. ($i % 2 ? ' style="background:'.XOX_NLS_ROW_COLOR.';"' : '').'>';

				$set_link = TRUE;
				$id = $entries[$i]['id'];


				$list .= '<td width="16"><input type="checkbox" class="checkbox" value="1" name="'.$id.'" /></td>';
				// figure out buttons
				$buttons = '';
				if ($sort) {
					$typ = $sort;
					$buttons .='<a href="'.$actions['save'].'&cid='.$id.'&move=up&typ='.$typ.'" title="Hoch"><img src="images/btn_up.gif" alt="Hoch" /></a><a href="'.$actions['save'].'&cid='.$id.'&move=down&typ='.$typ.'" title="Runter"><img src="images/btn_down.gif" alt="Runter" /></a>';
				}
				if ($btns & XOX_NLS_BTN_EDIT && !empty($actions['detail']))
					$buttons .= '<a href="'.$actions['detail'].'&id='.$id.'" title="Bearbeiten"><img src="images/btn_edit.gif" alt="Bearbeiten" /></a>';
			 	#if ($btns & XOX_NLS_BTN_COPY && !empty($actions['detail']))
				#	$buttons .= '<a href="'.$actions['detail'].'&id='.$id.'" onclick="confirm(\'Wollen Sie diesen Eintrag kopieren?\');" title="Kopieren"><img src="images/btn_copy.gif" alt="Kopieren" /></a>';
				if ($btns & XOX_NLS_BTN_DELETE && !empty($actions['delete']))
					$buttons .= '<a href="'.$actions['delete'].'&id='.$id.'" title="Löschen"><img src="images/btn_delete.gif" alt="Löschen" /></a>';
				#if ($btns & XOX_NLS_BTN_RELOAD)
				#	$buttons .= '<a href="#" onclick="alert(\'Die Ansicht wird aktualisiert!\')" title="Status aktualisieren" ><img src="images/btn_reload.gif" alt="Status aktualisieren" /></a>';
				if ($btns & XOX_NLS_BTN_BLOCK)
					$buttons .= '<a href="#" title="Blocken"><img src="images/btn_block.gif" alt="Blocken" /></a>';
				if ($btns & XOX_NLS_BTN_EMAIL && !empty($actions['email']))
					$buttons .= '<a href="'.$actions['email'].$id.'" target="_blank" title="Versenden"><img src="images/btn_email.gif" alt="Versenden" /></a>';
				if ($btns & XOX_NLS_BTN_PREVIEW && !empty($actions['preview']))
					$buttons .= '<a href="'.$actions['preview'].$id.'" target="_blank" title="Vorschau"><img src="images/btn_preview.gif" alt="Vorschau" /></a>';

				foreach ($entries[$i] as $key => $value) {
					if ($key != "id") {
						$disp = prepDisplayString($value);
						if ($set_link && ($btns & XOX_NLS_BTN_EDIT)) {
							if ( empty($actions['detail']) ) {
								$disp = '<b>'.$disp.'</b>';
							} else {
								$disp = '<a href="'.$actions['detail'].'&id='.$id.'" title="Bearbeiten"><b>'.$disp.'</b></a>';
							}
							$set_link = FALSE;
						}
						if (is_numeric($value)) {
							$list .= "<td align=\"right\"> $disp </td>";
						} else {
							$list .= "<td> $disp </td>";
						}
					}
				}
				$list .= "<td align=\"right\">$buttons</td>";

				$list .= "</tr>\n";
			} else {
				$field_count = count($fields) + 2;
				$list .= '<tr class="category_row"><td colspan="'.$field_count."\"><strong> $entries[$i] </strong></td></tr>";
			}

		}
		$list .= "\n</table>";
	}
	return $list;
}

function drawDetail($type, $help, $page) {

	// header
	$detail = "<div class=\"acaption\"> $type </div>\n";
	$detail .= "<div class=\"bbox\">\n";
	$detail .= "  <p>$help</p>\n";
	$detail .= "</div>\n";
	$detail = '<div class="acaption" style="text-align:right;"> <input type="submit" value="Weiter" /> </div>';

	return $detail;
}

/**
 * Draw a form
 * @return form
 *
 * @param $type text formular type
   * @param $help help text
 * @param $fields array of elementgroups with(elemntgroupname,array of fields(fieldtext,inputtype,required,params))
 * @param $btns array of button types
 */
//function drawForm($type, $help, $fields = 0, $btns = 0, $actions = 0) {
function drawForm($data) {
	//mydump($data,"data");
	if ($data['link'] == 0)
		$data['link'] = array ('new' => '#', 'edit' => '#', 'detail' => '#', 'delete' => '#', 'copy' => '#', 'save' => '#', 'back' => '#');
	$form = '';
	$form .= drawFormHeader($data['headline'], $data['hilfe'], $data['button'], $data['link']);
	$form .= drawFormBody($data['data'], $data['object']);
    if(isset($data['noform']) == true){
	 return  $form ;
    }else
    {return  '<form name="'.$data['headline'].'" method="POST" action="'.$data['link']['save'].'"><input type="hidden" name="p" value="'.substr($data['link']['save'], strrpos($data['link']['save'], '=') + 1).'" />'.$form.'</form>';}
}
function drawFormHeader($type, $help, $btns = 0, $actions = 0) {
		// figure out buttons
	if ( !is_array($actions) ) $actions=array();
	$buttons = '';
	if (($btns & XOX_NLS_BTN_EDIT) && !empty($actions['edit']))
		$buttons .= '<strong style="position:relative;top:-10px;margin-left:10px;">Bearbeiten</strong>&nbsp;<a href="'.$actions['edit'].'" title="Bearbeiten">Bearbeiten&nbsp;<img src="images/btn_edit.gif" alt="Bearbeiten" /></a>';
	if (($btns & XOX_NLS_BTN_COPY) && !empty($actions['new']))
		$buttons .= '<strong style="position:relative;top:-10px;margin-left:10px;">Kopieren</strong>&nbsp;<a href="#" title="Kopieren">Kopieren&nbsp;<img src="images/btn_copy.gif" alt="Kopieren" /></a>';
	if (($btns & XOX_NLS_BTN_DELETE) && !empty($actions['delete']))
		$buttons .= '<strong style="position:relative;top:-10px;margin-left:10px;">Löschen</strong>&nbsp;<input type="image" name="cmd_delete" src="images/btn_delete.gif" title="Löschen" alt="Löschen" style="border:0;">';
	if ($btns & XOX_NLS_BTN_RELOAD)
		$buttons .= '<strong style="position:relative;top:-10px;margin-left:10px;">Aktualisieren</strong>&nbsp;<a href="#" title="Status Aktualisieren">Aktualisieren&nbsp;<img src="images/btn_reload.gif" alt="Status aktualisieren" /></a>';
	if ($btns & XOX_NLS_BTN_SAVE)
		//$buttons .= '<input type="submit" value="save" alt="Speichern" />';
		$buttons .= '<strong style="position:relative;top:-10px;margin-left:10px;">Speichern</strong>&nbsp;<input type="image" name="cmd_save" src="images/btn_save.gif" title="Speichern" alt="Speichern" style="border:0;" />';
	//$buttons .= '<a href="'.$actions['save'].'" title="Speichern"><img src="images/btn_save.gif" alt="Speichern" /></a>';
	if ($btns & XOX_NLS_BTN_BACK && !empty($actions['back']))
		$buttons .= '<strong style="position:relative;top:-10px;margin-left:10px;">Zurück</strong>&nbsp;<a href="'.$actions['back'].'" title="Zurück"><img src="images/btn_back.gif" alt="Zurück" style="border:0;position:relative;top:-4px;" /></a>';
		#$buttons .= '<input type="image" name="cmd_back" src="images/btn_back.gif" alt="Zurück" />';
		#$buttons .= '<a href="#" onclick="'.$actions['back'].'" title="Zurück"><img src="images/btn_back.gif" alt="Zurück" /></a>';

	// header
	$list = "<div class=\"acaption\"> $type</div>\n";
	$list .= "<div class=\"bbox\">\n";
	$list .= "  <p>$help</p>\n";
	//$list.= "  <table width=\"100%\"><tr><td><strong>Anzahl $type: $num_entries Eintr".($num_entries==1?'ag':'äge')."</strong></td><td align=\"right\"><strong>Neuer Eintrag</strong> <a href=\"#\" title=\"Neuen Eintrag\"><img src=\"images/btn_new.gif\" alt=\"Neuen Eintrag\" align=\"absmiddle\" /></a></td></tr></table>\n";
	$list .= "  <table width=\"100%\"><tr><td></td><td align=\"right\">$buttons</td></tr></table>\n";
	$list .= "</div>\n";
	return $list;
}

function drawFormBody($fields = 0, $cObject) {
	$list = '';
	if (is_array($fields) && count($fields) > 0) {

        //mydump($fields,"fields");
		//mydump($cObject,"cObject");

		//id field
		//$list = '<input type="hidden" name="id" value="'.$cObject->id.'">';
        $list = '';
		foreach ($fields as $elements) {
			if (!empty($elements[0])) {
				$list .= '<fieldset class="formelement"><legend>'.$elements[0].'</legend>';
			}
			$list .= '<div class="formcontent">'."\n";
			$list .= '<table cellspacing="0" cellpadding="2" border="0" class="fbox" width="100%">'."\n";
			$num_fields = is_array($elements) ? count($elements) : 0;
			//mydump($elements,"all:");

			$num_rows = 0;
			for ($i = 1; $i < $num_fields; $i ++) {

				// render row by row to enable hiding a row under certain conditions
				$showrow = true;	// set this to false to hide the row
				$listrow = '<tr'. (++$num_rows % 2 ? ' style="background:'.XOX_NLS_ROW_COLOR.';"' : '').">\n";

				//required field?
				if ($elements[$i][2] == 1) {
					$listrow .= '<td width="110px" valign="top" style="padding:8px;"><div class="required">'.$elements[$i][0].'*</div></td>'."\n";
					//$listrow.= '<td style="color:#333333">'.$elements[$i][0].'*</td>'."\n";
				} else {
					if ($elements[$i][1] == 'bridge') {
						$listrow .= '<td colspan="2" valign="top" style="padding:8px;"><div class="extratext">'.$elements[$i][0].'</div></td>'."\n";
					} else {
						$listrow .= '<td width="110px" valign="top" style="padding:8px;"><div class="fieldtext">'.$elements[$i][0].'</div></td>'."\n";
					}
				}

				$fieldval = '';
        	  	//echo "Name: ".$elements[$i][0]."<br />";
				//Datenbank Object zugriffe
				if (empty ($elements[$i][4])) {
					$elements[$i][4] = '';
					die("keine Felder definiert!!! FATAL");
				}
				elseif (is_array($elements[$i][4])) {
					//PLZ/City spezial case
					$eck1 = stristr($elements[$i][4][0], '[');
					$tagname1 = substr(substr($eck1, 1), 0, -1);
					$fieldval1 = $cObject->_details["$tagname1"];

					$eck2 = stristr($elements[$i][4][1], '[');
					$tagname2 = substr(substr($eck2, 1), 0, -1);
					$fieldval2 = $cObject->_details["$tagname2"];
				}
				elseif ($elements[$i][4] == 'null') {
						$tagname = 0;
						$fieldval = 0;
				}
				elseif ($elements[$i][4] == 'passthru') {
					//umgeschrieben für "passthru" -- siedeffects nicht getestet ;) sorry
					  	$tagname = 0;
						$fieldval = $elements[$i][2];
				}

				elseif ($eck = stristr($elements[$i][4], '[')) {
					if(strpos(trim($elements[$i][4]), '[') == 0){
						$tagname = substr(substr($eck, 1), 0, -1);
						$fieldval = $cObject->_details["$tagname"];
					}else{
						$tagname = $elements[$i][4];
						$fieldval = $cObject->$tagname;
					}
				}
		     elseif ($punkt = stristr($elements[$i][4], '|')) {
		       $pieces = explode( '|', $elements[$i][4]);
		       //mydump($pieces);
		       $tagname = $pieces[0].'['.$pieces[1].']';
		       $dataname= substr($punkt,1);

		       $fieldval = $cObject->$dataname;
		     }
					elseif (strpos($elements[$i][4],"#")!== false){
		       $fieldval = substr($elements[$i][4],1) ;
		     }
				else {

					$tagname = $elements[$i][4];
					//Zugriff auf ein verschachteltes Object ala _xml_form->email
					$tags = explode('->',$tagname);
					if(count($tags) > 1 ){
					  $tagname = $tags[0].'['.$tags[1].']';
					  $tempObj = (isset($cObject->$tags[0])?$cObject->$tags[0]:'');
					  $fieldval = (isset($tempObj->$tags[1])?$tempObj->$tags[1]:'');
					}else{

					  $fieldval = $cObject->$tagname;
					}
				}

				switch ($elements[$i][1]) {
					case 'text' :
					case 'password' :
					case 'hidden' :
						$listrow .= '<td><input type="'.$elements[$i][1].'" size="'.$elements[$i][3].'" name="'.$tagname.'" value="'.$fieldval.'"/></td>'."\n";
					  //	$showrow = false;
						break;
					case 'radio' :
					case 'checkbox' :
						$listrow .= '<td>';
						require_once (dirname(__FILE__).'/xox.newsletter.conf.php');
						//params enthalten array aus radio values
            		if (empty($fieldval)) $fieldval=0;
						$showrow=false;
						$full_tagname = $tagname;
						if(stristr($tagname,'_xml_form')) $tagname='_xml_form';

						//Schleife für mehrfache checkbox
						//muss in xox.newsletter.conf.php gesetzt sein.
						foreach ($elements[$i][3] as $radiodisplay=>$radiovalue) {
							$showrow=true;
				  			  //	echo $radiovalue;
							$listrow .= '<input class="checkbox" type="'.$elements[$i][1].'" name="'.$full_tagname.'[]"';
							if (substr($radiovalue, 0, 1) == '*' && substr($radiovalue, 1, 1) != '*') {
								$radiovalue = substr($radiovalue, 1);
							}
							$checkvalue = '';
							// $checkbox_options defined in xox.newsletter.conf.php
							// pick it up from the global space
							$checkbox_options = $GLOBALS['checkbox_options'];
							//echo '<xmp>';
							//print_r($fieldval);
							//print_r($checkbox_options);
							//echo '</xmp>';
							if (!empty($checkbox_options[$tagname][$radiovalue])) {
								$checkvalue = $checkbox_options[$tagname][$radiovalue];
							}
							//echo $tagname.':'.$checkvalue."<br>\n";
							if(is_array($fieldval) && $tagname=='_xml_form')
							  	$listrow .= ' value="'.$radiovalue.'"'; // for xmlformclass
							else
								$listrow .= ' value="'.$checkvalue.'"'; // Default

							if ( is_numeric($checkvalue) && ($fieldval & $checkvalue) &&  !is_array($fieldval)) {
								$listrow .= ' checked';
							}elseif(is_array($fieldval)){  //for xmlform cases
								//echo "arr";
								if(isset($fieldval[$radiovalue]) && $fieldval[$radiovalue] == $checkvalue){
									$listrow .= ' checked';
								}
							} else {
								$listrow .= ((preg_match("/$fieldval/i", $checkvalue)) ? ' checked' : '');
							}
							$listrow .= ' />&nbsp;&nbsp;';
							if($tagname=='_xml_form')$radiovalue='';
							$listrow .= ($elements[$i][4]) ? '<span class="radiotext">'.$radiovalue.'</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'."\n";
						}
						$listrow .= '</td>';
						break;
					case 'textarea' :
						#if ($elements[$i][5] == true) {
						#	$listrow .= "<td><input type=\"hidden\" id=\"HtmlValue\" name=\"HtmlValue\" value=\"\" /><script type=\"text/javascript\">var oFCKeditor = new FCKeditor( 'html' ) ;oFCKeditor.BasePath = path;oFCKeditor.Value ='".$fieldval."' ;oFCKeditor.Create();   </script></td>\n";
						#} else {
							$listrow .= '<td><textarea name="'.$tagname.'" cols="'.$elements[$i][3][0].'" rows="'.$elements[$i][3][1].'"/>'.$fieldval.'</textarea></td>'."\n";
						#}
						break;
					case 'htmlarea' :
					case 'htmlarea2' :
							$listrow .= '<td><textarea id="'.$elements[$i][1].'" name="'.$tagname.'" cols="'.$elements[$i][3][0].'" rows="'.$elements[$i][3][1].'"/>'.$fieldval.'</textarea></td>'."\n";
						break;
					case 'passthru' :
						$listrow .= '<td><b>'.$fieldval.'</b></td>'."\n";
						#$listrow .= '<td>'.$fieldval.'</td>'."\n";
						break;
					case 'check' :
						//TODO: fertig und testen
						if ( empty($elements[$i][0])||empty($elements[$i][3]) ) {
							$showrow = false;
						} else {
							$listrow .= '<td><input type="checkbox" name="'.$elements[$i][0].'" value="'.$elements[$i][3].'"> '.$elements[$i][3].'<br>'.$elements[$i][2].'</td>'."\n";
						}
						break;
					case 'select' :
						$option = '';
						if (is_array($elements[$i][3])) {
							foreach ($elements[$i][3] as $radiodisplay=>$radiovalue) {
								if ( is_numeric($radiodisplay) ) $radiodisplay = $radiovalue;
								$option .= '<option value="'.($radiovalue).'"';
                // echo "field:".$fieldval." valeu:".$radiovalue;
                if(preg_match( "/$fieldval/i", $radiovalue)) $option .= ' selected ';
                $option .= '>'.$radiodisplay.'</option>';
							}
							$size = (empty ($elements[$i][5])) ? '' : 'size="'.$elements[$i][5].'" multiple';
							if ( empty($option) ) {
								$showrow=false;
							} else {
								$option = '<select name="'.$tagname.'" '.$size.'> '.$option.'</select>';
							}
						}
						elseif (is_string($elements[$i][3])) {
							$option = HTMLSelect($elements[$i][3], $tagname, $fieldval);
							if ( empty($option) ) $showrow=false;
						}
						$listrow .= '<td class="select">'.$option.'</td>'."\n";
						break;
					case 'twotext' :
						$listrow .= '<td><input type="text" size="'.$elements[$i][3][0].'" name="'.$tagname1.'" value="'.$fieldval1.'"/>&nbsp;&nbsp;&nbsp;<input type="text" size="'.$elements[$i][3][1].'" name="'.$tagname2.'" value="'.$fieldval2.'"/></td>'."\n";
						break;
					case 'bridge' :
						break;
					case 'date' :
						if ( empty($fieldval) ) {
							$dpart = array(date('Y'),date('m'),date('d'),date('H'),date('i'),date('s'));
						} else {
							$dpart = preg_split('/[-: ]/i',$fieldval);
						}
						$listrow .= '<td>';
						$listrow .= '<select name="'.$tagname.'_day" style=\"text-align:right;\">';
						for ( $i=1; $i<32; $i++ ) {
							$listrow.=sprintf("<option style=\"text-align:right;\"%s>%02d</option>",($i==$dpart[2])?' selected':'',$i);
						}
						$listrow .= '</select>';
						$listrow .= '<select name="'.$tagname.'_month" style=\"text-align:right;\">';
						for ( $i=1; $i<13; $i++ ) {
							$listrow.=sprintf("<option style=\"text-align:right;\"%s>%02d</option>",($i==$dpart[1])?' selected':'',$i);
						}
						$listrow .= '</select>';
						$listrow .= '<select name="'.$tagname.'_year" style=\"text-align:right;\">';
						for ( $i=date('Y'); $i<(date('Y')+2); $i++ ) {
							$listrow.=sprintf("<option style=\"text-align:right;\"%s>%02d</option>",($i==$dpart[0])?' selected':'',$i);
						}
						$listrow .= '</select>&nbsp;&nbsp;&nbsp;';
						$listrow .= '<select name="'.$tagname.'_hour" style=\"text-align:right;\">';
						for ( $i=0; $i<24; $i++ ) {
							$listrow.=sprintf("<option style=\"text-align:right;\"%s>%02d</option>",($i==$dpart[3])?' selected':'',$i);
						}
						$listrow .= '</select>';
						$listrow .= '<select name="'.$tagname.'_minute" style=\"text-align:right;\">';
						$listrow .= "<option style=\"text-align:right;\">00</option>";
						$listrow .= "<option style=\"text-align:right;\">30</option>";
						$listrow .= '</select>';
						$listrow .= '&nbsp;Uhr</td>';
						break;
					default :
						$listrow .= '<td>&nbsp;</td>';
				}

				if ($showrow) $list .= "$listrow</tr>\n";
			}

			$list .= "\n</table></div>";
			if (!empty($elements[0])) $list .= "</fieldset>";
			$list .= "\n";
		}

	}else $list .= $fields;

	return $list;
}

?>
