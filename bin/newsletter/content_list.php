<?php

	require_once(dirname(__FILE__).'/dummy.php');
	require_once(dirname(__FILE__).'/inc.sqltables.php');
	require_once(dirname(__FILE__).'/themes/class.newsletter_themes.php');
	require_once(dirname(__FILE__).'/inc.classes.php');



	if ( !isset($data) ) {
		$data = array(
		'headline'=>'Content',
		'body'=>'Here you can view, edit, copy and delete your existing contents, or add new content to the topic.',
		'tablehead'=>array('News','Text'),
		'link'=>array("edit"=>"#","new"=>"#","detail"=>"#",'delete'=>'#','copy'=>'#','save'=>'#')
		);
	}
    $where = '';
    if (isset($issue_id)) $where .= " WHERE issue_id='".$issue_id."'";
    if (isset($topic_id)) $where .= " AND topic_id='".$topic_id."'";

	// set num results to total of news and teaser entries
	$GLOBALS['num_results'] = countQuery("SELECT id FROM ".$SQLTable['content'].$where);

	$GLOBALS['max_results_per_page'] = 200;

	 if (isset($issue_id)){
		$issue = new cIssue($issue_id);
		$nl = $issue->getNewsletter();

		$tm = new xoxNewsletterTheme($nl->template_html);
		$first = true;
		$all_subs = array();
		foreach($tm->sub_name as $sub=>$name){

			$data['tablehead'] = array($name,'Text');
			$sqls="SELECT id,  title, body FROM ".$SQLTable['content'].$where." AND flags='".$sub."' ORDER BY displayorder";

			$data['data'] = $sqls;
			$data['buttons'] = 15;
			$data['sort'] = $sub;

			echo drawList($data,$first);
			$first=false;
			$all_subs[] = $sub;
		}
	 } else
	 {
	 }




	$num_results_subs = countQuery("SELECT id FROM ".$SQLTable['content'].$where." AND flags IN ('".implode("','",$all_subs)   ."')");

	if($num_results_subs < $GLOBALS['num_results']){
	// now draw all entries without matching sub
	$data2 = $data;
	$data2['tablehead'] = array('Keiner Kategorie zugeordnet','Text');
	$data2['data']= "SELECT id,  title, body FROM ".$SQLTable['content'].$where." AND flags NOT IN ('".implode("','",$all_subs)   ."') ORDER BY displayorder";
	$data2['sort']='all';
	echo drawList($data2,false);
	}

?>
