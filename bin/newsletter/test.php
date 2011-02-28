<?php

require_once (XOX_LIB."/security/class.logger.php");
$GLOBALS['log'] = new xoxLogger(XOX_APP_BASE.'/logs/adexe',LOG_ECHO|LOG_MSG_MESSAGES|LOG_MSG_ERRORS|LOG_MSG_TRACKING);

/******************************************************************************/

/* --------------------------------------------------
	delete lost issues, which should have been deleted
  when newsletter was deleted
 --------------------------------------------------*/
function deleteLostIssues() {
	
	$GLOBALS['log']->setID('deleteLostIssues');
	$GLOBALS['log']->begin('Deleting lost issues and content');
	
	// reset id arrays
	$nl_id = array();
	$is_id = array();
	$cn_id = array();
	
	// show existing newsletters
	if ($nl_rs = executeQuery("SELECT id, name, active FROM newsletter")) {
		while ( $nl_r = $nl_rs->getrow() ) {
			$nl_id[] = $nl_r['id'];
			echo sprintf("Newsletter (%s) %s is%s Active<br>",$nl_r['id'],$nl_r['name'],($nl_r['active']?'':' not'));
			// show active issues
			if ($is_rs = executeQuery("SELECT id, title, date FROM issue WHERE newsletter_id=".$nl_r['id'])) {
				while ( $is_r = $is_rs->getrow() ) {
					#$is_id[] = $is_r['id'];
					echo sprintf("... Issue (%s) %s von %s<br>",$is_r['id'],$is_r['title'],$is_r['date']);
					// show active content
					if ($cn_rs = executeQuery("SELECT id, title, url FROM content WHERE issue_id=".$is_r['id']." ORDER BY flags, displayorder")) {
						while ( $cn_r = $cn_rs->getrow() ) {
							#$cn_id[] = $cn_r['id'];
							echo sprintf("...... Content (%s) %s - %s<br>",$cn_r['id'],$cn_r['title'],$cn_r['url']);
						}
						$cn_rs->free();
					}
				}
				$is_rs->free();
			}
		}
		$nl_rs->free();
	}
	
	// show non active issues
	echo '<span style="color:red">';
	$cond = "newsletter_id NOT IN (".join(',',$nl_id).")";
	if ($is_rs = executeQuery("SELECT id, title, date FROM issue WHERE ".$cond)) {
		while ( $is_r = $is_rs->getrow() ) {
			$GLOBALS['log']->message(sprintf("Found lost Issue (%s) %s dated %s",$is_r['id'],$is_r['title'],$is_r['date']));
			if (
				executeSQL("DELETE FROM content WHERE issue_id=".$is_r['id'])
				&& executeSQL("DELETE FROM issue WHERE id=".$is_r['id'])
				) $GLOBALS['log']->message("...... DELETED");
		}
		$is_rs->free();
	}
	if ( !executeSQL("DELETE FROM newsletter_subscription_form WHERE ".$cond) ) $GLOBALS['log']->error("Could not delete newsletter_subscription_form entries");
	if ( !executeSQL("DELETE FROM subscription_form_subscriber WHERE ".$cond) ) $GLOBALS['log']->error("Could not delete subcription_form_subscriber entries");
	if ( !executeSQL("DELETE FROM topic WHERE ".$cond) ) $GLOBALS['log']->error("Could not delete topic entries");
	$GLOBALS['log']->end(true);
}
/**/

/******************************************************************************/

/* --------------------------------------------------
	extract url from body, if there is no url value
 --------------------------------------------------*/
function extractURLs() {
	$GLOBALS['log']->setID('extractURLs');
	$GLOBALS['log']->begin('Extracting URLs from new content');
	if ($rs = executeQuery("SELECT * FROM content WHERE url=''")) {
		while ( $r = $rs->getrow() ) {
			preg_match("/href=\"([^\" ]*)\"/i",$r['body'],$matches);
			if ( count($matches)==2 )  {
				$GLOBALS['log']->message(" SQL -> UPDATE content SET url='$matches[1]' WHERE id=".$r['id']);
				executeSQL("UPDATE content SET url='$matches[1]' WHERE id=".$r['id']);
			}
		}
		$rs->free();
		$GLOBALS['log']->end(true);
	} else {
		$GLOBALS['log']->error('NO Content with empty url');
		$GLOBALS['log']->end(false);
	}
}
/**/

/******************************************************************************/

/* --------------------------------------------------
	extract url from body, if there is no url value
 --------------------------------------------------*/
