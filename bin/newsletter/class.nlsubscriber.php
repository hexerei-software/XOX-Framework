<?php
/***************************************************************
*
 * @author	sef <info@inity.de>
 */

//require_once("../../settings.php");

/**
* Datenbankservername
*/
define('XOXME_DB_SERVER','192.168.33.7' );

/**
* Datenbankusername
*/
define('XOXME_DB_USER','dev');

/**
* Datenbankuserpasswort
*/
define('XOXME_DB_PASS','dev');



/**
* Datenbankname
*/
define('XOXME_DB_TNL', 'mamfewo');


define('INIBASTNL_DOMAIN_ID', 1);
define('INIBASTNL_SUBSCRIPTION_FORM_ID', 1);

define('INIBASTNL_TEXT_NL_INTRO','Lorem ipsum dolor sit amet,
consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero
eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.

');

define('INIBASTNL_TEXT_CONFIG_INTRO','Wie wollen Sie Ihre Informationen zugesendet bekommen?');
define('INIBASTNL_TEXT_CONFIG_TITLE','Benutzereinstellungen');

define('INIBASTNL_COLOR_NL_LIST','#99ccff');


$base_dir = dirname(dirname(__FILE__));

class nlsubscriber  {
	var $webuser = array('ID'=>1);
	var $abonls = array();

	function main($content='',$conf='')	{
		$this->conf=$conf;

		//debug($_POST,"post");
		//debug($_GET,"get");
		//debug($GLOBALS,"globals");


		//init
		$this->initialize();
		//tnl user ermitteln und ggf anlegen
		$this->gettnluser();
		//abonnierte nls ermitteln
		$this->getabonls();
		if(isset($_POST[$this->prefixId])){
			$this->checkpost();
			$this->getabonls();

		}
		$this->gettnluser();
		$webuser= $_SESSION["webuser"];

		$base_dir = dirname(__FILE__);

		require_once("xox.newsletter.conf.php");

		//User GRUPPEN bestimmen
		$uflag[] = 0;
		$uflag[] = $checkbox_options['uflags']['Normale']; //Normale 1
		$ufla = $checkbox_options['uflags']['Normale'];
		if(isset($webuser['bonAPO']) && $webuser['bonAPO']==1){
			$uflag[] = $checkbox_options['uflags']['BonAPO']; //8
			$ufla = $checkbox_options['uflags']['BonAPO'];
		}
		if(isset($webuser['bayUNI'])&& $webuser['bayUNI']==1){
			$uflag[] = $checkbox_options['uflags']['BayUNI']; //4
			$ufla = $checkbox_options['uflags']['BayUNI'];
		}
		if(isset($partner["status"]) && ($partner["status"]=="TOP" || $partner["status"]=="AD")){
			$uflag[] = $checkbox_options['uflags']['Experts']; //2
			$ufla = $checkbox_options['uflags']['Experts']; //2
		}
		//echo $uflag.":".$checkbox_options['uflags']['Normale']; //Normale;

		$uflagstr = implode(",",$uflag);


		//alle anzuzeigenden Newsletter ermitteln
		//$sql="SELECT * FROM newsletter WHERE domain_id=".INIBASTNL_DOMAIN_ID." AND active='1' AND (uflags IN (".$uflagstr.") OR uflags=".$ufla.")";
		$sql="SELECT * FROM newsletter WHERE domain_id=".INIBASTNL_DOMAIN_ID." AND active='1' AND (uflags='0' OR uflags='".$ufla."') ORDER BY sort,created";
		//echo $sql;

		mysql_select_db($this->tnl_db,$this->contnl);
		$result = mysql_query($sql, $this->contnl);



		$content='<br /><form action="?id='.$this->cObj->data['pid'].'" name="nlform" method="POST">
			<input type="hidden" name="no_cache" value="1">';


			$content .= '<div class="tnllist">'."\n";
			$content .= '<table border=0 width="400px" cellspacing="0" cellspacing="0" style="margin:0px;">';

			///$content .= '<tr><td colspan="2"><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td class="trennerH">&nbsp;</td></tr></table></td></tr>';


			/*
			///$content .= 	'<tr><td colspan="2">'.INIBASTNL_TEXT_NL_INTRO.'</td></tr>'."\n";
			$content.= '<tr  valign="bottom"><td align="right" colspan="2" ><br />';

			$content.= make_submit(htmlspecialchars($this->pi_getLL("submit_button_label")),'cmd_save');

			//$this->content.= '<br /><br />';//</td><td align="right" >';

			$content.= '<div style="position:relative; top:-20px; left: -270px;" >'.make_submit(htmlspecialchars($this->pi_getLL("close_button_label")),'cmd_back').'</div>';

			$content.= "</td></tr>\n";
			 */

			$i =0;
		while($row = mysql_fetch_array($result)){
			$style = ($i%2)?' style="background:'.INIBASTNL_COLOR_NL_LIST.';"':'';

			$content.='<tr'.$style.'><td width="25px">';
			$content .= '<input type="checkbox" name="'.$this->prefixId.'[nl]['.$row['id'].']" value="'.$row['id'].'" '.(isset($this->abonls[$row['id']])?'checked':'').'>';
			$content .= '</td><td>';

			$content .= '<b>'.$row['name'].'</b>';

			$content .= '</td></tr>';
			$content.='<tr'.$style.'><td width="25px">&nbsp;</td>';
			$content .= '<td>'.($row['description']?$row['description']:'&nbsp;').'</td></tr>';
			$content.='<tr'.$style.'><td width="25px">&nbsp;</td>';
			$content .= '<td>&nbsp;</td></tr>';

			$i++;
		}

		///$content .= '<tr><td colspan="2"><b>'.INIBASTNL_TEXT_CONFIG_TITLE.'</b></td></tr>';
		$content .= '<tr><td colspan="2"><br /><br />'.INIBASTNL_TEXT_CONFIG_INTRO.'</td></tr>';
		$content .= '<tr><td colspan="2" align="right">';
		$content .= '<table width="100%" align="right" border=0><tr valign="top"><td align="center" valign="top" width="25"><input type="radio" name="'.$this->prefixId.'[config]" value="html" '.($this->tnluser['config']=='html'?'checked':'').' onclick="document.nlform.submit();" /> </td> <td align="left" valign="top"><b>HTML-Mail</b> </td>';
		$content .= '<td width="25" align="center" valign="top"><input type="radio" name="'.$this->prefixId.'[config]" value="text" '.($this->tnluser['config']=='text'?'checked':'').' onclick="document.nlform.submit();" /> </td> <td align="left" ><b>Text-Mail</b>';
		$content .= '</td></tr></table><br /><br /><br /></td></tr>';

		$content.= '<tr  valign="bottom"><td align="right" colspan="2" ><br />';
		$content.= make_submit(htmlspecialchars("Save"),'cmd_save');
		//$this->content.= '<br /><br />';//</td><td align="right" >';

		//$content.= '<div style="position:relative; top:-20px; left: -270px;" >'.bayas_make_submit(htmlspecialchars($this->pi_getLL("close_button_label")),'cmd_back').'</div>';
		$content.= "</td></tr>\n";

		$content .= '</table></div>';

		$content .=	'	</form>';  //<p>You can click here to '.$this->pi_linkToPage("get to this page again",$GLOBALS["TSFE"]->id).'</p>

		return $content;
	}


	function checkpost(){

		if(isset($_POST[$this->prefixId]['config']) && $_POST[$this->prefixId]['config'] != $this->tnluser['config']){
			$upsql = "UPDATE subscriber SET config='".$_POST[$this->prefixId]['config']."' WHERE id=".$this->tnluser['id'];
			if(!$resultup= mysql_query($upsql, $this->contnl)){
				echo "Fehler bei DB UPDATE:[".$upsql."]".mysql_error();
			}
		}


		if (isset($_POST[$this->prefixId]['nl'])){
			$postnls = $_POST[$this->prefixId]['nl'];
			//abbestellungen ermitteln
			foreach ($this->abonls as $aboid){
				if(!isset($postnls[$aboid])){
					$delsql = "DELETE FROM subscription_form_subscriber WHERE newsletter_id=".$aboid." AND subscriber_id=".$this->tnluser['id'];
					//echo "abbestllen	:".$aboid.":".$delsql."<br>";
					if(!$resultdel= mysql_query($delsql, $this->contnl)){
					 echo "Fehler bei DB DELETE:".mysql_error();
					}

				}
			}


			//neuabos ermitteln
			foreach ($postnls as $aboid){
				if(!isset($this->abonls[$aboid])){
					$inssql = "INSERT INTO subscription_form_subscriber (subscription_form_id,subscriber_id,newsletter_id) VALUES (".INIBASTNL_SUBSCRIPTION_FORM_ID.",".$this->tnluser['id'].",".$aboid.")";
					//echo "bestellen	:".$aboid.": ".$inssql."<br>";
					if($resultins= mysql_query($inssql, $this->contnl)){
						$insid = mysql_insert_id($this->contnl);
						//echo $insid."<br>";

					}else echo "Fehler bei DB INSERT:".mysql_error();


				}

			}


		} else {
			$delsql = "DELETE FROM subscription_form_subscriber WHERE subscriber_id=".$this->tnluser['id'];
			if(!$resultdel= mysql_query($delsql, $this->contnl)){
			 echo "Fehler bei DB DELETE:".mysql_error();
			}
		}
	}

	function getabonls(){
		//die abonnierten nls ermitteln
		$sqlsfs="SELECT * FROM subscription_form_subscriber WHERE subscriber_id=".$this->tnluser['id'];
		//echo $sqlsfs;
		$this->abonls = array();

		mysql_select_db($this->tnl_db,$this->contnl);
		$resultsfs = mysql_query($sqlsfs, $this->contnl);


		while($row = mysql_fetch_array($resultsfs)){
			//$abonls[]=$row['newsletter_id'];
			$this->abonls[$row['newsletter_id']]=$row['newsletter_id'];
		}

	}

	function gettnluser(){


		//prüfen ob bereits in der nlsubscribers tabelle
		$sql="SELECT * FROM subscriber WHERE domain_id=".INIBASTNL_DOMAIN_ID." AND uid=".$this->webuser['ID'];
		mysql_select_db($this->tnl_db,$this->contnl);
		if(!($resultsubs = mysql_query($sql, $this->contnl))){ echo $sql;}


		if (mysql_num_rows($resultsubs)<= 0 ){
			$this->inserttnluser();
			$sql="SELECT * FROM subscriber WHERE domain_id=".INIBASTNL_DOMAIN_ID." AND uid=".$this->webuser['ID'];
			//echo $sql;
			mysql_select_db($this->tnl_db,$this->contnl);
			$resultsubs = mysql_query($sql, $this->contnl);

		}

		//User ist bekannt im System
		$this->tnluser = mysql_fetch_array($resultsubs);
		$_SESSION['inibastnl']['tnluser']=$this->tnluser;
		mysql_free_result($resultsubs);
//		debug($this->tnluser,"tnluser");

	}

	function inserttnluser(){
	//User anlegen
			$insertsql =
			"INSERT INTO subscriber (domain_id,email,uid,displayname,created,config,mode_id,mode_data) VALUES "
			."('".INIBASTNL_DOMAIN_ID."','".$this->webuser['Kontakt_Email']."','".$this->webuser['ID']
			."','".addslashes(trim(join(' ',array($this->webuser['Anrede_Anrede'],$this->webuser['Anrede_Titel'],$this->webuser['Forename'],$this->webuser['Surname']))))
			."','".date("Y-m-d H:i:s")."','html','".($this->partner['KGRI_MANDANT']==1 && ($this->partner["KGRI_KDGRUPPE_INTERNET"]=='1'||$this->partner["KGRI_KDGRUPPE_INTERNET"]=='3') ? '2' : '1')
			."','".$this->webuser['Bayer_Partnernr']."')";

	//		echo $insertsql;

			mysql_select_db($this->tnl_db,$this->contnl);

			if($resultins= mysql_query($insertsql, $this->contnl)){
						$insid = mysql_insert_id($this->contnl);
						//echo $insid."<br>";
			}else echo "Fehler bei DB INSERT:".mysql_error();


	}

	function initialize(){

		$this->partnernr= ($_SESSION["partner"]['PAR_PARTNR'] == '' ? $_SESSION["webuser"]['Bayer_Partnernr'] : $_SESSION["partner"]['PAR_PARTNR']);

		$this->objectnr= (CHART_OBJ==''?$_SESSION["partner"]['PAR_OBJNR']:CHART_OBJ);

		//Typo3 Seiten ID ermitteln


		$this->webuser = (isset($_SESSION['webuser'])?$_SESSION['webuser']:array('ID'=>1));
		$this->partner = $_SESSION['partner'];

		//Typo3 Extension Konfigurationsparameter einlesen
		//require('typo3conf/localconf.php');

		$parameters = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['xoxme']);

		$db_host = (isset($parameters['xoxme_db_server'])?$parameters['xoxme_db_server']:XOXME_DB_SERVER);
		$db_user = (isset($parameters['xoxme_db_user'])?$parameters['xoxme_db_user']:XOXME_DB_USER);

		$db_pass = (isset($parameters['xoxme_db_pass'])?$parameters['xoxme_db_pass']:XOXME_DB_PASS);

		$this->tnl_db = (isset($parameters['xoxme_db_tnl'])?$parameters['xoxme_db_tnl']:XOXME_DB_TNL);
		//Datenbankverbindungen
		$this->contnl= mysql_connect($db_host,$db_user,$db_pass);
		mysql_select_db($this->tnl_db,$this->contnl);


	}
}




	/**

	* Erstelle einen Submit Button.
	*/
	function make_submit($caption='Weiter &gt;&gt',$name='cmd_next') {

		$link = '<input type="submit" style="background:#0099cc;color:white;font-weight:bold;border:0;cursor:pointer;';

		$link.= 'padding-top:1px;padding-bottom:2px;padding-left:6px;padding-right:6px;font-size:13px;height:20px;width:120px;';

		$link.= '" value="'.$caption.'" name="'.$name.'" />';

		return $link;

	}



?>
