<?php

	if (!isset($GLOBALS['xoxmod'])) $GLOBALS['xoxmod'] = array();

  //=== template module handling ===================================

	/**	this is the base class for template modules.
	 it handles initialization and rendering of modules for the current page.
	 modules are rendered template units, that do not appear in the content area
	 @author <a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
	*/
	class xoxModHandler
	{
		var $output;
		var $page;
		var $mods;
		var $corners;
		var $base;

		/** construct module handler with given parameters
		 @param page the page that contains left, right, header and footer modules
		 */
		function xoxModHandler($modbase='') {
			if (!isset($GLOBALS['xoxmod'])) $GLOBALS['xoxmod'] = array();
			$this->reset();
			if (!empty($modbase)) $this->base = $modbase;
		}

		/** render the current page
		 @param page the page that contains left, right, header and footer modules
		 */
		function renderPage($page) {
			if (is_object($page)) {
				$this->page = $page;
				if ( is_array($this->page->mods) ) {
					$this->renderArea('header');
					$this->renderArea('left');
					$this->renderArea('right');
					$this->renderArea('footer');
				}
			}
		}
		function renderArea($area) {
			$target = (isset($this->page->mods[$area])) ? $this->page->mods[$area] : '';
			if (!empty($target)) {
				$_mods = is_array($target) ? $target : explode('|',$target);
				foreach($_mods as $mod) {
					$mod_caption = '';
					$mod_corners = false;
					$mod_teaser = '';
					$mod_cstyles = '';
					if (substr($mod,0,4) == 'mod:') {
						$mod = substr($mod,4);
						$modfile = $this->base."/lib/mod/$mod/mod.$mod.php";
						if (is_file($modfile)) {
							if (!isset($GLOBALS['xoxmod'][$mod])) $GLOBALS['xoxmod'][$mod] = array('_self'=>'');
							include $modfile;
							if (is_object($GLOBALS['xoxmod'][$mod]['_self'])) {
								$mod_caption = $GLOBALS['xoxmod'][$mod]['_self']->title;
								$mod_corners = $GLOBALS['xoxmod'][$mod]['_self']->corners;
								if (isset($GLOBALS['xoxmod'][$mod]['render_mode'])) {
									$mod_teaser = $GLOBALS['xoxmod'][$mod]['_self']->getDefault();
							 	} else {
									switch ($area) {
										case 'left':
											$mod_teaser = $GLOBALS['xoxmod'][$mod]['_self']->getCompact();
											break;
										case 'right':
											$mod_teaser = $GLOBALS['xoxmod'][$mod]['_self']->getTeaser();
											break;
										case 'header':
											$mod_teaser = $GLOBALS['xoxmod'][$mod]['_self']->getHeader();
											break;
										case 'footer';
											$mod_teaser = $GLOBALS['xoxmod'][$mod]['_self']->getFooter();
											break;
										default:
											$mod_teaser = $GLOBALS['xoxmod'][$mod]['_self']->getDefault();
											break;
									}
								}
								$mod_cstyles = $GLOBALS['xoxmod'][$mod]['_self']->cstyles;
							}
						} else {
							$mod_teaser = "Unknown MOD $modfile";
						}
					} elseif ( is_file($mod) ) {
						$mod_teaser = file_get_contents($mod);
					} else {
						$mod_teaser = $mod;
					}
					if ($this->corners || $mod_corners) {
						$this->output[$area] .= wrapCorners($mod_caption,$mod_teaser,$mod_cstyles).'<br />';
					} else {
						$this->output[$area] .= wrapBox($mod_caption,$mod_teaser,$mod_cstyles).'<br />';
					}
				}
			}
		}

		/** set the configuration for the modules
		 @param mod the name of the module
		 @param aname the variables name or an array of key=>values
		 @param var the value of the variable
		 */
		function setConfig( $mod, $aname, $var='_XOX_NO_VAR_' ) {
			if ( !empty($mod) && !empty($aname) ) {
				if (!isset($GLOBALS['xoxmod'][$mod])) $GLOBALS['xoxmod'][$mod] = array();
				if ( $var=='_XOX_NO_VAR_' ) {
					if ( is_array($aname)||is_object($aname) )
  					foreach ($aname as $name=>$value) $GLOBALS['xoxmod'][$mod][$name] = $value;
					elseif ( !empty($GLOBALS[$aname]) )
						$GLOBALS['xoxmod'][$mod][$aname] = $GLOBALS[$aname];
					else
						$GLOBALS['xoxmod'][$mod][$aname] = '';
				} else {
					$GLOBALS['xoxmod'][$mod][$aname] = "$var";
				}
			}
		} // setVar

		// get variables for template rendering
		function getVars() {
			return array(
				'header'	=> $this->output['header'],
				'left'		=> $this->output['left'],
				'right'		=> $this->output['right'],
				'footer'	=> $this->output['footer']
			);
		}

		/** reset all values.
		 */
		function reset() {
			$this->page = null;
			$this->output = array(
				'header' => '',
				'left' => '',
				'right' => '',
				'footer' => ''
			);
			$this->mods = array(
				'header' => array(),
				'left' => array(),
				'right' => array(),
				'footer' => array()
			);
			$this->corners = false;
			$this->base = '';
		} // reset

	}	// finish class xoxModHandler

?>
