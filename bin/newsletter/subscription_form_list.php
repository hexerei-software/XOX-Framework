<?php

	require_once('dummy.php');
	require_once('inc.sqltables.php');

	if ( !isset($data) ) {
		$data = array(
		'headline'=>'Forms',
		'body'=>array('Here you can view, edit, copy and delete your existing subscription forms, or add new subscription forms.','search','Usersearch:'),
		'tablehead'=>array('Name','Newsletters'),
		'link'=>array("edit"=>"?p=de/3/1","new"=>"?p=de/3/1","detail"=>"?p=de/3/1",'delete'=>'?p=de/7/1','copy'=>'#','save'=>'javascript:history.back();')
		);
	}

	$data['data'] = "SELECT id, name, '1' AS newsletters FROM ".$SQLTable['subscription_form']." WHERE domain_id='".$GLOBALS['tnl_domain_id']."' ORDER BY name";
	$data['buttons'] = 133 ;

	echo drawList($data);

?>
