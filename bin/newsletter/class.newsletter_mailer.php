<?php

	define('XOX_MAILER_TYPE','pickup'); // mail, pickup, qmail, sendmail, smtp 
	
	require_once (XOX_LIB."/template/class.compound_template.php");
	require_once (XOX_LIB."/html/class.html2text.php");
	require_once (dirname(__FILE__)."/inc.classes.php");
	require_once (XOX_LIB."/security/class.logger.php");
	require_once (XOX_CONFIG);

	require_once(XOX_LIB."/email/email_message.php");
	if (XOX_MAILER_TYPE!='mail') require_once(XOX_LIB."/email/".XOX_MAILER_TYPE."_message.php");

	define('MAILTEST_ACTIVE',	false);
	define('MAILTEST_MAX_MAILS', 1000);


	/*****************************************************************
		a newsletter renderer class serving as base for mailers
	*****************************************************************/
	class xoxNewsletterRenderer
	{
		/*
		 *	array of reserved replacement variables
		 */
		var $mailvars = array(
			'USEREMAIL', 'USERSALUT', 'USERFNAME', 'USERLNAME',
			'USERNAME', 'USERCOMPANY', 'USERSTREET', 'USERCOUNTRY',
			'USERZIP', 'USERCITY', 'USERTEL', 'USERFAX', 'EXPERTS',
			'USERMOB', 'DOMAINID', 'USERID', 'ISSUEID','BAYPOINTS'
		);

		/*
		 * the text template class
		 */
		var $text_template	= 0;

		/*
		 * the html template class
		 */
		var $html_template	= 0;

		/*
		 * the mail template class
		 */
		var $mail_template	= 0;

		/*
		 * the newsletter class
		 */
		var $c_newsletter		= 0;

		/*
		 * the current issue class
		 */
		var $c_issue				= 0;

		/*
		 * any errors are reported here
		 */
		var $error					= '';

		/*
		* the logger
		*/
		var $log = '';

		/*
		* mail send counter
		*/
		var $mail_counter = 0;

		/*
		* error mail send counter
		*/
		var $mail_error = 0;

		/*
		* how many mail errors in the start until stop
		*/
		var $error_break = 10;


		function xoxNewsletterRenderer() {

			/* set the logger
			*/
			#$this->log = new xoxLogger($GLOBALS['tnl_feedback_email'],LOG_MSG_MESSAGES|LOG_MSG_ERRORS|LOG_MSG_TRACKING);
			#$this->log->buffered = true;
			$this->log = new xoxLogger(XOX_APP_BASE.'/logs/bayas_nl_'.date('YmdHis'),LOG_MSG_MESSAGES|LOG_MSG_ERRORS|LOG_MSG_TRACKING);
			if ( empty($GLOBALS['tnl_logger_intro']) ) $GLOBALS['tnl_logger_intro']="Newsletter Versand";
			$this->log->begin($GLOBALS['tnl_logger_intro']);

		}

		function close(){

			//write logger summary
			$this->log->message('Versand abgeschlossen: '.'Newsletter versendet an '.$this->mail_counter.' Adresse'.($this->mail_counter!=1?'n':'').'.');
			if($this->mail_error > 0){
				$this->log->message("\n".'Bei '.$this->mail_error.' Adresse'.($this->mail_error!=1?'n':'').' traten Fehler auf.');
			}
			//close the logger
			$this->log->end(true);
			#$this->log->write_buffer($GLOBALS['tnl_logger_subject'],$GLOBALS['tnl_logger_from']);

		}

		/*
		 *	Create the text templates used to compose the mail template
		 */
		function setTextTemplate($main='default.txt',$sub='default_sub.txt') {
			$this->error='';
			// assure that we have an array of subs
			if ( !is_array($sub) ) {
				$sub = XOX_APP_BASE.'/'.$GLOBALS['xox_language_id'].'/templates/newsletter/'.$sub;
			} else {
				foreach($sub as $key=>$tmp)
					$sub[$key] = XOX_APP_BASE.'/'.$GLOBALS['xox_language_id'].'/templates/newsletter/'.$sub[$key];
			}
			// create text template
			$this->text_template = new xoxCompoundTemplate( XOX_APP_BASE.'/'.$GLOBALS['xox_language_id'].'/templates/newsletter/'.$main, $sub );
			// reset mail template
			$this->mail_template = 0;
		}

		/*
		 * return the composed text template
		 * this does not replace mailvars
		 */
		function composeText() {
			$result = '';
			if ($this->text_template)
				$result = $this->text_template->show(true);
			return $result;
		}

		/*
		 *	Create the html templates used to compose the mail template
		 */
		function setHtmlTemplate($main='default.html',$sub='default_sub.html') {
			$this->error='';
			// assure that we have an array of subs
			if ( !is_array($sub) ) {
				$subs = XOX_APP_BASE.'/'.$GLOBALS['xox_language_id'].'/templates/newsletter/'.$sub;
			} else {
				foreach($sub as $key=>$tmp)
					$sub[$key] = XOX_APP_BASE.'/'.$GLOBALS['xox_language_id'].'/templates/newsletter/'.$sub[$key];
			}
			// create html template
			$this->html_template = new xoxCompoundTemplate( XOX_APP_BASE.'/'.$GLOBALS['xox_language_id'].'/templates/newsletter/'.$main, $sub );
			// reset mail template
			$this->mail_template = 0;
		}

		/*
		 * return the composed html template
		 * this does not replace mailvars
		 */
		function composeHtml() {
			$result = '';
			if ($this->html_template)
				$result = $this->html_template->show(true);
			return $result;
		}

		/*
		 * set the content of the newsletter by choosing newsletter and issue
		 */
		function setNewsletter($newsletter_id, $issue_id=0) {

			$this->error='';

  		$this->c_newsletter = new cNewsletter($newsletter_id);
			$ai = $this->c_newsletter->getIssues();

			if (count($ai) < 1) {
				$this->error='No Valid Newsletter Issues Set';
				return false;
			}

			$this->c_issue = $ai[0];
			foreach($ai as $is) {
				if ( $is->id == $issue_id ) {
					$this->c_issue = $is;
				}
			}

			/*if (!isset($ai[$issue_id])) {
				$this->error='No Valid Newsletter Issue Set';
				return false;
			}

			$this->c_issue = $ai[$issue_id];*/
			$ac = $this->c_issue->getContents();

			#$c->debug();
			#$i->debug();
			#foreach($ac as $content) $content->debug();

			$mainvars = array();

			// add mail replacement variables
			foreach($this->mailvars as $var) {
				$mainvars[$var] = '{$'.$var.'}';
			}

			// add date and unsubscription url
			$mainvars['DATE'] = date('d.m.Y');
			// TODO: get correct unsubscribe url (config or database?)
			$mainvars['UNSUBSCRIBE_URL'] = '';

			// check if text template is used
			if ( $this->text_template ) {
				$this->text_template->setVar($mainvars);

				// create converter class
				$html = new Html2Text($this->c_issue->introduction,80);

				// issue title and introduction in text style
				$this->text_template->setVar('TITLE',$this->c_issue->title);
				$this->text_template->setVar('INTRODUCTION',trim($html->convert()));

				// copy content
				$content_count=1;
				foreach($ac as $content) {

					// text content
					$this->text_template->setVar('TITLE',$content->title,$content->flags,$content_count);
					$html->iHtmlText = $content->body;
					$this->text_template->setVar('BODY',trim($html->convert()),$content->flags,$content_count);
					$this->text_template->setVar('URL',
						(empty($content->url)?'':"-> ".$content->url)."\n\n",
						$content->flags,$content_count);

					$content_count++;
				}
			}

			// check if html template is used
			if ( $this->html_template ) {
				$this->html_template->setVar($mainvars);

				// issue title and introduction in html style
				$this->html_template->setVar('TITLE',$this->c_issue->title);
				$this->html_template->setVar('INTRODUCTION',$this->c_issue->introduction);

				// copy content
				$content_count=1;
				foreach($ac as $content) {

					// html content
					$this->html_template->setVar('TITLE',$content->title,$content->flags,$content_count);
					$this->html_template->setVar('BODY',$content->body,$content->flags,$content_count);
					$this->html_template->setVar('URL',
						(empty($content->url)?'':'<a href="'.$content->url).'">&gt;&gt;&gt;</a>',
						$content->flags,$content_count);

					$content_count++;
				}
			}
			return true;
		}

		function renderMailTemplate($newsletter_id=0) {
			if (!is_class($this->mail_template)) {
			}
			// return the composed result of the mail template
			return $this->mail_template->show(true);
		}

	}	// finish class xoxNewsletterRenderer


	/*****************************************************************
		a newsletter renderer and mailer class using the mime email
		message class from m.lemos to send
	*****************************************************************/
	class xoxLemosNewsletterMailer extends xoxNewsletterRenderer
	{
		function send($to='') {
			$this->error='';


			/*
			 * check if newsletter and issue are set
			 */
			if ( !is_object($this->c_newsletter) || !is_object($this->c_issue) ) {
				$this->error='No valid newsletter issue set';
				$this->log->error($this->error);
				return false;
			}

			/*
			 * check if text or html messages are set
			 */
			$message = $this->composeText();
			$html = $this->composeHtml();
			if ( empty($message) && empty($html) ) {
				$this->error='No content set';
				$this->log->error($this->error);
				return false;
			}

			/*
			 * now prepare email
			 */
			$from_name							= $this->c_newsletter->sender_name;
			$from_address						= $this->c_newsletter->getSender();
			if ( empty($from_address) ) {
				$this->error='No valid sender address set';
				$this->log->error($this->error);
				return false;
			}

			$reply_name							= $from_name;
			$reply_address					= $this->c_newsletter->getReply();
			if ( empty($reply_address) ) {
				$reply_address				= $from_address;
			}

			$error_delivery_name		= $from_name;
			$error_delivery_address	= $this->c_newsletter->getReturn();
			if ( empty($error_delivery_address) ) {
				if ( empty($GLOBALS['tnl_error_email']) ) {
					$error_delivery_address	= $from_address;
				} else {
					$error_delivery_address	= $GLOBALS['tnl_error_email'];
				}
			}

			if (empty($to))	{
				$to										= $this->c_newsletter->getSubscribers();
			} elseif (!is_array($to)) {
				$to										= array($to);
			}
			/*array(
				array(
					'UID' => '0000001',
					"address"=>"daniel@hexerei.net",
					"name"=>"Peter Gabriel"
				),
				array(
					'UID' => '0000002',
					"address"=>"daniel@hexerei-software.de",
					"name"=>"Paul Simon"
				),
				array(
					'UID' => '0000003',
					"address"=>"webmaster@hexerei-software.de",
					"name"=>"Mary Chain"
				)
			);*/
			$subject=$this->c_issue->title;

			switch ( XOX_MAILER_TYPE ) {
				case 'pickup':
					$email_message=new pickup_message_class;
					$email_message->dir_sep = '/';
					$email_message->mailroot_directory = XOX_APP_BASE.'/jobs';
					$email_message->pickup_file_prefix = date('YmdHis').'_';
					$email_message->pickup_file_postfix = '.eml';
					break;
				case 'sendmail':
					$email_message=new sendmail_message_class;
					$email_message->delivery_mode=SENDMAIL_DELIVERY_DEFAULT; /*  Mode of delivery of the message. Supported modes are:
							                                                     *  SENDMAIL_DELIVERY_DEFAULT     - Default mode
							                                                     *  SENDMAIL_DELIVERY_INTERACTIVE - Deliver synchronously waiting for remote server response.
							                                                     *  SENDMAIL_DELIVERY_BACKGROUND  - Deliver asynchronously without waiting for delivery success response.
							                                                     *  SENDMAIL_DELIVERY_QUEUE       - Leave message on the queue to be delivered later when the queue is run
							                                                     *  SENDMAIL_DELIVERY_DEFERRED    - Queue without even performing database lookup maps.
							                                                     */
					$email_message->bulk_mail_delivery_mode=SENDMAIL_DELIVERY_QUEUE; /*  Mode of delivery of the message when the class is set to the bulk mail delivery mode */
					$email_message->sendmail_arguments="";                   /* Additional sendmail command line arguments */
					break;
				case 'qmail':
				case 'smtp':
				case 'mail':
				default:
					$email_message=new email_message_class;
					break;
			}

			echo "<table style='font-size:11px'><tr><td><b>From</b></td><td>$from_address, $from_name</td></tr>";
			echo "<tr><td><b>Reply-To</b></td><td>$reply_address, $reply_name</td></tr>";
			echo "<tr><td><b>Errors-To</b></td><td>$error_delivery_address, $error_delivery_name</td></tr>";
			if (count($to)<100) foreach($to as $subscriber) echo '<tr><td><b>To</b></td><td>'.$subscriber->email.'</td></tr>';
			else echo '<tr><td></td><td><b>Massenmailing</b></td></tr>';
			echo '</table>';

			/*
			 *  For faster queueing use qmail...
			 *
			 *  require_once("qmail_message.php");
			 *  $email_message=new qmail_message_class;
			 *
			 *  or sendmail in queue only delivery mode
			 *
			 *  require_once("sendmail_message.php");
			 *  $email_message=sendmail_message_class;
			 *  $email_message->delivery_mode=SENDMAIL_DELIVERY_QUEUE;
			 *
			 *  Always call the SetBulkMail function to hint the class to optimize
			 *  its behaviour to make deliveries to many users more efficient.
			 */

			$email_message->SetBulkMail(1);

			$email_message->SetEncodedEmailHeader("From",$from_address,$from_name);
			$email_message->SetEncodedEmailHeader("Reply-To",$reply_address,$reply_name);

			/*
			 *	Set the Return-Path header to define the envelope sender address
			 *	to which bounced messages are delivered.
			 *  If you are using Windows, you need to use the smtp_message_class
			 *	to set the return-path address.
			 */
			if (!XOX_LOCAL_MODE) $email_message->SetHeader("Return-Path",$error_delivery_address);
			$email_message->SetEncodedEmailHeader("Errors-To",$error_delivery_address,$error_delivery_name);
			$email_message->SetEncodedHeader("Subject",$subject);

			// make sure text is not cached for personalization
			$email_message->cache_body=0;

			// create copy for text only
			$email_text = $email_message;

			/* Create empty parts for the parts that will be personalized for each recipient. */
			$email_message->CreateQuotedPrintableTextPart($message,"",$text_part);
			$email_message->CreateQuotedPrintableHTMLPart($html,"",$html_part);
			$email_text->CreateQuotedPrintableTextPart($message,"",$text_only_part);

			/*
			 *  Multiple alternative parts are gathered in multipart/alternative parts.
			 *  It is important that the fanciest part, in this case the HTML part,
			 *  is specified as the last part because that is the way that HTML capable
			 *  mail programs will show that part and not the text version part.
			 */
			$alternative_parts=array(
				$text_part,
				$html_part
			);
			$email_message->AddAlternativeMultipart($alternative_parts);

			/* Add the empty part wherever it belongs in the message. */
			$email_text->AddPart($text_only_part);

			/* Iterate personalization for each recipient. */
			$count_to = count($to);
			if ( MAILTEST_ACTIVE && $count_to > MAILTEST_MAX_MAILS ) $count_to = MAILTEST_MAX_MAILS;

			$tmp = new xoxSimpleTemplate('','','','\{\$','\}');
			$tmp->setVar( 'DOMAINID', $this->c_newsletter->domain_id );
			$tmp->setVar( 'ISSUEID',  $this->c_issue->id );

			set_time_limit(60);

			for($recipient=0;$recipient<$count_to;$recipient++)
			{
				$error = '';

				#### MAILTEST ####
				if ( MAILTEST_ACTIVE && $count_to > 20 ) {
					$testemail = array(
						'pop3test00@dev.inity.de', 'pop3test01@dev.inity.de', 'pop3test02@dev.inity.de', 'pop3test03@dev.inity.de',
						'daniel@hexenfamilie.de','daniel@hexerei-software.de','jasmin@hexenfamilie.de', 'petra@hexerei-software.de',
						'daniel@hexerei.net', 'daniel.vorhauer@hexerei-software.de', 'info@hexerei-software.de', 'karolinger60@gmx.de' 
					);
					$to[$recipient]->email = $testemail[rand(0,11)];
					#$to[$recipient]->email = sprintf("forwardtest%02d@dev.inity.de",rand(0,3));
				}

				/* Personalize the recipient address. */
				$to_address=$to[$recipient]->email;
				$to_name=$to[$recipient]->displayname;

				$textonly = ($to[$recipient]->config=='text');

				//$email_class &= ( $textonly ) ? $email_text : $email_message;
				$email_class = ( $textonly ) ? $email_text : $email_message;

				if ( XOX_MAILER_TYPE=='pickup' ) {
					$email_class->pickup_file_prefix = sprintf("%s_%05d_%05d_%05d_%07s_%s_",date('YmdHis'),$this->c_issue->id,$this->mail_counter+1,$to[$recipient]->uid,$to[$recipient]->mode_data,($to[$recipient]->mode_id==1?'N':'X'));
				}

				$email_class->SetEncodedEmailHeader("To",$to_address,$to_name);

				/* Do we really need to personalize the message body?
				 * If not, let the class reuse the message body defined for the first recipient above.
				 */
				if(!$email_class->cache_body)
				{
					/* Create a personalized body part. */

					$fingerprint = dechex($this->c_issue->id)."g".dechex($to[$recipient]->uid);
					if ( MAILTEST_ACTIVE ) $fingerprint .= "g".$to[$recipient]->mode_data;

					$tmp->setVar( 'USERID',			  $fingerprint );
					$tmp->setVar( 'USEREMAIL',    $to[$recipient]->email );
					$tmp->setVar( 'USERNAME',			$to[$recipient]->displayname );

					$tmp->setVar( 'USERSALUT',    $to[$recipient]->_details['gender'] );
					$tmp->setVar( 'USERFNAME',    $to[$recipient]->_details['firstname'] );
					$tmp->setVar( 'USERLNAME',    $to[$recipient]->_details['lastname'] );
					$tmp->setVar( 'USERCOMPANY',  $to[$recipient]->_details['company'] );
					$tmp->setVar( 'USERSTREET',   $to[$recipient]->_details['street'] );
					$tmp->setVar( 'USERCOUNTRY',  $to[$recipient]->_details['country'] );
					$tmp->setVar( 'USERZIP',      $to[$recipient]->_details['zip'] );
					$tmp->setVar( 'USERCITY',     $to[$recipient]->_details['city'] );
			 		$tmp->setVar( 'USERTEL',      $to[$recipient]->_details['telephone'] );
					$tmp->setVar( 'USERFAX',      $to[$recipient]->_details['fax'] );
					$tmp->setVar( 'USERMOB',      $to[$recipient]->_details['mobile'] );

					if ( $to[$recipient]->isExpert() ) {
						$tmp->setVar( 'EXPERTS', '<td align="center" nowrap><a href="http://www.bay-as.de/index.php?id=189&fluid='.$fingerprint.'" style="font-size:12px; font-family:Arial, Helvetica, sans-serif; text-decoration:none; color:#ffffff;">&nbsp;&nbsp;&nbsp;<font color="white">BAYAS experts</font><b style="font-size:15px">&nbsp;&nbsp;&nbsp;</b></a></td><td width="1" bgcolor="#ffffff"><img src="http://www.bay-as.de/fileadmin/images/punkt.gif" height="18" width="1"></td>' );
						$tmp->setVar( 'BAYPOINTS', $to[$recipient]->getBayPoints() );
					} else {
						$tmp->setVar( 'EXPERTS', '' );
						$tmp->setVar( 'BAYPOINTS', '' );
					}

					#$message="Hello ".strtok($to_name," ").",\n\nThis message is just to let you know that Manuel Lemos' e-mail sending class is working as expected for sending personalized messages.\n\nThank you,\n$from_name";
					$tmp->parsed_template = $message;
					$email_class->CreateQuotedPrintableTextPart($tmp->show(true),"",$user_text_part);
					if ( !$textonly ) {
						$tmp->parsed_template = $html;
						$email_class->CreateQuotedPrintableHTMLPart($tmp->show(true),"",$user_html_part);
					}

					/* Make the personalized replace the initially empty part */
					if ( !$textonly ) {
						$email_class->ReplacePart($text_part,$user_text_part);
						$email_class->ReplacePart($html_part,$user_html_part);
					} else {
						$email_class->ReplacePart($text_only_part,$user_text_part);
					}
				}
				/* Send the message checking for eventually acumulated errors */
				#if ( !XOX_LOCAL_MODE ) {
					$this->error=$email_class->Send();

					if ($this->mail_counter++ % 50 == 0) set_time_limit(60);

					$this->log->log(strlen($this->error),sprintf('Versand an: %05d %05d %05d %s [%s] : %s : %s',$this->mail_counter,$to[$recipient]->id,$to[$recipient]->uid,$to[$recipient]->displayname,$to[$recipient]->email,$to[$recipient]->mode_data.($to[$recipient]->mode_id==2?'*':' '),$this->error));

					if (strlen($this->error)) {
						$this->mailerror++;
						//echo "\n\nERROR: $this->error<br>\n\n";
						if($this->mailerror >= $this->error_break && $this->mailerror == $this->mail_counter)		break;
					}
				#}
			}

			/* When you are done with bulk mailing call the SetBulkMail function
			 * again passing 0 to tell the all deliveries were done.
			 */
			$email_message->SetBulkMail(0);
			$email_text->SetBulkMail(0);

			if (strlen($this->error)) return false;
			return true;

		}
	}	// finish class xoxLemosNewsletterMailer

?>