function updateBayasUser() {
	// bayas user updater
	// this code updates the newsletter user table
	// with the current status of the bayas_shop database
	$GLOBALS['log']->begin('BayAS User Update');
	
	$aIgnore = array( '',
		'0000001', '0000002', '0000003',
		'2659443', '2659001', '2661030',
		'1059173', '3150909'
	);
	
	// open bayas_shop db and read tblWebUser
	if ( $rs = executeQuery(
			"SELECT ID, Kontakt_Email, MemberSince, "
		. "Anrede_Anrede, Anrede_Titel, Forename, Surname, "
		. "Bayer_Partnernr, KGRI_KDGRUPPE_INTERNET "
		. "FROM ".XOX_DB_PARTNER.".tblWebUser LEFT JOIN ".XOX_DB_PARTNER.".kissPartner ON PAR_PARTNR=Bayer_Partnernr "
		. "WHERE Bayer_Aktiv=1 AND Username > '' AND Kontakt_Email LIKE '%@%' AND Bayer_Partnernr NOT IN ('".join("','",$aIgnore)."') AND KGRI_KDGRUPPE_INTERNET IN (1,2,3) AND KGRI_MANDANT=1 "
		. "GROUP BY Kontakt_Email ORDER BY KGRI_KDGRUPPE_INTERNET") )
	{
		$iTotal  = 0;
		$iInsert = 0;
		$iUpdate = 0;
		$iErrors = 0;

		while ( $r = $rs->getrow() )
		{
			$iTotal++;

			$sql_find = "SELECT id FROM subscriber WHERE uid=".$r['ID'];

			$mapping = array(
				'domain_id' 	=> $GLOBALS['tnl_domain_id'],
				'email' 			=> trim($r['Kontakt_Email']),
				'uid' 				=> $r['ID'],
				'displayname'	=> addslashes(trim(join(' ',array($r['Anrede_Anrede'],$r['Anrede_Titel'],$r['Forename'],$r['Surname'])))),
				'created'			=> date("Y-m-d H:i:s"),
				'config'			=> 'html',
				'mode_id'			=> (($r["KGRI_KDGRUPPE_INTERNET"]=='1'||$r["KGRI_KDGRUPPE_INTERNET"]=='3') ? '2' : '1'),
				'mode_data'		=> $r['Bayer_Partnernr']
			);

			$iInsert++;
			$sql = "INSERT INTO subscriber (domain_id,email,uid,displayname,created,config,mode_id,mode_data) VALUES ('"
			.join("','",$mapping)."')";

			if ( $rss = executeQuery($sql_find) ) {
				if ( $row = $rss->getrow() ) {
					$iUpdate++;
					$iInsert--;
					$sql = "UPDATE subscriber SET ";
					foreach($mapping as $fname=>$field) {
						if ( $fname!='uid' && $fname!='config' ) {
							$sql .= $fname."='".$field."', ";
						}
					}
					$sql = substr($sql,0,-2)." WHERE uid='".$r['ID']."'";
				}
				$rss->free();
			}
			#$GLOBALS['log']->message($sql);
			if (!executeSQL($sql)) {
				$iErrors++;
				$GLOBALS['log']->error($sql);
			}
		}
		//close the logger
		$GLOBALS['log']->message(sprintf("%u BayAS Benutzer verarbeitet, davon %u neu angelegt und %u aktualisiert mit %u Fehlern.",$iTotal,$iInsert,$iUpdate,$iErrors));
		$GLOBALS['log']->end(true);
	} else {
		$GLOBALS['log']->error("Keine Datenbankverbindung! Bitte versuchen Sie es zu einem späteren Zeitpunkt nochmal.");
		$GLOBALS['log']->end(false);
	}
}


	
/******************************************************************************/
/******************************************************************************/
/******************************************************************************/

if ( !empty($_GET['cmdGo']) ) {
	switch($_GET['cmdGo']) {
		case 'deleteLostIssues':
			echo "<h3>TEST Delete Lost Issues</h3><hr>";
			deleteLostIssues();
			break;
		case 'extractURLs':
			echo "<h3>TEST Extract URLs</h3><hr>";
			extractURLs();
			break;
		case 'updateBayasUser':
			echo "<h3>TEST Aktualisieren der Empfängerdaten</h3><hr>";
			updateBayasUser();
			break;
	}
	?>
	<a href="index.php?p=de/5/1" alt="Zurück">Zurück</a>
	<?php
} else {
	?>
		<form method="GET" action="index.php">		
			<input name="p" type="hidden" value="de/5/1" />
			<input name="cmdGo" type="submit" value="deleteLostIssues" style="width:200px;" /><br />
			<input name="cmdGo" type="submit" value="extractURLs" style="width:200px;" /><br />
			<input name="cmdGo" type="submit" value="updateBayasUser" style="width:200px;" /><br />
		</form>
	<?php
}

	
?>          