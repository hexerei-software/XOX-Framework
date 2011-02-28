<?php

	require_once(dirname(__FILE__).'/dummy.php');
	require_once(dirname(__FILE__).'/inc.sqltables.php');

	if ( !isset($data) ) {
		$data = array(
		'headline'=>'Subscribers',
		'body'=>array('Here you can view, edit, copy and delete your existing subscribers, or add new subscribers.','search','Usersearch:'),
		'tablehead'=>array('E-Mail','Name','Subscribed','Subsrciptions'),
		'link'=>array("edit"=>"?p=de/3/1","new"=>"?p=de/3/1","detail"=>"?p=de/3/1",'delete'=>'?p=de/7/1','copy'=>'#','save'=>'javascript:history.back();')
		);
	}
	//Search Box verarbeiten
	$search_str = '';
	if(isset($_GET['search'])){
		$search_str = " AND (email LIKE '%".$_GET['search']."%' OR displayname LIKE '%".$_GET['search']."%')";
	}

	$data['data'] = "SELECT id, email, displayname, created, '1' AS abos FROM ".$SQLTable['subscriber']." WHERE domain_id='".$GLOBALS['tnl_domain_id']."' ".$search_str." ORDER BY email";
	$data['buttons'] = 5;

	echo drawList($data);

?>
