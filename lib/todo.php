<?php

	$parsed_source='';
	
	$parent_script = isset($_SERVER['PATH_TRANSLATED']) ? $_SERVER['PATH_TRANSLATED'] : $_SERVER['PHP_SELF'];
	if($parent_script && ($fh=@fopen($parent_script,'r'))) {
		$parsed_source = fread($fh,filesize($parent_script));
		fclose($fh);
	}
  
	$child_script = (isset($GLOBALS['nav']) && is_object($GLOBALS['nav'])) ? $GLOBALS['nav']->page->url : '';
	if($child_script && ($fh=@fopen($child_script,'r'))) {
		$parsed_source.= "// TODO:<div align=\"right\" style=\"background:black;color:yellow;\">$child_script</div>\n";
		$parsed_source.= fread($fh,filesize($child_script));
		fclose($fh);
	}
	
	if ( preg_match_all('/TODO[:]?(.*)/',$parsed_source,$matches) ) {
		$xoxtodo = '';
		foreach($matches[1] as $match) $xoxtodo.='<p style="margin-top:4px;">'.trim($match).'</p>';
		if ( $xoxtodo!='' )  {
			?>
			<script language="javascript">

			if ( parent && document.getElementById ) {
				if ( parent.frames['xoxmessages'] ) {
					var view = parent.frames['xoxmessages'].document.getElementById('xoxtodo');
					if ( view ) {
						view.innerHTML = '<?php echo $xoxtodo; ?>';
					}
				}
			}

			</script>
			<?php
		}
	}
	
	unset($parsed_source);
	unset($matches);
	unset($match);
	unset($fh);
	unset($xoxtodo);

?>