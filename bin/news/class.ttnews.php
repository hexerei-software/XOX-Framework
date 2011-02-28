<?php
/**
 * Example of calling a method in a PHP class from TypoScript
 * 
 */
unset($MCONF);	

/*require_once (dirname(XOX_APP_BASE)."/mod1/conf.php");

require_once ("d:/apache/htdocs/quickstart/typo3/init.php");
require_once ("d:/apache/htdocs/quickstart/typo3/template.php"); 

//mydump($_SERVER);
require_once ("conf.php");
echo $BACK_PATH;
echo TYPO3_MOD_PATH;
require_once ($BACK_PATH."init.php");
require_once ($BACK_PATH."template.php"); 
*/
 
class user_ttnews	{
	var $cObj;		// Reference to the parent (calling) cObj set from TypoScript
	var $update = array();
	var $connect='';
	var $db = '';
	
	/**
	 * Konstruktor
	 * @see user_reverseString()
	 */
	 
	 function user_ttnews()
	 {
	 $this->connect = mysql_connect($db_host,$db_user,$db_pass);
		$this->db = mysql_select_db($db_name,$this->connect);
		
	 }
	 
	
	
	/**
	 * @param	string		Empty string (no content to process)
	 * @param	array		TypoScript configuration
	 * @return	string		HTML output, showing content elements (in reverse order if configured.)
	 */
	function listNews($content,$conf)	{
		$this->cObj = $cObj;
		
		$query = 'SELECT * FROM tt_news WHERE pid='.$GLOBALS['TSFE']->id;
		if ($conf['reverseOrder'])	$query.=' DESC';
		$output='<br />'.'This is the query: <strong>'.$query.'</strong><br /><br />';
		return $output.$this->selectThem($query);
	}
	
	function listNew($catid=0)	{
		
		$query = 'SELECT * FROM tt_news'.($catid!=0?" WHERE category=$catid":'');
		//if ($conf['reverseOrder'])	$query.=' DESC';
		$output='<br />'.'This is the query: <strong>'.$query.'</strong><br /><br />';
		$data = $this->selectThem($query);
		return $output.$this->boxout($data);
		
	}
	
	function listCats($catid=0)	{
		
		$catquery = 'SELECT * FROM tt_news_cat';
		//if ($conf['reverseOrder'])	$query.=' DESC';
		$output='<br />'.'This is the CAt query: <strong>'.$catquery.'</strong><br />';
		$data = $this->selectThem($catquery);
		return $output.$this->selectout($data,$catid)."<br /> \n";
	}

	/**
	 * Selecting the records by input $query and returning the header field values
	 * 
	 * @param	string		MySQL query selecting the content elements.
	 * @return	string		The header field values of the content elements imploded by a <br /> tag
	 * @access private
	 */
	function selectNews($query)	{
	//debug($_POST,"post");
		$res = mysql_query($query);
		$output=array();
		$editTable = 'tt_news';
		while($row=mysql_fetch_assoc($res))	{
			$params='&edit['.$editTable.']['.$row['uid'].']=edit';
			//$link = '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$GLOBALS['BACK_PATH'])).'">Edit</a>';
			$link = '<a href="#" onclick="document.location=\'../../../../typo3/alt_doc.php?returnUrl=/quickstart/typo3conf/ext/ini_tnl/mod1/index.php?id=1'.$params.'\'; return false;">Edit</a>';
			$checkname = "SET[newscheck".$row['uid']."]";
			$checkid ="newscheck".$row['uid'];
		
		 	$check='<input type="hidden" name="'.$checkname.'" value="0"><input type="checkbox" name="'.$checkname.'" value="1"';
		 	//$check.=($_POST['SET'][]?' CHECKED':'').'>';

		 	if ($_POST['SET'][$checkid]){
		 		$check.=' CHECKED>';
		 		$this->update[$checkid]=array($row['title'],$row['bodytext']);
		 	}else{
		 	$check.=' >';
		 	}
		//	$check=t3lib_BEfunc::getFuncCheck(0,$checkname,$_POST[$checkname]);
			$output[]=$row['title'].'</td><td>'.$link.'</td><td>'.$check."\n";
		//$output[]='<a href="/quickstart/typo3/alt_doc.php?returnUrl='.$BACK_PATH.'&edit[tt_news]['.$row['uid'].']=edit">'.$row['title'].'</a>';
		}
		return '<table><tr><td>'.implode($output,'</td></tr><tr><td>').'</td></tr></table><input type="submit" value="sub" name="updateNews"/>';
	}
	
