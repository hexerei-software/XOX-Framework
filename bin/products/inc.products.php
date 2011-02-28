<?php

	function getProduct($type,$category,$order_by='name') {
		return array(
			array(
				'name'=> 'Daniel Vorhauer',
				'headline'=> 'Produkt 1',
				'teaser'	=> 'Teaser 1'
			),
			array(
				'name'=> 'Daniel Vorhauer',
				'headline'=> 'Produkt 2',
				'teaser'	=> 'Teaser 2'
			),
			array(
				'name'=> 'Daniel Vorhauer',
				'headline'=> 'Produkt 3',
				'teaser'	=> 'Teaser 3'
			)
		);
	}

	function formatProduct($name='',$caption='',$content='',$picture='',$icon="images/text.gif",$width="98%") {

		if ( empty($GLOBALS['products_template']) ) {
			$GLOBALS['products_template'] = new xoxSimpleTemplate('products','products');
		}

		$products = $GLOBALS['products_template'];

		$products->setVar('name',			$name);
		$products->setVar('caption',	$caption);
		$products->setVar('content',	$content);
		$products->setVar('picture',	$picture);
		$products->setVar('icon',			$icon);
		$products->setVar('width',		$width);

		return $products->show(TRUE);
	}

?>