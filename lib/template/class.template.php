<?php

  //=== template handling ======================================================

	/**	this is the base class for this template engine.
	 the actual template implementations are derived of this class
	 @author <a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
	*/
	class xoxBaseTemplate
	{
		var $template;
		var $pageurl;
		var $vars;
		var $parsed_template;

		var $starttag;
		var $endtag;

		var $autostripslashes;
		var $autohtquote;

		/** construct base template with given parameters
		 @param pageurl the url of the page for the content part
		 @param template the path to the template file
		 @param css the path to the stylesheet
		 @param starttag the start tag for variable replacements
		 @param endtag the end tag for variable replacements
		 */
		function xoxBaseTemplate($pageurl='',$template='',$css='', $starttag='%', $endtag='%', $autostripslashes=false, $autohtquote=false) {

			// set defaults
			$template_mask = ((empty($GLOBALS['xox_template_mask']))?'templates/*.tmpl':$GLOBALS['xox_template_mask']);
			$template_default = ((empty($GLOBALS['xox_template_default']))?'default':$GLOBALS['xox_template_default']);

			$this->pageurl = $pageurl;

			// build complete path to template
  		if ( !ereg('^..+://+|^(\.\.)?/',$template_mask) && XOX_APP_BASE!='' )
				$template_mask = XOX_APP_BASE."/$template_mask";

			// check for wildpattern in template mask
			if(ereg('/',$template)||substr($template,0,1)=='<')
				$this->template = $template;
			elseif(ereg('\*',$template_mask))
				$this->template = sprintf(str_replace('*','%s',$template_mask),(($template)?$template:$template_default));
  		else
				$this->template = "$template_mask/".(($template)?$template:$template_default);

			$this->starttag = $starttag;
			$this->endtag = $endtag;
			$this->autostripslashes = $autostripslashes;
			$this->autohtquote = $autohtquote;

			$this->vars = array();

  		// set replace variables
			$this->vars['html_base'] = XOX_WWW_BASE.'/';
			$this->vars['html_title'] = ((empty($GLOBALS['xox_html_title']))?'&nbsp;':$GLOBALS['xox_html_title']);
			$this->vars['html_description'] = ((empty($GLOBALS['xox_html_description']))?'':$GLOBALS['xox_html_description']);
			$this->vars['html_keywords'] = ((empty($GLOBALS['xox_html_keywords']))?'':$GLOBALS['xox_html_keywords']);
  		$this->vars['html_css'] = ((!empty($css))?$css:((empty($GLOBALS['xox_html_css']))?'style.css':$GLOBALS['xox_html_css']));
			$this->vars['app_version'] = XOX_APP_VERSION;

			$this->parsed_template = '';
		}

		/** set the replacement variables values
		 @param aname the variables name or an array of key=>values
		 @param var the value of the variable
		 */
		function setVar( $aname, $var='_XOX_NO_VAR_' ) {
			if ( !empty($aname) ) {
				if ( $var=='_XOX_NO_VAR_' ) {
					if ( is_array($aname)||is_object($aname) )
  					foreach ($aname as $name=>$value) $this->vars[$name] = $this->fixVal($value);
					elseif ( !empty($GLOBALS[$aname]) )
						$this->vars[$aname] = $this->fixVal($GLOBALS[$aname]);
					else
						$this->vars[$aname] = '';
				} else {
					if ( is_array($var)||is_object($var) )
  						foreach ($var as $name=>$value) $this->vars[$name] = $this->fixVal($value);
  					else
						$this->vars[$aname] = $this->fixVal("$var");
				}
			}
		} // setVar

		function fixVal($value) {
			$value = ($this->autostripslashes) ? stripslashes($value) : $value;
			$value = ($this->autohtquote) ? htmlspecialchars($value) : $value;
			return $value;
		}

		/** render template.
		 @param get flag if content should be returned
		 @return true or the rendered template
		 */
		function show($get=FALSE) {
			return TRUE;
		} // show

	}	// finish class xoxBaseTemplate

	/*****************************************************************
		xoxSimpleTemplate
	*****************************************************************/
	/**	this is a simple template implementation.
	 this template uses html template files and renders any html or
	 php source. if php content has to be rendered, then the template
	 automatically renders anything before the <code>%content%</code> tag
	 then executes the php content and finally renders the rest of the template
	 @author <a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
	*/
	class xoxSimpleTemplate extends xoxBaseTemplate
	{
		/** read template file */
		function readTemplate() {

			if (substr($this->template,0,1)=='<') {
				$this->parsed_template = $this->template;
				return TRUE;
			}

  		// read entire template from disk
  		if($fh=@fopen($this->template,'r')) {
				$this->parsed_template = fread($fh,filesize($this->template));
				fclose($fh);
				return TRUE;
			}

			echo("FATAL ERROR! Can't read template ".$this->template);
			return FALSE;

		} // readTemplate

		/** read content file */
		function readContent() {

			if (!(empty($this->pageurl) || eregi('^..+://+|^\.+/|.php(\?(.*))?$',$this->pageurl))) {
			  if(empty($this->vars['content'])){
			    // attempt to load page url
			    $this->vars['content'] = 'Page not found '.$this->pageurl;
					$pos = strpos($this->pageurl,'?');
					if ( $pos === false ) $pos = strlen($this->pageurl);
					$pageurl = substr($this->pageurl,0,$pos);
			    if ( function_exists('file_get_contents') ) {
						$this->vars['content'] = file_get_contents($pageurl);
					} else {
						if($fh=@fopen($pageurl,'r')){
			    		$this->vars['content']=fread($fh,filesize($pageurl));
			    		fclose($fh);
							return FALSE;
						}
					}
			  }
			}

			return TRUE;

		} // readPage


		/** render template.
		 @param get flag if content should be returned
		 @return true or the rendered template
		 */
		function show($get=FALSE) {

			// check pageurl
			#if ( empty($this->pageurl) )
			#	return FALSE;

  		// check if page url is external
  		if (eregi('^..+://+|^\.+/',$this->pageurl)) {
  		  header('location:'.$this->pageurl);
  		  echo '<html><body>Klicken Sie <a href="'.$this->pageurl.'">hier</a> um auf die gew&uuml;nschte Seite zu gelangen.</body></html>';
  		  return TRUE;
  		}

			// check template
			if ( empty($this->parsed_template) && (!$this->readTemplate()) ) {
  			echo '<p>FATAL ERROR: Could not render Template:</p>';
				echo $this->template.'<br>';
				echo $this->pageurl.'<br>';
  			echo '<p>'.$_SERVER['HTTP_USER_AGENT'].'</p>';
				return FALSE;
			}

			// read page
			$this->readContent();

  		// copy to preg params
  		$regs=$values=array();
  		foreach ($this->vars as $name=>$value) {
			if ( is_array($value)||is_object($value) ) {
				foreach ($value as $nam=>$val) {
					$regs[] = '@'.$this->starttag.$nam.$this->endtag.'@';
					$values[] = ''.$val;
				}
			} else {
				$regs[] = '@'.$this->starttag.$name.$this->endtag.'@';
				$values[] = ''.$value;
			}
  		}

  		// transform template, remove unknown variables and output result
  		$this->result = (!count($regs)) ? $this->parsed_template : preg_replace($regs,$values,$this->parsed_template);
			$this->insertat = strpos($this->result,$this->starttag."content".$this->endtag);
			if ( $this->insertat === false ) $this->insertat = strpos($this->result,$this->starttag."CONTENT".$this->endtag);

			#$unknown_match = '\s*([a-zA-Z0-9_-]+)\s*';
			$unknown_match = '([a-zA-Z0-9]?[a-zA-Z0-9_-]*[a-zA-Z0-9])';
			if ( $get || $this->insertat === false ) {
				$this->result = preg_replace('@'.$this->starttag.$unknown_match.$this->endtag.'@sm', "", $this->result);
				if ($get) return $this->result;
			  echo $this->result;
			} else {
			  echo preg_replace('@'.$this->starttag.$unknown_match.$this->endtag.'@sm', "", substr($this->result,0,$this->insertat));
				if ( !empty($this->pageurl) ) {
					$url = explode('?',$this->pageurl);
					if ( !empty($url[1]) ) {
						$pars = explode('&',$url[1]);
						foreach ( $pars as $par ) {
							$p = explode('=',$par);
							if ( isset($p[1]) ) {
								$GLOBALS[$p[0]] = urldecode($p[1]);
							}
						}
					}
					include($url[0]);
				}
			  echo preg_replace('@'.$this->starttag.$unknown_match.$this->endtag.'@sm', "", substr($this->result,$this->insertat+9));
			}

			return TRUE;

		} // show

	}	// finish class xoxSimpleTemplate

	/*****************************************************************
		xoxXMLTemplate
	*****************************************************************/
	/**	this is an xml/xslt based template implementation.
	 this template uses xslt template files and renders any xml source.
	 @author <a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
	*/
	class xoxXMLTemplate extends xoxBaseTemplate
	{
		/** render template.
		 @param get flag if content should be returned
		 @return true or the rendered template
		 */
		function show($get=FALSE) {

			// check pageurl
			if ( empty($this->pageurl) || empty($this->template) )
				return FALSE;

			// process template
      if ( function_exists('xslt_create') )
      {
        $parser = xslt_create();
        $result = xslt_process($parser, $this->pageurl, $this->template, NULL, array(), $this->vars );
        xslt_free($parser);
      } else {
        // Load the XML source
        $xml = new DOMDocument;
        $xml->load($this->pageurl);

        $xsl = new DOMDocument;
        $xsl->load($this->template);

        // Configure the transformer
        $proc = new XSLTProcessor;
        $proc->importStyleSheet($xsl); // attach the xsl rules

        $result = $proc->transformToXML($xml);
      }

			// replace special chars
			$result = preg_replace(
				array('/�/','/�/','/�/','/�/','/�/','/�/','/�/','/�/'),
				array('&Auml;','&auml;','&Ouml;','&ouml;','&Uuml;','&uuml;','&szlig;','&euro;'),
				$result
			);

			if ( $get ) {
				return $result;
			} else {
			  echo $result;
			}

			return TRUE;

		} // show

	}	// finish class xoxXMLTemplate

?>