	function updateContentNews()
	{
		//debug($this->update,"update");
		$query = '';
		foreach ($this->update as $value){
			$query .= "INSERT INTO content (title,body,flags) VALUES ('".$value[0]."','".$value[1]."','news')\n";
		}
		return $query;
	}
	
	function selectThem($query)	{
		//$res = mysql(TYPO3_db,$query);
		$res = mysql_query($query,$this->connect);
		$output=array();
		while($row=mysql_fetch_assoc($res))	{
			
			$output[]=array('id'=>$row['uid'],'uid'=>$row['uid'],'title'=>$row['title'],'bodytext'=>$row['bodytext']);
		}
		//mydump($output,"outputarray");
		return $output;
	}
	function boxout($data)
	{
		$editTable = 'tt_news';
		$output=array();
		foreach ($data as $value)
		{
			$params='&edit['.$editTable.']['.$value['uid'].']=edit';
			//$link = '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$GLOBALS['BACK_PATH'])).'">Edit</a>';
			$link = '<a href="#" onclick="document.location=\'../../../../typo3/alt_doc.php?returnUrl=/quickstart/typo3conf/ext/ini_tnl/mod1/index.php?id=1'.$params.'\'; return false;">Edit</a>';
			$checkname = "SET[newscheck".$value['uid']."]";
			$checkid ="newscheck".$value['uid'];
		
		 	$check='<input type="hidden" name="'.$checkname.'" value="0"><input type="checkbox" name="'.$checkname.'" value="1"';
		 	//$check.=($_POST['SET'][]?' CHECKED':'').'>';

		 	if ($_POST['SET'][$checkid]){
		 		$check.=' CHECKED>';
		 		$this->update[$checkid]=array($value['title'],$value['bodytext']);
		 	}else{
		 	$check.=' >';
		 	}
		//	$check=t3lib_BEfunc::getFuncCheck(0,$checkname,$_POST[$checkname]);
			$output[]=$value['title'].'</td><td>'.$link.'</td><td>'.$check."\n";
		//$output[]='<a href="/quickstart/typo3/alt_doc.php?returnUrl='.$BACK_PATH.'&edit[tt_news]['.$row['uid'].']=edit">'.$row['title'].'</a>';
		
		}
		return '<table><tr><td>'.implode($output,'</td></tr><tr><td>').'</td></tr></table><input type="submit" value="sub" name="updateNews"/>'; 
	
	}
	
	function stringout($data){
		return '<br />'.implode($data,'<br />');	
	}
	
	function selectout($data,$chose){
		//mydump($_GET,"post");
		
		$option = "<option value=\"0\"".($chose==0?' selected':'').">__Alle Einträge</option>";
    foreach ($data as $value) {
    	$option .= '<option value="'.$value["id"].'" '.($value["id"]==$chose?' selected':'').'>'.$value["title"].'</option>';
    }
    //$size = (empty($elements[$i][4]))?'':'size="'.$elements[$i][4].'" multiple';
    $list.= "<select name=\"catselect\" onChange=\"document.location='index.php?p=de/1/1/7&catid='+ document.forms[0].catselect.options[document.forms[0].catselect.selectedIndex].value;\"> ";
    $list.=$option.'</select>'."\n";
    return $list;
	}
}

?>
