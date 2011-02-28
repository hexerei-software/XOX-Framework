<?
/******************************************************************************
** This file is part of the PMWiki extension media2mult.                     **
** Copyright (c) 2005-2008 Zentrum virtUOS, University of Osnabrück, Germany **
**                                                                           **
** This program is free software; you can redistribute it and/or             **
** modify it under the terms of the GNU General Public License               **
** as published by the Free Software Foundation; either version 2            **
** of the License, or (at your option) any later version.                    **
**                                                                           **
** This program is distributed in the hope that it will be useful,           **
** but WITHOUT ANY WARRANTY; without even the implied warranty of            **
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             **
** GNU General Public License for more details.                              **
**                                                                           **
** You should have received a copy of the GNU General Public License         **
** along with this program; if not, write to the Free Software               **
** Foundation, Inc., 51 Franklin Street, Fifth Floor,                        **
** Boston, MA 02110-1301, USA.                                               **
*******************************************************************************/

require_once 'MediaObject.class.php';

class ScriptObject extends MediaObject {
	var $code;

	function ScriptObject ($pagename, $script, $isfile=true) {
		if ($isfile) {
			MediaObject::MediaObject($pagename, $script);
			$this->code  = '';
		}
		else {
			MediaObject::MediaObject($pagename, '');
			$this->fname = md5($script);
			$this->code  = $script;
			recursive_mkdir($this->dir());
			$f = fopen($this->path(), "w");
			fputs($f, $script);
			fclose($f);
		}
	}

	function type ()       {return 'script';}
	function scripttype () {return '';}
	function htmlready ()  {return false;}
	
	function cleanup () {
		if ($this->code != '' && $this->exists())
			$this->remove();
		// @@ remove empty directories
	}


	function getCode () {
		if ($this->code != '')
			return $this->code;
		if ($this->exists())
			return file_get_contents($this->path());
		return '';
	}
	
	function convert ($format, $attr, $showMessages=false) {
		if ($this->code == '') {
//			$this->save();
		}
		else {
			$f = fopen($this->path(), "w");
			fputs($f, $this->code);
			fclose($f);
		}
		$mo = $this->createMO($format, $attr, $showMessages);
		if ($this->code == ''){
//			$this->restore();
		}
		else 
			$this->remove();
		
		if ($mo === false)
			return false;

		$f = $this->dir()."/".$mo->fname;
//		$b = file_strip_extension($f).'.box';
		if ($this->dir() != $mo->dir() && file_exists($f)) { // move MO to correct dir
//			@unlink($mo->path());
//			@unlink(file_strip_extension($mo->path()).'.box');
			@unlink($mo->path());  // @@
			recursive_mkdir($mo->dir());
			@system('mv '. file_strip_extension($f).".* ".$mo->dir()); // @@ 
/*			@copy($f, $mo->path());
			@copy($b, file_strip_extension($mo->path()).'.box');
			@unlink($f);
			@unlink($b);*/
		}
		return $mo;
	}

	
	// template method: erzeugt das jeweilige Medienobjekt
	function createMO ($script, $attr, $showMessages=false) {
		return false;
	}

	function mustReconvert ($mo) { 
		return $mo && (!$mo->exists() || ($this->code == '' && $mo->time() < $this->time()) || $mo->filesize() == 0);
	}

	function getDocBook ($role, $attr) {
		return '';
	}
	
	function getWikiXML ($role, $attr) {
		$res = "";
		$types = array('png', 'eps', 'pdf');
		foreach ($types as $t) {
			$mo = $this->convert($t, $attr);
			if ($mo)
				$res .= $mo->getWikiXML($role, $attr);
		}
		return $res;
	}

	function getHTML ($attr) {
		$mo = $this->convert('png', $attr);
		if ($mo)
			return $mo->getHTML($attr);
		return '';
	}
}


/*
// class converting MetaPost scripts
class MpostScriptConverter extends ScriptToMediaObjectConverter {

	function evalScript($code, $filename = false) {
		if(!$filename){
			$code = $this->formatEvalScript($code);
			$code = $this->produceImage($code);
		}
		return $this->postEvalScript($code); // @@ to be removed
	}

	function formatEvalScript($code){
		//replace all end commands by one end command at the end of file
		$code = preg_replace('/^(.*)(\send\s|.)(.*)$/s','\1\3; end',$code,1);
		return $code;
	}

	function produceImage($code){
		global $MPOST, $CONVERT;
		//Example: 
		// draw (20,20)--(0,0)
		$fileName = md5($code) . "." . $this->converterFormat;
		if(file_exists("{$this->outputDir}/$fileName"))
			return $fileName;

		//create and process .mp file from $code		
		$dataFile = "{$this->outputDir}/" . str_replace("." . $this->converterFormat, ".mp", $fileName);
		$f = tempnam($this->outputDir, '');
		rename($f, $dataFile);
		$f = fopen($dataFile, "w");
		fputs($f, $code);
		fclose($f);
		//print_r("<br><br><br>$MPOST -output-directory={{$this->outputDir}} {$dataFile}<br> Pass: ");
		passthru("$MPOST -output-directory={$this->outputDir} $dataFile");

		//read out datafile into $filearray
		$logFile = str_replace(".mp",".log",$dataFile);
		if(file_exists($logFile)){
			$f = fopen("$logFile", "r");
			$log = fread($f, filesize($logFile));
			fclose($f);
		}
		//print_r("<br>LOG: ".$log);

		$log = trim(str_replace(' ..','',str_replace('written: ','',strstr($log,'written: '))));
		$logFiles = explode(" ",$log);

		//print_r("<br> LOG: ".$log);
		//print_r("<br> LOG2: ".$logFiles[0]);

		//for all elements append them seperated by "false 0 startjob pop" into the first ps-file
		$firstPS = trim($logFiles[0]);
		if(file_exists("{$this->outputDir}/$firstPS")){
			$f = fopen("{$this->outputDir}/$firstPS", "a");		
			for ($index = 1; $index < sizeof($logFiles); $index++) {
				if(file_exists(trim("{$this->outputDir}/$logFiles[$index]"))){
					$element = fopen("{$this->outputDir}/".trim($logFiles[$index]), "r");
					$strElement = fread($element, filesize("{$this->outputDir}/".trim($logFiles[$index])));
					fclose($element);
					$strElement = "false 0 startjob pop\n".$strElement;
				}
				if (is_writable("{$this->outputDir}/$firstPS"))
					fwrite($f,$strElement);											
				unlink(trim("{$this->outputDir}/$logFiles[$index]"));
			}
			fclose($f);
		}
		//convert merged ps-file to image
		$imageFile = preg_replace("/^(.*\.)(.x*)/","\\1" . $this->converterFormat, $firstPS);
		exec("$CONVERT {$this->outputDir}/$firstPS {$this->outputDir}/$imageFile");
		//print_r("<br> $CONVERT {{$this->outputDir}}/{$firstPS} {{$this->outputDir}}/{$imageFile}");					

		//delete Files
		unlink($dataFile);
		unlink($logFile);
		unlink("{$this->outputDir}/$firstPS");

		return $fileName;	    		
	}
}*/

////////////////////////////////////////////////////////////////////////////////////

?>
