<?php

	// set language
	if (!defined('DEFAULT_LANGUAGE')) define('DEFAULT_LANGUAGE','de');
	$mod_language = ((empty($GLOBALS['xox_language_id']))?DEFAULT_LANGUAGE:$GLOBALS['xox_language_id']);

	// *** top *****************************************************************

	if (empty($GLOBALS['user'])) include(dirname(__FILE__).'/mod.login.php');
	else include(XOX_APP_BASE.'/'.$mod_language.'/top.html');

	// *** news module *********************************************************

	$max_news_viewed = 10;
	//include('news/mod.news.php');

	// *** bottom **************************************************************

	include(XOX_APP_BASE.'/'.$mod_language.'/bottom.html');

?>