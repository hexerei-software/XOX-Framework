<?php

	$prod_lang = ( isset($GLOBALS['xox_language_id']) ) ? $GLOBALS['xox_language_id'] : 'de';
	$prod_source = ( !empty($GLOBALS['prod_source']) ) ? $GLOBALS['prod_source'] : 'default';
	
	$style_file = "$prod_lang/products/$prod_source/products.xsl";
	if ( !file_exists($style_file) ) $style_file = "$prod_lang/products/products.xsl";
	
	$tmp = new xoxXMLTemplate("$prod_lang/products/$prod_source/products.xml", $style_file);

	// set replacement variables
  $tmp->setVar('page_title', htmlspecialchars($nav->page->title));
  $tmp->setVar('prod_source');
	
	if (!empty($prod_admin))			$tmp->setVar('prod_admin');
	if (!empty($max_prod_viewed)) $tmp->setVar('max_prod_viewed');
	if (!empty($prod_id))         $tmp->setVar('prod_id');

	// show the news template
	$tmp->show();
	
	/*require_once("inc.products.php");

	$products_content = " ";

	$products = getProduct($_POST['t'],$_POST['c']);
	foreach($products as $p) {
		$products_content.=formatProduct($n['name'],$n['headline'],$n['teaser']);
	}

	// build caption
	$products_caption = 'CHAKA Products';
	$products_link = 'Overview of CHAKA Products';
	if ( $GLOBALS['xox_language_id'] ) {
		switch ($GLOBALS['xox_language_id']) {
			case 'de':
				$products_caption = 'CHAKA Produkte';
				$products_link = '&Uuml;bersicht &uuml;ber die CHAKA Produkte';
				break;
		}
	}

	echo getBoxed($products_caption,$products_content.$products_link);*/

?>