<?php

   require_once (XOX."/test/class.test.php");
	require_once("themes/class.newsletter_themes.php");

class testThemes extends xoxTestTask {
	function testThemes() {
		parent :: xoxTestTask('testThemes', 'xoxTestTask');
	}

	function init() {

		if (!$this->initialized) {
			$this->initialized = true;
			$this->message("Initialized");
		}
		return $this->initialized;
	}

	function run() {
		$retVal = $this->init();
		if ($retVal) {
			$this->message("Running");

			$this->assert(TRUE, "Assert auf TRUE");
			$this->assert(FALSE, "Assert auf FALSE");
			// test isValid
			//$d = new cSubscriber(33);

//			$this->assert($d->delete(), "hmmm... should have delete :( ");

			$t = new xoxNewsletterThemeManager();

			$this->assert(!$t->getThemes(), "themes");


		}
		return $retVal;
	}

}

?>

