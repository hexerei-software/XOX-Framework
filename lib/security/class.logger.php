<?php

	/*

	 XOX PHP Library 2.0
	 (c) 1997-2004 hexerei software creations

	 This library is not yet free software. If you are a member of the
	 hexerei software development team, you have a non-exclusive right
	 to use this library for projects that are either for the hexerei
	 or one of it's customers under the label of hexerei. You may also
	 use the library for your own purposes if hexerei has granted you
	 a license to do so.

	 If you have received a copy of this source without explicit right
	 or licence from hexerei, then you may not modify it or reuse it
	 in any form without prior notice to hexerei. Most likely you have
	 received a copy along with an implemented application from the
	 hexerei or one of its licensees and herby have no right to reuse,
	 modify or publish this code under any terms other than the rights
	 you have acquired for the given application.

	 This library is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
	 MERCHANTABILITY of FITNESS FOR A PARTICULAR PURPOSE.

	 Under no circumstances may you remove this header and or any
	 copyright notice which marks hexerei software creations as the
	 owner and author of this library and its sources.

	 Daniel Vorhauer
	 daniel@hexerei.net

	*/

	// === CONSTANTS =============================================================

  /*
   * diffrent types of loggers
   */
  
	/** log to system log */
	define( 'SYSTEM_LOGGER',  0 );
  /** log to email */
	define( 'EMAIL_LOGGER',   1 );
	/** log to remote port */
  define( 'REMOTE_LOGGER',  2 );
	/** log to file */
  define( 'FILE_LOGGER',    3 );

  /*
   * log message identifiers
   */

  /** log errors */
	define( 'LOG_MSG_ERRORS',   1 );
	/** log messages */
  define( 'LOG_MSG_MESSAGES', 2 );
	/** log time tracking */
  define( 'LOG_MSG_TRACKING', 4 );
	/** log debug */
  define( 'LOG_MSG_DEBUG',    8 );
	/** log output */
  define( 'LOG_MSG_ECHO',     16);
	/** log all of the above */
  define( 'LOG_MSG_ALL',      31);
	/** log nothing */
  define( 'LOG_MSG_NONE',     32);

  // === CLASS =================================================================

	/**
	 Allows simple logging functionality for use in any script.
	 Handles debug (log) and error (err) output to seperate files.
	 
	 @author <a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
	 
	 allow nested begin(..)->end(..) calls for performance-logs
	 @author Lutz Dornbusch
	 @date 2005-03-23

	*/
  class xoxLogger
  {

    // === DATA ================================================================

    /**
     * flag with event bits showing what to log
     */
    var $log_events;

		/**
		 * flag stating if to show user before remote i.p.
		 */
		var $log_user;

    /**
     * flags for the default mode 
     */
    var $default_mode;
    
		/**
     * flags for the error mode
     */
    var $error_mode;

		/**
     * boolean to enable buffering of log messages
     */
		var $buffered = false;

		/**
     * variable used for buffering
     */
  	var $buffer_string;


    /**
     * filename, mail- or port-adresse for the default log stream
     */

    var $default_destination;
    
		/**
     * filename, mail- or port-adresse for the error log stream
     */
    var $error_destination;


    /*
     * labels for message printing
     */

    /** id stamp printed before every message */
		var $log_id;                           
    /** timer (now array for nested calls) */
		var $arr_log_time=array();
    /** current timed task (now array for nested calls, MUST be updated ALWAYS with  
		 $this->arr_log_time !! (both are used as a stack) */
		var $arr_current_task=array();

		/** username of user performing the script */
		var $remote_user;
		/** ip addres from host which called the script */
		var $remote_addr;

    // === CODE ================================================================

    /**
     * constructor creates logger with given filename and mode
     * @param fileName name of the file to use for logging
     * @param mode the mode of the logger
		 * @param user optional username to add to log entries
     */
    function xoxLogger( $name='', $mode=-1, $user=false )
    {
      // set event parameter
			if ( $mode < 0 ) {
      	$this->log_events           = LOG_MSG_ERRORS|LOG_MSG_MESSAGES;
			} else {
      	$this->log_events           = $mode;
			}

			// get username or anonymous
			$this->remote_user 						= (!empty($GLOBALS['user_name']))
																			? $GLOBALS['user_name']
																			: ((!empty($_SERVER['REMOTE_USER']))
																			? $_SERVER['REMOTE_USER'] 
																			: ((!empty($_SERVER['USERNAME']))
																			? $_SERVER['USERNAME']
																			: 'anonymous'));
			// get ip addres or localhost			
			$this->remote_addr 						= (!empty($_SERVER['REMOTE_ADDR']))
																			? $_SERVER['REMOTE_ADDR']
																			: 'localhost';

			$this->log_user 							= $user;


      /*
       * check name parameter
       */

      // check if we have an email adress
      if ( ereg( '.+@.+\..+', $name ) ) {

        $this->default_mode         = EMAIL_LOGGER;
        $this->default_destination  = $name;
        $this->error_mode           = EMAIL_LOGGER;
        $this->error_destination    = $name;

      // else could be a remote adress ('localhost', 'http://'*, ip-adress or server:port)
      } else if ( ereg( '^(localhost|http://)|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})|.+:[0-9]{1,6}', $name ) ) {

        $this->default_mode         = REMOTE_LOGGER;
        $this->default_destination  = $name;
        $this->error_mode           = REMOTE_LOGGER;
        $this->error_destination    = $name;

      // assume filename
      } else if ( strlen($name) > 0 ) {

        //if ( ereg( '(.+[/\\].+)\..+', $name, $regs ) ) {
        //  $name = $regs[1];
        //}
        $this->default_mode         = FILE_LOGGER;
        $this->default_destination  = $name.'.log';
        $this->error_mode           = FILE_LOGGER;
        $this->error_destination    = $name.'.err';

      // if no name parameter then set to default
      } else {

        $this->default_mode         = SYSTEM_LOGGER;
        $this->default_destination  = '';
        $this->error_mode           = SYSTEM_LOGGER;
        $this->error_destination    = '';

      }

      /*
       * set log file according to event settings
       */

      // if we don't want to log anything go back to default
      if ( ($this->log_events & LOG_MSG_NONE)
				|| !(($this->log_events & LOG_MSG_MESSAGES)
        || ($this->log_events & LOG_MSG_TRACKING)
        || ($this->log_events & LOG_DEBUG))) {
        $this->default_mode         = SYSTEM_LOGGER;
        $this->default_destination  = '';
      }

			// if we don't want to log errors go back to default
      if ( !($this->log_events & LOG_MSG_ERRORS) ) {
        $this->error_mode           = $this->default_mode;
        $this->error_destination    = $this->default_destination;
      }

    }


  //--- timed logging code --------------------------------------------------

    /**
     * print log entry with start message and start measuring time.
     * @param task the current taskname to process and time
     */
    function begin( $task ) {
      if ( $this->log_events & LOG_MSG_TRACKING ) {
        // set current task
        // build message and log
				#$this->log(FALSE, '======================================================================');
				$this->log(FALSE, 'TIMER '.$this->log_id.': *** START *** Starting '.$task.'...');
        // remember current time in array and also push $task to array (which is important!)
        array_push($this->arr_current_task,$task);
        array_push($this->arr_log_time, microtime());
      }
    }

    /**
     * end timed log entry with end message and resulting duration
     * @param  success  if true task was successful else it failed
     * @param  explanation  optional text to add to log entry
     */
    function end( $success, $explanation='' )
    {
      if ( $this->log_events & LOG_MSG_TRACKING ) {
				// retrieve Data from both arrays (thats why they have to be filled synchron!!Better solution: 1 array with two entries)
				$strStartTime	=array_pop($this->arr_log_time);
				$strTask			=array_pop($this->arr_current_task);
        // calculate duration
        $tstart   = explode(' ',$strStartTime);
        $tend     = explode(' ',microtime());
        $duration = (int)($tend[1]) - (int)($tstart[1]);
        $duration += ( (double)($tend[0]) - (double)($tstart[0]) );
        // build message and log
        $this->log(FALSE, 'TIMER '.$this->log_id.': *** '.(($success)?'PASSED':'FAILED')
				.' *** Completed '.$strTask." in $duration seconds.".(($explanation)?" // $explanation":''));
				#$this->log(FALSE, '======================================================================');
      }
    }

  //--- message code ------------------------------------------------------

    /**
     * set identifaction to be displayed for each output
     * @param  id  the string to display
     */
    function setID( $id ) {
      $this->log_id = $id;
    }

    /**
     * log the given message as default or error log entry
     * @param  isError  true if this is en error log entry
     * @param  message  the message to log
     */
    function log( $isError, $message ) {

			// return if logging is deactivated
      if ($this->log_events & LOG_MSG_NONE) return;

			// echo the message
      if (($this->log_events & LOG_MSG_ECHO)) echo date('YmdHis')." $message<br>";

		$message = date('YmdHis')." ["
			.($this->log_user?$this->remote_user.'@':'')
			."{$this->remote_addr}] $message\n";

  		if ($this->buffered) {
  			$this->buffer_string .= $message;
  		} else {
				// log to either default or error log
      	if ( $isError && ($this->log_events & LOG_MSG_ERRORS) ) {
      	  error_log( $message, $this->error_mode, $this->error_destination );
      	} else {
      	  error_log( $message, $this->default_mode, $this->default_destination );
      	}
		}
    }

		/** write the buffer to the default out, or write a mail
     * @param subject the subject line of the mail
		 * @param from the from address for mail 
     */
   	function  write_buffer($subject='Log',$from='XOX Logger') {
			if (strlen($this->buffer_string) > 0) {
				if ($this->default_mode == EMAIL_LOGGER) {
					$date = date('d.m.y H:m:s');
				 	mail(
						$this->default_destination,
						"$subject ($date)\n",
						$this->buffer_string,
						"From: $from\n"."Content-Type: text/plain\n"."X-Mailer: PHP/".phpversion()
					);
				} else {
					error_log($this->buffer_string, $this->default_mode, $this->default_destination);
				}
			}
		}


    /**
     * just log a message
     * @param  message  the message to log
     */
    function message( $message ) {
      if ( ($this->log_events & LOG_MSG_MESSAGES) )
        $this->log(FALSE, 'INFO '.$this->log_id.': '.$message);
    }

    /**
     * log an error message
     * @param  message  the message to log
     */
    function error( $message ) {
      if ( ($this->log_events & LOG_MSG_ERRORS) )
        $this->log(TRUE, 'ERROR '.$this->log_id.': '.$message);
    }

    /**
     * log message if condition evaluates to false
     * @param  condition  the condition to evaluate
     * @param  message  	the message to log when condition is false
     */
    function assert( $condition, $message ) {
      if ( (($this->log_events & LOG_MSG_DEBUG)||($this->log_events & LOG_MSG_ERRORS)) && !$condition ) {
          $this->log( ($this->log_events & LOG_MSG_ERRORS),
            'ASSERT '.$this->log_id.': '.$message);
      }
    }

    /**
     * log a debug message
     * @param  message  the message to log
     */
    function debug( $message ) {
      if ( ($this->log_events & LOG_MSG_DEBUG) )
        $this->log(FALSE, 'DEBUG '.$this->log_id.': '.$message);
    }

  }
?>
