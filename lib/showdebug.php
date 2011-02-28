<?php

	show_vars(FALSE,TRUE,TRUE);

	$xoxdebug = isset($GLOBALS['xoxdebug']) ? $GLOBALS['xoxdebug'] : '';

	if ( $xoxdebug!='' )  {
		$xoxdebug = addslashes($xoxdebug);
		$xoxall = preg_split('/[\r\n]+/',$xoxdebug);
?>

		<script language="javascript">

		if ( parent && document.getElementById ) {
			var xox_content = '';
			<?php
				foreach($xoxall as $d) echo "xox_content+='$d';\n";
			?>
			if ( parent.frames['xoxmessages'] ) {
				var view = parent.frames['xoxmessages'].document.getElementById('xoxdebug');
				if ( view ) view.innerHTML = xox_content;
			}
		}

		</script>

<?php
	}

?>