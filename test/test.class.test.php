<?php

	require_once("class.test.php");

	
	class taskA extends xoxTestTask
	{
		function taskA() {
			parent::xoxTestTask('taskA', 'xoxTestTask');
		}

		function init() {
			if ( !$this->initialized ) {
				$this->initialized = true;
				$this->message("Initialized");
			}
			return $this->initialized;
		}

		function run() {
			$retVal = $this->init();
			if ( $retVal ) {
				$this->message("Running");
			}
			return $retVal;
		}

	}

	class taskB extends xoxTestTask
	{
		function taskB() {
			parent::xoxTestTask('taskB', 'xoxTestTask');
		}

		function init() {
			if ( !$this->initialized ) {
				$this->initialized = true;
				$this->message("Initialized");
			}
			return $this->initialized;
		}

		function run() {
			$retVal = $this->init();
			if ( $retVal ) {
				$this->message("Running");
			}
			return $retVal;
		}

	}
	
	class testA extends xoxTestBase
	{
		function testA() {
			parent::xoxTestBase('testA', 'xoxTestBase');
		}
		function init() {
			$this->logger = new xoxLogger('daniel@hexerei.net',LOG_MSG_ALL);
			$this->logger->buffered = true;
			$this->logger->setID($this->name);
			$this->logger->begin($this->name);
			return parent::init();
		}
		function run() {
			$retVal = parent::run();
			$this->logger->end($this->initialized,'ran all tests');
			$this->logger->write_buffer('TEST A','daniel@hexerei.net');
			return $retVal;
		}
	}

	$ta = new taskA();
	$tb = new taskB();
	
	$test = new testA();
	
	$test->addTask($ta);
	$test->addTask($tb);
	
	//$test->init();
	$test->run();

?>