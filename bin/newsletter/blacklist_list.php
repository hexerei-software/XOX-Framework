<?php

	require_once('dummy.php');
	require_once('inc.sqltables.php');

	if ( !isset($data) ) {
		$data = array(
		'headline'=>'Blacklist',
		'body'=>'Here you can view, edit, copy and delete your existing blacklist filters, or add new filters.',
		'tablehead'=>array('Filter','Comment','Created'),
		'link'=>array("edit"=>"#","new"=>"#","detail"=>"#",'delete'=>'#','copy'=>'#','save'=>'#')
		);
	}

	$data['data'] = "SELECT id, filter, comment, created FROM ".$SQLTable['blacklist']." WHERE domain_id='".$GLOBALS['tnl_domain_id']."' ORDER BY filter";
	$data['buttons'] = 5;

	echo drawList($data);

?>