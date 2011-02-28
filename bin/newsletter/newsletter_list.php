<?php

	require_once(dirname(__FILE__).'/dummy.php');
	require_once(dirname(__FILE__).'/inc.sqltables.php');

	if ( !isset($data) ) {
		$data = array(
		'headline'=>'Newsletter',
		'body'=>'Here you can view, edit, copy and delete your existing newsletters, or add new newsletters.',
		'tablehead'=>array('Title','Subject','Status'),
		'link'=>array("edit"=>"#","new"=>"#","detail"=>"#",'delete'=>'#','copy'=>'#','save'=>'#')
		);
	}

	$data['data'] = 
		'SELECT a.id, a.name, a.description, count(b.id) AS issues FROM '
		.$SQLTable['newsletter'].' AS a LEFT JOIN issue AS b ON b.newsletter_id=a.id WHERE a.domain_id='
		.$GLOBALS['tnl_domain_id']." GROUP BY a.id ORDER BY a.sort, a.changed";
	
	$GLOBALS['num_results']=countQuery($data['data'],'a.id');
	
	$data['buttons'] = 385;

	echo drawList($data);

?>