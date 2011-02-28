<?php

	require_once(dirname(__FILE__).'/dummy.php');
	require_once(dirname(__FILE__).'/inc.sqltables.php');

	if ( !isset($data) ) {
		$data = array(
		'headline'=>'Issues',
		'body'=>'Here you can view, edit, copy and delete your existing issues, or add new issue to the newsletter.',
		'tablehead'=>array('Date','Title','Status'),
		'link'=>array("edit"=>"#","new"=>"#","detail"=>"#",'delete'=>'#','copy'=>'#','save'=>'#')
		);
	}
    $where = '';
    if (isset($newsletter_id)) $where = " WHERE newsletter_id='".$newsletter_id."'";
    
	$data['data'] = "SELECT id, date, title, '1' AS status FROM ".$SQLTable['issue'].$where." ORDER BY title";
	$data['buttons'] = 129;

	echo drawList($data);

?>