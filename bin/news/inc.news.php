<?php

	function getNews($max_news,$order_by='date') {
		return array(
			array(
				'date'=> '23.10.2002',
				'author'=> 'Daniel Vorhauer',
				'headline'=> 'Headline 1',
				'teaser'	=> 'Teaser 1'
			),
			array(
				'date'=> '23.10.2002',
				'author'=> 'Daniel Vorhauer',
				'headline'=> 'Headline 2',
				'teaser'	=> 'Teaser 2'
			),
			array(
				'date'=> '23.10.2002',
				'author'=> 'Daniel Vorhauer',
				'headline'=> 'Headline 3',
				'teaser'	=> 'Teaser 3'
			)
		);
	}

	function formatNews($date='',$author='',$caption='',$content='',$picture='',$icon="images/text.gif",$width="98%") {

		if ( empty($GLOBALS['news_template']) ) {
			$GLOBALS['news_template'] = new xoxSimpleTemplate('news','news');
		}

		$news = $GLOBALS['news_template'];

		$news->setVar('date',			$date);
		$news->setVar('author',		$author);
		$news->setVar('caption',	$caption);
		$news->setVar('content',	$content);
		$news->setVar('picture',	$picture);
		$news->setVar('icon',			$icon);
		$news->setVar('width',		$width);

		return $news->show(TRUE);
	}

?>
