<?php
	define('XOX_LIB', 							'../lib');

require_once(XOX_LIB.'/navigation/class.navigation.php');
require_once('debug.php');


	// create navigation and set pass through
  $nav = new xoxNavigation('www.xox.php');
  $nav->addPassthru('url');


  mydump($nav);

?>
