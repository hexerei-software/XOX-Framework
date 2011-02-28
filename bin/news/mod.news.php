<?php

	$news_lang = ( isset($GLOBALS['xox_language_id']) ) ? $GLOBALS['xox_language_id'] : 'de';

	#echo XOX_APP_BASE.'/'.$news_lang.'/news/news.xml : '.XOX_APP_BASE.'/xox/bin/news/news.xsl';
	
	$tmp = new xoxXMLTemplate(XOX_APP_BASE.'/'.$news_lang.'/news/news.xml', XOX_APP_BASE.'/xox/bin/news/news.xsl');

	// set replacement variables
  $tmp->setVar('page_title', htmlspecialchars($GLOBALS['nav']->page->title));
	if (!empty($news_admin)) 			$tmp->setVar('news_admin');
	if (!empty($max_news_viewed)) $tmp->setVar('max_news_viewed',$max_news_viewed);
	if (!empty($news_id))         $tmp->setVar('news_id');

	// show the news template
	$tmp->show();

?>