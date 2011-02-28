<?php

	require_once(XOX_BIN.'/newsletter/dummy.php');
	require_once(XOX_LIB.'/functions.php');

	if (empty($form_title)) $form_title = initvar('form_title','Title');
	if (empty($form_text)) $form_text = initvar('form_text','Text');
	if (empty($form_links)) $form_links = initvar('form_links',array('back'=>'javascript:history.back();'));

echo drawFormHeader(
		$form_title,
		$form_text,
		68,
		$form_links
		
);


?>