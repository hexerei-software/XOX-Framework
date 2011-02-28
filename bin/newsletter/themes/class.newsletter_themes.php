<?php

//require_once("../../../../settings.php");



/*
 *  Klasse repräsentiert ein Thema (Design)
 *  Jedes Thema wird in einem Ordner gespeichert
 * Der Ordnername ist der Präfix des Themas
 * Eine Ini.Datei Präfix.ini beschreibt die Thementeile
 *
 **/
class xoxNewsletterTheme {

	var $prefix = '';
	var $inifile = '';
	var $conf = array();
	//Pfadangabe der Haupttmpl
	var $main_tpl = '';
	//Pfadangabe der subtempl
	var $sub_tpl = array();
	//Anzeigenamen der Subtemplates
	var $sub_name = array('default'=>'Default');

	//Pfadangabe der Haupttextvorlage
	var $main_txt = '';
	//Pfadangabe der Subtextvorlagen
	var $sub_txt = array();

	var $name = '';

	function xoxNewsletterTheme($prefix='default' , $inifile = '' )	{
	  //	echo $prefix;
		if($prefix == 'default'){$inifile=XOX_THEMES_DIR.'/default.ini';} //default setzen
		else{$inifile=XOX_THEMES_DIR.'/'.$prefix.'/'.$prefix.XOX_THEMES_INI_NAME;}
		//echo $ini_file;
		if(file_exists($inifile)){

			$this->prefix = $prefix;
			$this->inifile = $inifile;

			$this->readIniFile();

			return true;
		}else return false;

	}  //

	function readIniFile()
	{
		//require_once("iniRWC.inc.php");
		 // echo $this->inifile;
		//$c=new iniRWC;
		$conf=parse_ini_file($this->inifile,true);
		$this->main_tpl = $conf['main_template'];
		$this->sub_tpl = (isset($conf['subtmpl'])?$conf['subtmpl']:array());
		$this->sub_name = (isset($conf['subname'])?$conf['subname']:array());

		$this->main_txt = $conf['main_text'];
		$this->sub_txt = (isset($conf['subtext'])?$conf['subtext']:array());


		$this->name = $conf['template_name'];
	}  //  readIniFile

	function getMainTmpl()
	{
		return $this->prefix.'/'.$this->main_tpl;
	}  //  getMainTmpl

	function getMainTxt()
	{
	  return $this->prefix.'/'.$this->main_txt;
	}  //  getMainTxt

	function getSubNames()
	{
		$out = array();
		foreach($this->sub_name as $sub=>$name){
			$out[$name]=$sub;
		}
		return $out;
	}  //  getSubNames

  	function getSubTpls()
	{
		$out = array();
		foreach($this->sub_tpl as $sub=>$tpl){
			$out[$sub]=$this->prefix.'/'.$tpl;
		}
		return $out;
	}  //  getSubTmpl

  	function getSubTxt()
	{
		$out = array();
		foreach($this->sub_txt as $sub=>$txt){
			$out[$sub]=$this->prefix.'/'.$txt;
		}
		return $out;
	}  //  getSubTmpl



}

class xoxNewsletterThemeManager {

	var $themes = array();

	function xoxNewsletterThemeManager($themes_dir = XOX_THEMES_DIR)
	{
		if(file_exists($themes_dir)){

			$dh  = opendir($themes_dir);
			while (false !== ($dirname = readdir($dh))) {
			//	echo $dirname."<br>";
				if(is_dir($themes_dir.'/'.$dirname)){
					$inifile = $themes_dir.'/'.$dirname.'/'.$dirname.XOX_THEMES_INI_NAME;
					if(file_exists($inifile)){
						$this->themes[$dirname] = new xoxNewsletterTheme($dirname,$inifile);
					}
				}
			}
			return true;
		}else return false;
	}  //  xoxNewsletterThemeManager

	/* Returns an array of arr[name]= prefix
	 * used for settings in nlbearbeiten.php
	 */
	function getThemes()
	{
		foreach($this->themes as $thema){
		  $out[$thema->name] = $thema->prefix;
		}
		return $out;
	}  //  getThemes

	function getTheme($prefix='')
	{
		if(isset($this->themes[$prefix])){
			return $this->themes[$prefix];
		}else return false;
	}  //  getTheme


}

/*
$t = new xoxNewsletterThemeManager();
print_r($t->themes);
  */
?>
