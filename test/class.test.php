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
	/**

	base test classes.

	defines the base classes from which unit tests are derrived.
	this should be the base of all tests implemented for automated
	framework, library or application tests.

	XOX has to be defined to XOX root path (including xox dirname)

	@file class.test.php
	@class xoxTestTask A single test task
	@class xoxTestBase A test with a collection of tasks
	@author	<a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>

	*/



	require_once(XOX."/lib/security/class.logger.php");

  // message types
	
	/** normal log type */
	define( 'TEST_LOG_DEFAULT', 0 );
	/** message log type */ 
  define( 'TEST_LOG_MESSAGE', 1 );
	/** error log type */
  define( 'TEST_LOG_ERROR',   2 );
	/** assert log type */
  define( 'TEST_LOG_ASSERT',  3 );
	/** debug log type */
  define( 'TEST_LOG_DEBUG',   4 );

	/**

	 a single test task.

	 defines the base class from which unit tests are derrived.
	 a task resembles a thread that can be initialized and run.

	 @author	<a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
	 @version 1.0
	 @date 06.05.2004

	*/
	class xoxTestTask
	{
		var $name;          /**< the name of the task being perfored */
		var $classname;     /**< the name of the class being tested */
		var $logger;        /**< handle to the logger instance used for logging */
		var $silent;        /**< boolean stating if any messages are generated */
		var $verbose;       /**< boolean stating if messages should be echoed */
		var $initialized;		/**< boolean stating if task was initialized */
    var $success;       /**< boolean stating if test ran successfully */

		/** constructor.
		 *	initializes the class members
		 *	@param name The internal name of the test task
		 *	@param classname The name of the class being tested
		 *	@param silent Boolean to switch logging and output on/off
		 */
		function xoxTestTask($name='xoxTestTask', $classname='xoxTestTask', $silent=false) {
			$this->name = $name;
			$this->classname = $classname;
			$this->logger = false;
			$this->silent = $silent;
			$this->initialized = false;
			$this->verbose = true;
      $this->success = true;
		}

		/** initialize task */
		function init() {
			if ( !$this->initialized ) {
				$this->initialized = true;
			}
			return $this->initialized;
		}

		/** run task */
		function run() {
			$retVal = init();
			return $retVal;
		}

		/** log mesage.
		 *	outputs given message to the logger
		 *	@param message The message to log
		 *	@param condition The condition which makes message to an error message if false
		 *	@param type The message type
		 */
		function log($message,$condition=true,$type=TEST_LOG_DEFAULT) {
			if ( !$this->silent ) {
				if ( !$this->logger ) {
					$this->logger = new xoxLogger($this->name);
					$this->logger->setId($this->name);
					if ( $this->verbose ) $this->logger->log_events = $this->logger->log_events|LOG_MSG_ECHO;
				}
				switch($type) {
					case TEST_LOG_MESSAGE:	$this->logger->message($message);           break;
					case TEST_LOG_ERROR:    $this->logger->error($message);             break;
					case TEST_LOG_ASSERT:   $this->logger->assert($condition,$message);	break;
					case TEST_LOG_DEBUG:    $this->logger->debug($message);             break;
					default:                $this->logger->log(!$condition,$message);   break;
				}
			}
		}

		/** log message
		 *	outputs given message to the logger
		 *	@param message The message to log
		 */
		function message($message) {
			$this->log($message,true,TEST_LOG_MESSAGE);
		}

		/** log error
		 *	outputs given message to the logger
		 *	@param message The message to log
		 */
		function error($message) {
			$this->log($message,false,TEST_LOG_ERROR);
		}

		/** log assert
		 *	outputs given message to the logger if condition is false
		 *	@param condition The condition to evaluate
		 *	@param message The message to log
		 */
		function assert($condition,$message) {
			$this->log($message,$condition,TEST_LOG_ASSERT);
		}

		/** log debug
		 *	outputs given message to the logger
		 *	@param message The message to log
		 */
		function debug($message) {
			$this->log($message,true,TEST_LOG_DEBUG);
		}

		/** ouput task as string */
		function toString() {
			return $this->name;
		}

	}	// finish class xoxTestTask


	/**

		the base test class running several tasks

	 defines the base class from which unit tests are derrived.
	 here you can add a collection of tasks for combined tests.

	 @author	<a href="mailto:daniel@hexerei.net">Daniel Vorhauer</a>
	 @version 1.0
	 @date 06.05.2004

	*/
	class xoxTestBase extends xoxTestTask
	{
		var $tasks;		/**< array of tasks being performed in this test */

		function xoxTestClass($name='xoxTestClass', $classname='xoxTestClassClass', $silent=false) {
			parent::xoxTestTask($name, $classname, $silent=false);
			$this->tasks = array();
		}

		/** add task to list of tasks to be performed
		 *	@param task Reference to the task instance to perform
		 *	@param name Name of the task to perform
		 */
		function addTask(&$task, $name='') {
			if ($name=='') $name = $task->name;
			$this->tasks[$name]=$task;
		}

		/** initialize all tasks of test */
		function init() {
			if ( !$this->initialized ) {
				$this->initialized = true;
				if ( is_array($this->tasks) ) {
					foreach($this->tasks as $name=>$task) {
						if (!$this->tasks[$name]->init()) {
							$this->log("INIT FAILED ".$name);
							$this->initialized = false;
							break;
						}
					}
				}
			}
			return $this->initialized;
		}

		/** run all tasks of test */
		function run() {
			$retVal = $this->init();
			if ( $retVal ) {
				if ( is_array($this->tasks) ) {
					foreach($this->tasks as $name=>$task) {
						if (!$this->tasks[$name]->run()) {
							$this->log("RUN FAILED ".$name);
							$retVal = false;
							break;
						}
					}
				}
			}
			return $retVal;
		}

	}	// finish class xoxTestBase

?>