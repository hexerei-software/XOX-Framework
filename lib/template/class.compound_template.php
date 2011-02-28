<?php

	require_once(dirname(__FILE__).'/class.template.php');

  //=== template handling ======================================================

	/*****************************************************************
		xoxCompundTemplate
	*****************************************************************/
	class xoxCompoundTemplate
	{
		var $templates	= array();
		var $vars				= array();
		var $sub_count	= 0;

		function xoxCompoundTemplate($master_template='',$sub_template='',$css='') {

			// reset local vars
			$this->templates 	= array();
			$this->vars 			= array();
			$this->sub_count	= 0;

			// set master template
			$this->templates[0] = new xoxSimpleTemplate('',$master_template,$css);

			// set sub template(s)
			if ( !is_array($sub_template) ) $sub_template = array($sub_template);
			foreach($sub_template as $sub=>$template) {
				$this->sub_count++;
				if ( is_numeric($sub) ) $sub=$this->sub_count;
				$this->templates[$sub] = new xoxSimpleTemplate('',$template,$css);
			}
		}

		function setVar( $aname, $var=0, $template=-1, $row=0 ) {
			if ( empty($template) ) $template=1;
			elseif( $template==-1 ) $template=0;
			if ( !isset($this->templates[$template]) ) $template=1;
			if ( !empty($aname) ) {
				if ( empty($var) ) {
					if ( is_object($aname) ) {
  					foreach ($aname as $name=>$value) $this->vars[$template][$row][$name] = $value;
					} elseif ( is_array($aname) && count($aname) > 0 ) {
						if ( isset($aname[0]) && is_array($aname[0]) ) {
  						foreach ($aname as $data)
  							foreach ($data as $name=>$value)
									$this->vars[$template][++$row][$name] = $value;
						} else {
  						foreach ($aname as $name=>$value)
								$this->vars[$template][$row][$name] = $value;
						}
					} elseif ( !empty($GLOBALS[$aname]) ) {
						$this->vars[$template][$row][$aname] = $GLOBALS[$aname];
					} else {
						$this->vars[$template][$row][$aname] = $var;
					}
				} elseif (is_array($var)) {
  				foreach ($var as $value)
						$this->vars[$template][++$row][$aname] = $value;
				} else {
					$this->vars[$template][$row][$aname] = $var;
				}
			}
		} // setVar

		function show($get=FALSE) {
			if ($total_count = count($this->templates)) {
				$content = '';

				#for ( $i=1; $i<$total_count; $i++) {
				$subs = $this->templates;
				unset($subs[0]);
				$i=1;
				foreach($subs as $key=>$tmp) {
			 //echo "checking $key => $tmp <br>\n";

					$sub = '';
					if ( isset($this->vars[0][0]) ) $tmp->setVar($this->vars[0][0]);
					$var = (isset($this->vars[$key])) ? $this->vars[$key] : ((isset($this->vars[$i]))?$this->vars[$i]:0);

					if ( is_array($var) ) {
						if ( count($var)>0 ) {
							foreach($var as $vars) {
								$tmp->setVar($vars);
								$sub.=$tmp->show(true);
							}
						} else {
							$tmp->setVar($var);
							$sub.=$tmp->show(true);
						}
					} else {
						$sub.=$tmp->show(true);
					}
					if (is_numeric($key)) {
						$this->templates[0]->setVar(sprintf('SUB%04d',$i),$sub);
					} else {
						$this->templates[0]->setVar(strtoupper($key),$sub);
					}
					$content.=$sub;
					$i++;
				}

				$this->templates[0]->setVar('CONTENT',$content);
				if ( isset($this->vars[0][0]) ) $this->templates[0]->setVar($this->vars[0][0]);
				return $this->templates[0]->show($get);
			}
			return false;
		} // show

		function debug() {
			echo "\n..:: TEMPLATE ::..\n============================================\n";
			var_dump($this->templates);
			echo "\n..:: VARS ::..\n============================================\n";
			var_dump($this->vars);
			echo "\n..:: SUB_COUNT ::..\n============================================\n";
			var_dump($this->sub_count);
		}

	}	// finish class xoxCompoundTemplate

?>
