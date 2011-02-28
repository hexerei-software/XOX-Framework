<?php

	/**
	* ht file writer
	*/
	class htFile
	{
		protected $path;
		protected $filename;
		protected $handle;
		protected $error;
		protected $content;
		protected $overwrite;
		
		public function __construct($filename,$path='',$overwrite=true)
		{
			$this->filename = $filename;
			$this->path = (empty($path)) ? dirname(__FILE__) : $path;
			$this->handle = false;
			$this->error = '';
			$this->content = '';
			$this->overwrite = $overwrite;
		}
		
		public function create($content) {
			$this->setContent($content);
			return $this->save();
		}
		
		public function append($content) {
			$this->appendContent($content);
			return $this->save();
		}
		
		public function setContent($content) {
			$this->content = $content;
		}
		
		public function appendContent($content) {
			$this->content .= $content;
		}
		
		public function read() {
			if (file_exists("$this->path/$this->filename")) {
				$this->content = file_get_contents("$this->path/$this->filename");
				return $this->content;
			}
			return '';
		}
		
		public function save($overwrite='_none_') {
			if ($overwrite=='_none_') $overwrite = $this->overwrite;
			if (is_dir($this->path) && !empty($this->filename)) {
				if ($this->handle = fopen("$this->path/$this->filename",($overwrite)?'w':'a')) {
					fwrite($this->handle,$this->content);
					fclose($this->handle);
					return true;
				}
			}
			return false;
		}
	}
	

	/**
	* htaccess writer
	*/
	class htAccess extends htFile
	{
		public function __construct($path='') {
			parent::__construct('.htaccess',$path,true);
		}
		
		public function create($pwdfile='.htpasswd', $path='', $name='', $user='', $group='', $grpfile='') {
			
			if (empty($name)) $name = 'Protected Environment';
			if (empty($path)) $path = (empty($this->path)) ? dirname(__FILE__) : $this->path;
			$this->path = $path;
			
			$content = 'AuthType Basic'."\n";
			$content.= 'AuthName "'.$name."\"\n";
			$content.= 'AuthUserFile '."$pwdfile\n";
			if (!empty($grpfile)) {
				$content.= 'AuthGroupFile '."$grpfile\n";				
				$content.= (empty($group)) ? 'require valid-user' : "require group $group";
			} else {
				$content.= (empty($user)) ? 'require valid-user' : "require user $user";
			}
			
			$this->setContent($content);
			return $this->save();
		}
		
		public function add($user) {
			
			if ($this->read()) {
				$this->appendContent(" $user");
				return $this->save();
			}
			return false;
		}
		
	}
	
	/**
	* htpasswd writer
	*/
	class htPasswd extends htFile
	{
		function __construct($path='')
		{
			parent::__construct('.htpasswd',$path,false);
		}
		
		public function create($user,$pass) {
			$this->addUser($user,$pass);
			return $this->save(true);
		}
		
		public function add($user,$pass) {
			$this->content = '';
			$this->addUser($user,$pass);
			return $this->save(false);
		}

		public function addUser($user,$pass) {
			$this->appendContent("$user:".crypt($pass, base64_encode($pass))."\n");
		}
	}

	/**
	* htgroup writer
	*/
	class htGroup extends htFile
	{
		private $groups;
		
		public function __construct($path='')
		{
			parent::__construct('.htgroup',$path,true);
			$this->groups = array();
			$this->parse();
		}
		
		public function parse() {
			$content = $this->read();
			$lines = explode("\n",$content);
			$groups = array();
			foreach($lines as $line) {
				list($group,$users) = explode(': ',$line);
				if ($group && $users) $groups[$group] = explode(' ',$users);
			}
			$this->groups = $groups;
			// print_r($groups);
		}
		
		public function save() {
			$content = '';
			foreach($this->groups as $group=>$ausers) {
				$users = implode(' ',$ausers);
				$content.= "$group: $users\n";
			}
			echo $content;
		}
		
		public function create($group,$users) {
			if (is_array($users)) $users = implode(' ',$users);
			$this->setContent("$group: $users\n");
			return $this->save(true);
		}
		
		public function add($group,$users) {
			if (is_array($users)) $users = implode(' ',$users);
			$this->setContent("$group: $users\n");
			return $this->save(false);
		}
	}


	// 
	// $name = 'Kundenbereich';
	// $group = 'customer';
	// $users = array(
	// 	'janus',
	// 	'petra',
	// );
	// $pwds = array(
	// 	'vops',
	// 	'jomalu',
	// );
	// 
	$base = dirname(__FILE__);
	$root = dirname($base); // '/homepages/20/d15624794/htdocs';
	// 
	$htg = new htGroup($root);
	$htg->recreate();
	// $htg->add($group,$users);
	// 
	// $hpw = new htPasswd($root);
	// for($i=0; $i<count($users); $i++) {
	// 	$hpw->add($users[$i],$pwds[$i]);
	// }
	// 
	// $haw = new htAccess($base);
	// $haw->create("$root/.htpasswd",$base,$name,(count($users)) ? implode(' ',$users) : '',$group,(empty($group)) ? '' : "$root/.htgroup");
	
?>