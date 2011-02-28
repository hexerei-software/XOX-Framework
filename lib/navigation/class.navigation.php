<?php

  //=== CONSTANTS ==============================================================

  /** the parameter name for the navigation value in the query string */
	define('NAVIGATION_VAR','p');

	/** filename of websites navigation configuration file */
  define('NAVIGATION_CONFIG','www.xox.php');

  //=== CLASSES ================================================================

	/** class to handle navigation logic and display.
	 @author <a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
   */
	class xoxNavigation
	{
		/** array holding parts of the navigation string */
		var $am;
		/** base url of server */
		var $base;
		/** root page url with language parameter */
		var $root;
		/** url of current page */
		var $pageurl;
    /** last part of url without language*/
    var $pageid;
		/** the current page object */
		var $page;
		/** the top (parent) page object */
		var $top;
		/** array with pagenames up to level of current page */
		var $names;
		/** the navigation tree */
		var $navigation;
		/** parameters to pass through from the query string */
		var $passthru;
		/** user rights */
		var $rights;

		/** auto htmlencode text and title entries */
		var $autoencode	= FALSE;

		/** use static page names for readable urls */
		var $staticurls	= FALSE;


		/** constructor that initializes environment and reads navigation file
		 @param www path to the navigation file to use (if empty default name is used)
		 */
		function xoxNavigation($www='') {

			$this->staticurls = (defined('XOX_NAVIGATION_STATIC_URLS') && XOX_NAVIGATION_STATIC_URLS == true);

			// get arguments
  		$this->am = explode('/',((isset($_GET[NAVIGATION_VAR]))?$_GET[NAVIGATION_VAR]:XOX_DEFAULT_LANGUAGE.'/0/0/0'));
  		// first argument is web path
  		if(!isset($this->am[0])||empty($this->am[0]))$this->am[0]=XOX_DEFAULT_LANGUAGE;
  		// next three arguments are navigation
  		if(!isset($this->am[1])||empty($this->am[1]))$this->am[1]='0';
  		if(!isset($this->am[2])||empty($this->am[2]))$this->am[2]='0';
  		if(!isset($this->am[3])||empty($this->am[3]))$this->am[3]='0';

  		$GLOBALS['xox_language_id'] = $this->am[0];
      $this->pageid = $this->am[1] . "/" . $this->am[2] ."/" . $this->am[3];
			$www_navigation = array();

			if (empty($www)) $www = XOX_APP_BASE.'/'.$this->am[0]."/".NAVIGATION_CONFIG;

  		// read navigation file
			if(file_exists($www)) require_once($www);
  		else die("FATAL ERROR! Cannot find configuration file! $www");

  		// check navigation array
  		if(count($www_navigation)<1) die('FATAL ERROR! Empty Navigation');

			$this->navigation = $www_navigation;

			if (!empty($www_title)) 						$GLOBALS['xox_html_title'] = $www_title;
			if (!empty($www_description))				$GLOBALS['xox_html_description'] = $www_description;
			if (!empty($www_keywords))					$GLOBALS['xox_html_keywords'] = $www_keywords;
			if (!empty($www_default_css)) 			$GLOBALS['xox_html_css'] = $www_default_css;
			if (!empty($www_template_mask)) 		$GLOBALS['xox_template_mask'] = $www_template_mask;
			if (!empty($www_default_template)) 	$GLOBALS['xox_template_default'] = $www_default_template;

			if (!empty($www_html_autoencode)) 	$this->autoencode = $www_html_autoencode;

  		// adjust page name if default

			$this->base = XOX_WWW_BASE.'/';
			$this->root = (XOX_INDEX_DEFAULT||$this->staticurls) ? XOX_WWW_BASE.'/' : XOX_WWW_PAGE;
  		if (!$this->staticurls) $this->root.= "?".NAVIGATION_VAR.'=';
  		$this->root.= $this->am[0].'/';
			$this->passthru = array();
			$this->rights = (isset($GLOBALS['user_rights']))?$GLOBALS['user_rights']:array(0);

			$this->getPage();

  		$this->pageurl = $this->root.$this->am[1].'/'.$this->am[2].'/'.$this->am[3];
  		if ($this->staticurls) $this->pageurl.= '.html';
		}

		/** add variable to pass through all links
		 @param name the name of the pass through parameter to add
		 @param value the value of the parameter to pass through
		 (if empty the value will be determined from globals, get and post variables)
		 */
		function addPassthru($name, $value='_USE_GLOBAL_DEFINITION_') {
			if ( !empty($name) ) {
				if ( $value=='_USE_GLOBAL_DEFINITION_' ) {
					// merge globals, get and post vars
      		$a=array_merge($GLOBALS,$_GET,$_POST);
      		$value=(isset($a[$name]))?$a[$name]:'';
				}
				$this->passthru[$name] = $value;
			}
		}

		/** get passthru values adds the pass through parameters and values to the given url
		 @param url the url to which pass through parameters should be added.
		 @return the url with the parameters and values added as urlencoded query string
		 */
		function getPassthru($url='') {
			$pt = $url;
			$pt.=((!empty($pt)||$this->staticurls) && strpos($pt,'?')===false )?'?':'&';
			foreach($this->passthru as $name=>$value) {
				if (!empty($value))
					$pt .= urlencode($name).'='.urlencode($value).'&';
			}
			return substr($pt,0,-1);
		}

		/** return the navigation page either by name or by key
		 @param parent the parent navigation root or page to search in
		 @param key index or name of the page to aquire
		 @param level optional level for duplicate name entries
		 @return page object
		 */
		function findPage($parent='',$key='',$level=0)
		{
			// we need a parent (navigation root or page) to search in
			if ( empty($parent) || !is_array($parent) ) {
				return false;
			}

			// good old key style
			if(is_numeric($key)){
				// make sure key is a valid index and return page
				if ( $key >= count($parent) ) { $key=0; if ($level) $this->am[$level]=0; }
				return $parent[$key];

			// fancy new name style - thanks sep ;)
			} else {
				// search by name and return if found
				$am_counter = 0;
				foreach($parent as $entry){
					if($entry->name == $key){
						if ($level) $this->am[$level] = $am_counter;
						return $entry;
					}
					$am_counter++;

				}
				// return default page
				if ($level) $this->am[$level]=0;
		  		return $parent[0];
			}

			return false;

		}  //  getAssocPage

		/** get page by key or name respecting the rights
		 returns page object (default page if rights fail)
		 or empty (false) on empty navigation or no rights at all
		 @param n1 first level of navigation
		 @param n2 second level of navigation
		 @param n3 third level of navigation
		 @return page object
		 */
		function getPage($n1='',$n2='',$n3='') {

			// set defaults
			if ($n1=='') $n1=$this->am[1];
			if ($n2=='') $n2=$this->am[2];
			if ($n3=='') $n3=$this->am[3];

			// get group and page
			$this->page = false;
			$this->top = false;
			$this->names = array();

			$fpage = $this->findPage($this->navigation,$n1,1);
			// check if page found and right for level 1
			if ( $fpage && $this->hasRight($fpage->right) ) {

				$this->names[] = $fpage->name;
				$this->page = $fpage;
				$this->top = $this->page;
				$fpage = $this->findPage($this->page->page,$n2,2);

				// check if page found and right for level 2
				if ( $fpage && $this->hasRight($fpage->right) ) {

					$this->names[] = $fpage->name;
					$this->top = $this->page;
					$this->page = $fpage;
					$fpage = $this->findPage($this->page->page,$n3,3);

					// check if page found and right for level 3
					if ( $fpage && $this->hasRight($fpage->right) ) {
						$this->names[] = $fpage->name;
						$this->top = $this->page;
						$this->page = $fpage;
					}
					// no page found or no right on level 3
					else {
						// default level 3
						if ( is_array($this->page->page) ) {
							if ( $this->hasRight($this->page->page[0]->right) ) {
								$this->names[] = $fpage->name;
								$this->top = $this->page;
								$this->page = $this->page->page[0];
							}
						}
					}
				// no page found or no right on level 2
				} else {
					// default level 2
					if ( is_array($this->page->page) ) {
						if ( $this->hasRight($this->page->page[0]->right) ) {
							$this->names[] = $fpage->name;
							$this->top = $this->page;
							$this->page = $this->page->page[0];
						}
					}
					// default level 3
					if ( is_array($this->page->page) ) {
						if ( $this->hasRight($this->page->page[0]->right) ) {
							$this->names[] = $fpage->name;
							$this->top = $this->page;
							$this->page = $this->page->page[0];
						}
					}
				}

			// no page found or no right on level 1
			} else {
				// default level 1
				if ( $this->hasRight($this->navigation[0]->right) ) {
					$this->names[] = $fpage->name;
					$this->page = $this->navigation[0];
					$this->top = $this->page;
				}
				// default level 2
				if ( is_array($this->page->page) ) {
					if ( $this->hasRight($this->page->page[0]->right) )
						$this->names[] = $fpage->name;
						$this->top = $this->page;
						$this->page = $this->page->page[0];
				}
				// default level 3
				if ( is_array($this->page->page) ) {
					if ( $this->hasRight($this->page->page[0]->right) )
						$this->names[] = $fpage->name;
						$this->top = $this->page;
						$this->page = $this->page->page[0];
				}
			}
			if ( !$this->top ) $this->top = $this->page;
			$this->group = ( $this->page ) ? $this->page->title : '';
			return $this->page;
		}

		/** returns an array with all template replacement variables from the navigation class
		 @return array with variables
		 */
		function getVars() {
			return array(
				'breadcrumb'		=> $this->getBreadcrumb(),
				'html_head'			=> $this->page->header,
				'navigation_0'	=> $this->getNavigation(0),
				'navigation_10'	=> $this->getNavigation(10),
  			'navigation_1'	=> $this->getNavigation(1),
  			'navigation_12'	=> $this->getNavigation(12),
  			'navigation_2'	=> $this->getNavigation(2),
  			'p'							=> $this->pageurl,
				'page_title'		=> $this->htmlPrepare($this->page->title),
				'page_text'			=> $this->htmlPrepare($this->page->text),
				'page_url'			=> $this->htmlPrepare($this->am[0].'/'.$this->page->entry),
        'page_id'       => $this->pageid,
				'top_title'			=> $this->htmlPrepare($this->top->title),
				'top_text'			=> $this->htmlPrepare($this->top->text),
				'top_url'				=> $this->htmlPrepare($this->am[0].'/'.$this->top->entry),
				'passthru'			=> $this->getPassthru(),
  			'root'					=> $this->root,
			);
		}

  	/** render navigation for given level.
		 @note currently the navigation is implemented wit a max of 3 levels
		 @param level the level to render (default 0 for root level)
		 */
  	function getNavigation( $level=0 ) {

			$nav = "";

			// copy passthru values
			$passthru = $this->getPassthru();

			/* -----------------27.05.2004 02:45-----------------
				TODO: decide if we want <strong> and no <a> when
			 	link is the current selected link (usability)
			 	also use of <span class="invisible">. <span>
			 	to seperate links failed in IE... check
			 --------------------------------------------------*/

  	  switch ($level) {
  	    case 0:
					$firstEntry = true;
          $nav = '';
  	      for ($i=0;$i<count($this->navigation);$i++) {
  	        $entry = $this->navigation[$i];
						if ( !empty($entry->title) && $this->hasRight($entry->right) ) {
							$title = (substr($entry->title,0,1)=='^') ? '<small style="font-size:70%;">'.$this->htmlPrepare(substr($entry->title,1)).'</small>' : $this->htmlPrepare($entry->title);
							$name = ($this->staticurls) ? $entry->name.'.html' : $i;
							$nav.='<li'.($firstEntry?' class="first"':'').'><a title="'.$this->htmlPrepare($entry->text).'"  href="'.$this->root.$name.$passthru
							.(($entry->target) ? '" target="'.$entry->target : '')
							.'"'.(($this->am[1]==$i)?' class="selected">':'>').$title.'</a></li>';
							$firstEntry = false;
						}
  	      }
          if ( !empty($nav) ) $nav = '<ul id="topmenu">'.$nav.'</ul>';
  	      break;

  	    case 1:
					$firstEntry = true;
					$parentname = ($this->staticurls) ? $this->navigation[$this->am[1]]->name : $this->am[1];
          $nav='';
  	      if ( is_array($this->navigation[$this->am[1]]->page) ) {
    	      for ($i=0;$i<count($this->navigation[$this->am[1]]->page);$i++) {
    	        $entry = $this->navigation[$this->am[1]]->page[$i];
  						if ( !empty($entry->title) && $this->hasRight($entry->right)  ) {
								$name = ($this->staticurls) ? $entry->name.'.html' : $i;
    	        	$nav.='<li'.($firstEntry?' class="first"':'').'><a title="'.$this->htmlPrepare($entry->text).'" href="'.$this->root.$parentname.'/'.$name.$passthru
								.(($entry->target) ? '" target="'.$entry->target : '')
								.'"'.(($this->am[2]==$i)?' class="selected">':'>').$this->htmlPrepare($entry->title).'</a></li>';
								$firstEntry = false;
  						}
    	      }
  	      }
          if ( !empty($nav) ) $nav = '<ul id="navmenu">'.$nav.'</ul>';
  	      break;

  	    case 2:
					$firstEntry = true;
          $nav='';
  	      if ( is_array($this->navigation[$this->am[1]]->page) ) {
    	      $entry = $this->navigation[$this->am[1]]->page[$this->am[2]];
    	      if ( is_array($entry->page) ) {
    	        for ($j=0;$j<count($entry->page);$j++) {
  							if ( !empty($entry->page[$j]->title) && $this->hasRight($entry->right) ) {
									$name = ($this->staticurls) ? $entry->name.'.html' : $j;
    	        	  $nav.='<li'.($firstEntry?' class="first"':'').'><a title="'.$this->htmlPrepare($entry->page[$j]->text).'" href="'.$this->root.$this->am[1].'/'.$this->am[2].'/'.$name.$passthru
									.(($entry->page[$j]->target) ? '" target="'.$entry->page[$j]->target : '').'"'
    	        	  .(($this->am[3]==$j)?' class="selected">':'>').$this->htmlPrepare($entry->page[$j]->title).'</a></li>';
									$firstEntry = false;
    	        	}
    	        }
    	      }
    	    }
          if ( !empty($nav) ) $nav = '<ul id="submenu">'.$nav.'</ul>';
  	      break;

        /* -----------------27.05.2004 02:49-----------------
					TODO: we could use manualy numberd links using
				 	<dfn> tag for better readability by screenreaders
				 --------------------------------------------------*/
				case 12:
          $nav='';
          if ( is_array($this->navigation[$this->am[1]]->page) ) {
    	      for ($i=0;$i<count($this->navigation[$this->am[1]]->page);$i++) {
    	        $entry = $this->navigation[$this->am[1]]->page[$i];
  						if ( !empty($entry->title) && $this->hasRight($entry->right)  ) {
								$name = ($this->staticurls) ? $entry->name.'.html' : $i;
								$nav.='<li><a title="'.$this->htmlPrepare($entry->text).'" href="'.$this->root.$this->am[1].'/'.$name.$passthru
								.(($entry->target) ? '" target="'.$entry->target : '').'"'
								.(($this->am[2]==$i)?' class="selected">':'>').$this->htmlPrepare($entry->title).'</a></li>';
    	        	if ($this->am[2]==$i) {
   	    				  if ( is_array($entry->page) ) {
  	      					$nav.='<ul id="submenu">';
   	    				    for ($j=0;$j<count($entry->page);$j++) {
 										if ( !empty($entry->page[$j]->title) && $this->hasRight($entry->page[$j]->right) ) {
												$name = ($this->staticurls) ? $entry->page[$j]->name.'.html' : $j;
   	    				    	  $nav.='<li><a title="'.$this->htmlPrepare($entry->page[$j]->text).'" href="'.$this->root.$this->am[1].'/'.$this->am[2].'/'.$name.$passthru
												.(($entry->page[$j]->target) ? '" target="'.$entry->page[$j]->target : '').'"'
   	    				    	  .(($this->am[3]==$j)?' class="selected">':'>').$this->htmlPrepare($entry->page[$j]->title).'</a></li>';
   	    				    	}
   	    				    }
  	      					$nav.='</ul>';
    	    				}
								}
  						}
    	      }
  	      }
          if ( !empty($nav) ) $nav = '<ul id="navmenu">'.$nav.'</ul>';
  	      break;

				case 10:
          $nav='';
          if ( is_array($this->navigation) ) {
    	      for ($i=0;$i<count($this->navigation);$i++) {
    	        $entry = $this->navigation[$i];
  						if ( !empty($entry->title) && $this->hasRight($entry->right)  ) {
								$name = ($this->staticurls) ? $entry->name.'.html' : $i;
								$nav.='<li><a title="'.$this->htmlPrepare($entry->text).'" href="'.$this->root.$name.$passthru
								.(($entry->target) ? '" target="'.$entry->target : '').'"'
								.(($this->am[1]==$i)?' class="selected">':'>').$this->htmlPrepare($entry->title).'</a>';
   	    				if ( is_array($entry->page) ) {
  	      				$nav.='<ul id="submenu">';
   	    				  for ($j=0;$j<count($entry->page);$j++) {
 									if ( !empty($entry->page[$j]->title) && $this->hasRight($entry->page[$j]->right) ) {
											$name = ($this->staticurls) ? $entry->page[$j]->name.'.html' : $j;
   	    				    	$nav.='<li><a title="'.$this->htmlPrepare($entry->page[$j]->text).'" href="'.$this->root.$i.'/'.$name.$passthru
											.(($entry->page[$j]->target) ? '" target="'.$entry->page[$j]->target : '').'"'
   	    				    	.(($this->am[2]==$j && $this->am[1]==$i)?' class="selected">':'>').$this->htmlPrepare($entry->page[$j]->title).'</a></li>';
   	    				    }
   	    				  }
  	      				$nav.='</ul>';
								}
  	      			$nav.='</li>';
  						}
    	      }
  	      }
          if ( !empty($nav) ) $nav = '<ul id="navmenu">'.$nav.'</ul>';
  	      break;

  	  }

			return $nav;

  	}  // getNavigation

		/** get breadcrumb - current page navigation string showing all levels
		 @param sep the seperator to use between paths
		 @return html string to render breadcrumb
		 */
		function getBreadcrumb($sep=' &#187; ') {

			$bread = array();

			// copy passthru values
			$passthru = $this->getPassthru();

			if ( is_array($this->navigation) ) {
				$entry = $this->navigation[$this->am[1]];
				#$name = ($this->staticurls) ? $entry->name.'.html' : $i;

				if ( $this->hasRight($entry->right) ) {
					$bread[] = array('link'=>'<a href="'.$this->root.$this->am[1].(($this->staticurls)?'.html':'').$passthru.(($entry->target) ? '" target="'.$entry->target : '').'" title="'.$this->htmlPrepare($entry->text).'">%s</a>','title'=>$this->htmlPrepare(empty($entry->title)?$entry->text:(substr($entry->title,0,1)=='^'?substr($entry->title,1):$entry->title)));
					if ( is_array($entry->page) ) {
						$entry = $entry->page[$this->am[2]];
						if ( $this->hasRight($entry->right) ) {
							$bread[] = array('link'=>'<a href="'.$this->root.$this->am[1].'/'.$this->am[2].(($this->staticurls)?'.html':'').$passthru.(($entry->target) ? '" target="'.$entry->target : '').'" title="'.$this->htmlPrepare($entry->text).'">%s</a>','title'=>$this->htmlPrepare(empty($entry->title)?$entry->text:$entry->title));
							if ( is_array($entry->page) ) {
								$entry = $entry->page[$this->am[3]];
								if ( $this->hasRight($entry->right) ) {
									$bread[] = array('link'=>'<a href="'.$this->root.$this->am[1].'/'.$this->am[2].(($this->staticurls)?'.html':'').'/'.$this->am[3].$passthru.(($entry->target) ? '" target="'.$entry->target : '').'" title="'.$this->htmlPrepare($entry->text).'">%s</a>','title'=>$this->htmlPrepare(empty($entry->title)?$entry->text:$entry->title));
								}
							}
						}
					}
				}
			}
			$depth = count($bread)-1;
			for ( $i=0; $i<=$depth; $i++ ) {
				$bread[$i] = ($i==$depth) ? '<strong>'.$bread[$i]['title'].'</strong>' : sprintf($bread[$i]['link'],$bread[$i]['title']);
			}
			return implode($sep,$bread);

		}	// getBreadcrumb

  	/** get navigation as tree (for sitemap)
		 @param title the title to display above the navigation tree
		 @param h
		 @return html representation of navigation tree
		 */
  	function getNavigationTree($title='',$h=0) {

			$nav = '<div id="sitemap">';
			$nav .= ($title) ? '<div class="mapcaption">'.$title.'</div>' : '';

			// copy passthru values
			$passthru = $this->getPassthru();

    	for (;$h<count($this->navigation);$h++) {
    		$top = $this->navigation[$h];
  			if ( !empty($top->title) && $this->hasRight($top->right) && substr($top->title,0,1)!='^' ) {
				$nav.='<h3>'.$this->htmlPrepare($top->title).'</h3>';
      		if ( is_array($top->page) ) {
						$nav.='<ul id="mapnav">';
    			  for ($i=0;$i<count($top->page);$i++) {
    			    $entry = $top->page[$i];
  						if ( !empty($entry->title) && $this->hasRight($entry->right)  ) {
								$nav.='<li><a title="'.$this->htmlPrepare($entry->text).'" href="'.$this->root.$h.'/'.$i.$passthru
								.(($entry->target) ? '" target="'.$entry->target : '').'"'
								.(($this->am[2]==$i)?' class="selected">':'>').'<b>'.$this->htmlPrepare($entry->title).'</b><br />'.$this->htmlPrepare($entry->text).'</a></li>';
  	  		    	$nav.='<ul id="mapsub">';
   	  		  		if ( is_array($entry->page) ) {
   	  		  			for ($j=0;$j<count($entry->page);$j++) {
 									if ( !empty($entry->page[$j]->title) && $this->hasRight($entry->right) ) {
   	  		  				  $nav.='<li><a title="'.$this->htmlPrepare($entry->page[$j]->text).'" href="'.$this->root.$h.'/'.$i.'/'.$j.$passthru
											.(($entry->page[$j]->target) ? '" target="'.$entry->page[$j]->target : '').'"'
   	  		  				  .(($this->am[3]==$j)?' class="selected">':'>').'<b>'.$this->htmlPrepare($entry->page[$j]->title).'</b><br />'.$this->htmlPrepare($entry->page[$j]->text).'</a></li>';
   	  		  				}
   	  		  			}
    			    	}
  	  		    	$nav.='</ul>';
  						}
    			  }
  	  			$nav.='</ul>';
  	  		} else {
						$nav.='<p><a title="'.$this->htmlPrepare($top->page->text).'" href="'.$this->root.$h.$passthru
						.(($top->page->target) ? '" target="'.$top->page->target : '').'"'
						.(($this->am[1]==$h)?' class="selected">':'>').'<b>'.$this->htmlPrepare($top->page->title).'</b><br />'.$this->htmlPrepare($top->page->text).'</a></p>';
					}
				}
			}
  	  $nav.='</div>';

			return $nav;

  	}  // getNavigationTree

  	/** get navigation as javascript enabled tree
		 same as tree above, only collapsable using javascript
		 @param title the title to display above the navigation tree
		 */
  	function getNavigationJSTree($title='') {

			$nav = ($title) ? '<div class="root">'.$title.'</div>' : '';

  	  for ($h=0;$h<count($this->navigation);$h++) {
  	    $entry = $this->navigation[$h];
				if ( !empty($entry->title) && $this->hasRight($entry->right) ) {

					// 1st level parent
  	  		if ( is_array($entry->page) ) {
						$nav.='<div class="parent" id="'.$h.'_parent"><nobr>';
						$nav.='<img name="imEx" src="images/tree_open.gif" border="0" class="item" onclick="expandIt(\''.$h.'\'); return false;" alt="+" width="16" height="16" id="'.$h.'_image"></img>';
						$nav.='<a class="item" title="'.$this->htmlPrepare($entry->text).'"  href="'.$this->root.$h.(($entry->target) ? '" target="'.$entry->target : '').'">';
						if ($this->am[1]==$h) $nav.= '<span style="background:yellow;">'.$this->htmlPrepare($entry->title).'</span>';
						else $nav.= $this->htmlPrepare($entry->title);
						$nav.='</a></nobr></div><div class="child" id="'.$h.'_children">';

    				for ($i=0;$i<count($this->navigation[$h]->page);$i++) {
    					$entry = $this->navigation[$h]->page[$i];
  						if ( !empty($entry->title) && $this->hasRight($entry->right) ) {
								// 2nd level parent
  	  					if ( is_array($entry->page) ) {
									$nav.='<div class="parent" id="'.$h.'_'.$i.'_parent"><nobr>';
									$nav.='<img src="images/tree_blank.gif" class="item" border="0" width="16" height="16" align="left">';
									$nav.='<img name="imEx" src="images/tree_open.gif" border="0" class="item" onclick="expandIt(\''.$h.'_'.$i.'\'); return false;" alt="+" width="16" height="16" id="'.$h.'_image"></img>';
									$nav.='<a class="item" title="'.$this->htmlPrepare($entry->text).'"  href="'.$this->root.$h.'/'.$i
											.(($entry->target) ? '" target="'.$entry->target : '').'">';
									if ($this->am[1]==$h && $this->am[2]==$i) $nav.= '<span style="background:yellow;">'.$this->htmlPrepare($entry->title).'</span>';
									else $nav.= $this->htmlPrepare($entry->title);
									$nav.='</a></nobr></div>';
									$nav.='<div class="child" id="'.$h.'_'.$i.'_children">';

    							for ($j=0;$j<count($entry->page);$j++) {
  									if ( !empty($entry->page[$j]->title) && $this->hasRight($entry->page[$j]->right) ) {
											$nav.='<nobr><img src="images/tree_blank.gif" class="item" border="0" width="16" height="16" align="left">';
											$nav.='<img src="images/tree_blank.gif" class="item" border="0" width="16" height="16" align="left">';
											$nav.='<img src="images/tree_node.gif" class="item" border="0" width="16" height="16" align="left">';
											$nav.='<a class="item" title="'.$this->htmlPrepare($entry->page[$j]->text).'" href="'.$this->root.$h.'/'.$i.'/'.$j
											.(($entry->page[$j]->target) ? '" target="'.$entry->page[$j]->target : '').'">';
											if ($this->am[1]==$h && $this->am[2]==$i && $this->am[3]==$j) $nav.= '<span style="background:yellow;">'.$this->htmlPrepare($entry->page[$j]->title).'</span>';
											else $nav.= $this->htmlPrepare($entry->page[$j]->title);
											$nav.='</a></nobr>';
    								}
									}

									$nav.='</div>';

								// 2nd level node
								} else {
									$nav.='<nobr><img src="images/tree_blank.gif" class="item" border="0" width="16" height="16" align="left">';
									$nav.='<img src="images/tree_node.gif" class="item" border="0" width="16" height="16" align="left">';
									$nav.='<a class="item" title="'.$this->htmlPrepare($entry->text).'" href="'.$this->root.$h.'/'.$i
									.(($entry->target) ? '" target="'.$entry->target : '').'">';
									if ($this->am[1]==$h && $this->am[2]==$i) $nav.= '<span style="background:yellow;">'.$this->htmlPrepare($entry->title).'</span>';
									else $nav.= $this->htmlPrepare($entry->title);
									$nav.='</a></nobr><br />';
								}
  						}
    				}

						$nav.='</div>';

					// 1st level node
					} else {
						$nav.='<nobr><a class="item" title="'.$this->htmlPrepare($entry->text).'"  href="'.$this->root.$h
						.(($entry->target) ? '" target="'.$entry->target : '')
						.'">'.$this->htmlPrepare($entry->title).'</a></nobr>';
					}
				}
  	  }

			return $nav;

  	}  // getNavigationJSTree

		/** convert text to htmlentities (only when autoencode is true)
		 @param text the text to convert
		 @return the converted (or unconverted if autoencode is false) text
		 */
		function htmlPrepare($text) {
			return ( $this->autoencode ) ? htmlentities($text) : $text;
		}	// htmlPrepare

		/** check if user has right to view the page.
		 this function checks if the given right exists in the local
		 array of rights which is usually set to the logged in users rights,
		 or can be set to a fixed value.
		 @param right the right to check for
		 @return true if page may be viewed, else false
		 */
		function hasRight($right=0) {
			if ( $right==0 || empty($right) || in_array($right,$this->rights) ) {
				return TRUE;
			}
			return FALSE;
		}

	}	// finish class xoxNavigation

	/** a page or a group containing pages.
	 this class holds all attribute of a page in the navigation tree.
	 the information is used for access and rendering of the content.
	 @author <a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
	*/
  class nav_entry {

		/** distinct name of the page - should be unique */
		var $name;
		/** the title of the page used for display in navigation */
    var $title;
		/** text of the page, usually used as tooltip for the navigation link */
    var $text;
		/** holds the unparsed entries passed by user */
    var $entry;
		/** holds children of this page, or empty if no children exist */
    var $page;
		/** the target url of the content of the page */
    var $url;
		/** the target window to open link in (default _self) */
    var $target;
		/** the right needed to access this page */
    var $right;
		/** the stylesheet to use for this page */
		var $css;
		/** the template to use for rendering this page */
		var $template;
		/** additional headers to render */
		var $header;
		/** pagemodules to prerender */
		var $mods;
		/** can this page be edited */
		var $editable;

    /** construct new nav entry with given parameters.
		 <b>variable entry parameter</b>
		 - if the entry is an array of pages, then the array will be used as children
		   and the first pages url as default url.
		 - if entry is an object, then the current page has this entry as one child
		   and its url will be default.
		 <b>variable url parameter</b>
		 - if the url starts with an <code>#</code> the link will be opened in
		   <code>_blank</code> window.
		 - if the url starts with a <code>./</code> path, or includes protocol
		   (i.e. <code>http://</code>) then the url will be taken unchanged.
		 - if the url starts with <code>~xox/</code> then the path to the xox
		   path is prepended to the url (works only if xox is in webpath)
		 - if the url starts with <code>~</code> then the path to the applications
		   root path is prepended to the url
		 - otherwise the complete application and language path is prepended

		 @param name distinct name of the page - should be unique
     @param title the title of the page used for display in navigation
     @param text text of the page, usually used as tooltip for the navigation link
     @param page holds children of this page, or empty if no children exist
     @param url the target url of the content of the page
     @param target the target window to open link in (default _self)
     @param right the right needed to access this page
		 @param css the stylesheet to use for this page
		 @param template the template to use for rendering this page
		 @param header additional headers to render
		 @param mods array with modules to prerender
		 */
		function nav_entry( $name='', $title='', $text='', $entry='', $right=0, $css='', $template='', $header='', $mods='' ) {
			$config = array(
      	'name' 			=> $name,
      	'title'			=> $title,
      	'text' 			=> $text,
				'entry'			=> $entry,
				'right' 		=> $right,
				'css'				=> $css,
				'template'	=> $template,
				'header' 		=> $header,
				'mods' 			=> $mods,
				'editable'	=> false
			);
			if (is_array($name)) $config = array_merge($config,$name);
			foreach($config as $key=>$value) $this->$key = $value;
			$this->target	= '';
      if ( is_array($this->entry) ) {
        $this->page = $this->entry;
        $this->url 	= $this->entry[0]->url;
      } else if ( is_object($this->entry) ) {
        $this->page = array($this->entry);
        $this->url 	= $entry->url;
      } else {
        $this->page = '';
				// check for target
				if ( ereg('^#',$this->entry) ) {
					$this->target = '_blank';
					$this->entry	= substr($this->entry,1);
				}
				// build complete path to page
				$this->url 	= $this->buildPath($this->entry);
      }
    }

		function buildPath($entry) {
			$path = '';
			$this->editable = false;
			if ( ereg('^.+:/+|^\.+/',$entry) || XOX_APP_BASE=='' ) {
        $path = $entry;
			} else {
				if ( ereg('^~xox/',$entry) ) {
					$path = XOX.substr($entry,4);
				} elseif ( ereg('^~', $entry) ) {
					$path = XOX_APP_BASE.'/'.substr($entry,1);
				} else {
					$path = XOX_APP_BASE.'/';
					$path .= ((empty($GLOBALS['xox_language_id']))? XOX_DEFAULT_LANGUAGE : $GLOBALS['xox_language_id'])."/$entry";
					$this->editable = (strtolower(substr($entry,-5)) == '.html');
				}
			}
			return $path;
		}
  } // finish class nav_entry

?>
