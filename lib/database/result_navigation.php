<?php

  // current select page
	if (isset($_POST['current_page'])) $GLOBALS['current_page']=$_POST['current_page'];
	elseif ( isset($_GET['cp']) ) $GLOBALS['current_page']=$_GET['cp'];
	elseif ( isset($_SESSION['current_page']) ) $GLOBALS['current_page']=$_SESSION['current_page'];
  if (!isset($GLOBALS['current_page']))
		$GLOBALS['current_page']=(isset($GLOBALS['last_page']))?$GLOBALS['last_page']:1;

	if (!isset($GLOBALS['max_results_per_page'])) $GLOBALS['max_results_per_page'] = 10;

  $GLOBALS['pages'] = (int)($GLOBALS['num_results'] / $GLOBALS['max_results_per_page']);
  if ( $GLOBALS['num_results']/$GLOBALS['max_results_per_page']-$GLOBALS['pages'] > 0 ) $GLOBALS['pages'] += 1;

	if ($GLOBALS['current_page']<1) $GLOBALS['current_page']=1;
	elseif ( $GLOBALS['current_page'] > $GLOBALS['pages'] ) $GLOBALS['current_page'] = $GLOBALS['pages'];
	
	if (!isset($GLOBALS['page_url'])) $GLOBALS['page_url'] = ((isset($GLOBALS['nav'])) ? $GLOBALS['nav']->pageurl : $_SERVER["REQUEST_URI"]);

	#$GLOBALS['page_navigation_counter']=0;


	#
	# show page navigation
	#
	function showResultNavigation($twidth='100%') {

		$html = "\n<!------- BEGIN pagingToolBar --------------->\n";

	    if ( $GLOBALS['pages'] > 1 ) {

			$html.=	'<div style="width:'.$twidth.';height:20px;" class="pagingToolBar"><div style="float:left;width:9%;">';

			if ( $GLOBALS['current_page'] > 1 )
				$html.=	'<input type="button" name="cmd_prev" class="pagingToolBarBtn" value="&lt;&lt;" onclick="document.location=\''.$GLOBALS['page_url'].'&cp='.($GLOBALS['current_page']-1).'\';" />';
			else $GLOBALS['current_page']=1;

			$html.=	'&nbsp;</div><div style="float:left;text-align:center;width:81%;" class="pagingToolBarPages">&nbsp;';

			if ($GLOBALS['current_page']%10 == 0) {
			    $pageStart=$GLOBALS['current_page'];
			} else {
				$pageStart=$GLOBALS['current_page']-($GLOBALS['current_page']%10);
			}
			if ($pageStart<1) $pageStart = 1;
			$pageEnd = $pageStart + 9;
			if ($pageStart>1) $html .= '<a href="'.$GLOBALS['page_url'].'&cp=1" class="pagingToolBarPage">01</a>&nbsp;...&nbsp;';

			for ( $i = $pageStart; $i <= $GLOBALS['pages'] && $i <= $pageEnd; $i++ ) {
				if ( $i == $GLOBALS['current_page'] ) {
					$html .= '<span class="pagingToolBarSelected">'.(($i<100)?substr('00'.$i,-2):substr('000'.$i,-3)).'</span>';
				} else {
		        	$html .= '<a href="'.$GLOBALS['page_url'].'&cp='.$i.'" class="pagingToolBarPage">'.(($i<100)?substr('00'.$i,-2):substr('000'.$i,-3)).'</a>';
			  	}
				$html .= '&nbsp; ';
		    }

			if ($pageEnd<$GLOBALS['pages']) $html .= '...&nbsp;<a href="'.$GLOBALS['page_url'].'&cp='.$GLOBALS['pages'].'" class="pagingToolBarPage">'.(($GLOBALS['pages']<100)?substr('00'.$GLOBALS['pages'],-2):substr('000'.$GLOBALS['pages'],-3)).'</a>';

      		$html.=	'</div><div style="float:right;text-align:right;width:9%;">&nbsp;';

      		if ( $GLOBALS['pages'] > $GLOBALS['current_page'] )
				$html.=	'<input type="button" name="cmd_next" class="pagingToolBarBtn" value="&gt;&gt;" onclick="document.location=\''.$GLOBALS['page_url'].'&cp='.($GLOBALS['current_page']+1).'\';" />';

      		$html.= "</div></div><br style=\"clear:both;\" />\n";
			$html .= "\n<!------- END pagingToolBar --------------->\n";
			return $html;
		}
	}  // showNavigation

	if (isset($res['us']) && $res['us']==0) $_SESSION['user_select'] = "";
	$_SESSION['current_page'] = $GLOBALS['current_page'];
	$GLOBALS['result_navigation'] = " LIMIT ".(($GLOBALS['current_page']-1)*$GLOBALS['max_results_per_page']).",".$GLOBALS['max_results_per_page'];

 ?>
