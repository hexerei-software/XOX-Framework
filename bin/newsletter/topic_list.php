<?php

	require_once(dirname(__FILE__).'/dummy.php');
	require_once(dirname(__FILE__).'/inc.sqltables.php');

	if ( !isset($data) ) {
		$data = array(
		'headline'=>'Topic',
		'body'=>'Here you can view, edit, copy and delete your existing topics, or add new topic to the newsletter.',
		'tablehead'=>array('Title','Subject','Status'),
		'link'=>array("edit"=>"#","new"=>"#","detail"=>"#",'delete'=>'#','copy'=>'#','save'=>'#')
		);
	}
  $where = '';
  if (isset($newsletter_id)) $where = " WHERE newsletter_id='".$newsletter_id."'";
    
	$data['data'] = "SELECT id, name, description, '1' AS status FROM ".$SQLTable['topic'].$where." ORDER BY name";
	$data['buttons'] = 15;

	echo drawList($data);

?>