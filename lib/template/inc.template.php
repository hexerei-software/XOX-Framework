<?php

	require_once(dirname(__FILE__)."/class.template.php");

	/** creates default box with caption
	 @author <a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
	 @param caption the caption for the box
	 @param content the content of the box
	 @param class_pre the class prefix to use (default "a")
	 @param width the width of the box (default "100%")
	 @param icon the icon to use in the caption ("BYCLASS" uses stylesheet only)
	 @return the ready rendered html box
	 @note the icon parameter is currently not supported
	 */
	function getBoxed($caption,$content,$class_pre='a',$width="100%",$icon="BYCLASS") {
		/*
		if ( empty($GLOBALS['box_template']) ) {
			$GLOBALS['box_template'] = new xoxSimpleTemplate('box','box');
		}

		if ($icon=="BYCLASS") $icon ="images/".$class_pre."caption.gif";

		$box = $GLOBALS['box_template'];

		$box->setVar('width',		$width);
		$box->setVar('icon',		$icon);
		$box->setVar('caption',	$caption);
		$box->setVar('content',	$content);
		$box->setVar('class',		$class_pre);

		return $box->show(TRUE);
		*/
		if (isset($GLOBALS['getBoxCorners']))
		return wrapCorners($caption,$content,$GLOBALS['getBoxCorners'],$width,'');
		else return wrapBox($caption,$content,$class_pre,$width,'');
	}

	function wrapBox($caption,$content,$class_pre='a',$width="100%",$ustyle='') {
		$style = (empty($width) ? '' : 'width:'.(ereg('%$',$width)?$width:$width.'px').';');
		$style = (empty($ustyle) ? $style : $style.$ustyle);
		$style = (empty($style) ? '' : ' style="'.$style.'"');
		$box = '<div'.$style.'>';
    if (!empty($caption)) $box.= '<div class="'.$class_pre.'caption">'.$caption.'</div>';
    $box.= '<div class="'.$class_pre.'box">'.$content.'</div>';
		$box.= "</div>\n";
		return $box;
	}

	function wrapCorners($caption,$content,$class_pre='a',$width="100%",$ustyle='') {
		if (empty($content)) return '';
		// split class parameter
		$boxclasses = explode('|',$class_pre);
		if (count($boxclasses) < 1) $boxclasses = array('','');
		elseif (count($boxclasses) < 2) $boxclasses[1] = $boxclasses[0];
		$style = (empty($width) ? '' : 'width:'.(ereg('%$',$width)?$width:$width.'px').';');
		$style = (empty($ustyle) ? $style : $style.$ustyle);
		$style = (empty($style) ? '' : ' style="'.$style.'"');
		$box = '<div class="'.$boxclasses[0].'box"'.$style.'>';
		$box.= '<div class="'.$boxclasses[1].'box_tr">';
		$box.= '<div class="'.$boxclasses[1].'box_tl">';
		$box.= '<div class="'.$boxclasses[1].'box_br">';
		$box.= '<div class="'.$boxclasses[1].'box_bl">';
		$box.= '<div class="box_content">';
		if (!empty($caption)) $box.= '<h3>'.$caption.'</h3>';
		$box.= $content."</div></div></div></div></div></div>\n";
		return $box;
	}

?>
