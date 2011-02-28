<?php

	if (!isset($GLOBALS['xoxmod'])) $GLOBALS['xoxmod'] = array();

	define('XOX_MOD_RENDER_CONTENT', 	0);
	define('XOX_MOD_RENDER_PREVIEW', 	1);
	define('XOX_MOD_RENDER_TEASER', 	2);
	define('XOX_MOD_RENDER_COMPACT', 	3);
	define('XOX_MOD_RENDER_HEADER', 	4);
	define('XOX_MOD_RENDER_FOOTER', 	5);
	define('XOX_MOD_RENDER_DEFAULT', 	-1);

  //=== template module ============================================

	/**	this is the base class for template modules.
	 modules are template units, that are pre-rendered to the content areas
	 @author <a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
	*/
	class xoxModule
	{
		var $name;
		var $title;

		var $corners;
		var $cstyles;
		var $base;

		var $_content;
		var $_preview;
		var $_teaser;
		var $_compact;
		var $_header;
		var $_footer;
		var $_default;
		var $_vars;

		/** empty constructor
		 */
		function xoxModule($name='sample') {
			$this->reset();
			$this->name = $name;
      if (!isset($GLOBALS['xoxmod']["$this->name"])) $GLOBALS['xoxmod']["$this->name"] = array();
      $GLOBALS['xoxmod']["$this->name"]['_self'] = &$this;
		}

		function render($mode=XOX_MOD_RENDER_DEFAULT) {
			if ($mode==XOX_MOD_RENDER_DEFAULT
        && isset($GLOBALS['xoxmod']["$this->name"]['render_mode']))
        $mode = $GLOBALS['xoxmod']["$this->name"]['render_mode'];
			switch ($mode) {
				case XOX_MOD_RENDER_CONTENT:
					return "<h3>Sample Content</h3><hr /><p>This content should display in the center content area.</p>";
				case XOX_MOD_RENDER_PREVIEW:
					return "<h3>Sample Preview</h3><hr /><p>This content should display in a box or frame in content area.</p>";
				case XOX_MOD_RENDER_TEASER:
					return "<h3>Sample Teaser</h3><hr /><p>This content should display in the left or right page areas.</p>";
				case XOX_MOD_RENDER_COMPACT:
					return "<h3>Sample Compact</h3><hr /><p>This content should display in narrow left or right page areas.</p>";
				case XOX_MOD_RENDER_HEADER:
					return "<h3>Sample Header</h3><hr /><p>This content should display in the page header.</p>";
				case XOX_MOD_RENDER_FOOTER:
					return "<h3>Sample Footer</h3><hr /><p>This content should display in the page footer.</p>";
				case XOX_MOD_RENDER_DEFAULT:
				default:
					return "<h3>Sample Default</h3><hr /><p>This content should display whereever :)</p>";
			}
		}

		function getContent($refresh=false) {
			if ($refresh || empty($this->_content)) $this->_content = $this->render(XOX_MOD_RENDER_CONTENT);
			return $this->_content;
		}

		function getPreview($refresh=false) {
			if ($refresh || empty($this->_preview)) $this->_preview = $this->render(XOX_MOD_RENDER_PREVIEW);
			return $this->_preview;
		}

		function getTeaser($refresh=false) {
			if ($refresh || empty($this->_teaser)) $this->_teaser = $this->render(XOX_MOD_RENDER_TEASER);
			return $this->_teaser;
		}

		function getCompact($refresh=false) {
			if ($refresh || empty($this->_compact)) $this->_compact = $this->render(XOX_MOD_RENDER_COMPACT);
			return $this->_compact;
		}

		function getHeader($refresh=false) {
			if ($refresh || empty($this->_header)) $this->_header = $this->render(XOX_MOD_RENDER_HEADER);
			return $this->_header;
		}

		function getFooter($refresh=false) {
			if ($refresh || empty($this->_footer)) $this->_footer = $this->render(XOX_MOD_RENDER_FOOTER);
			return $this->_footer;
		}

		function getDefault($refresh=false) {
			if ($refresh || empty($this->_default)) $this->_default = $this->render(XOX_MOD_RENDER_DEFAULT);
			return $this->_default;
		}


		/** set the configuration for the module
		 @param aname the variables name or an array of key=>values
		 @param var the value of the variable
		 */
		function setVar( $aname, $var='_XOX_NO_VAR_' ) {
			if ( !empty($aname) ) {
				if ( $var=='_XOX_NO_VAR_' ) {
					if ( is_array($aname)||is_object($aname) )
  					foreach ($aname as $name=>$value) $this->_vars[$name] = $value;
					elseif ( !empty($GLOBALS[$aname]) )
						$this->_vars[$aname] = $GLOBALS[$aname];
					else
						$this->_vars[$aname] = '';
				} else {
					$this->_vars[$aname] = "$var";
				}
			}
		} // setVar

		/** reset all values.
		 */
		function reset() {
			$this->name = '';
			$this->title = '';
			$this->corners = false;
			$this->cstyles = '|';
			$this->base = '';
			$this->_teaser = '';
			$this->_preview = '';
			$this->_content = '';
			$this->_compact = '';
			$this->_header = '';
			$this->_footer = '';
			$this->_default = '';
			$this->_vars = array();
		} // reset

	}	// finish class xoxModule

?>
