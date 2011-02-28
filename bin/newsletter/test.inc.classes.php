<?php      

	require_once (XOX."/test/class.test.php");
require_once ("inc.classes.php");

class testSubscriber extends xoxTestTask {
	function testSubscriber() {
		parent :: xoxTestTask('testSubscriber', 'xoxTestTask');
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

			$c = new cSubscriber();
      //mydump($c,"Subscriber");
			$this->assert(!$c->save(), "isValid accepted default values");
			$c->email = "daniel@hexerei.net";
			$this->assert(!$c->save(), "isValid accepted email only");
			$c->domain_id = 15;
			$this->assert($c->save(), "hmmm... should have saved :( ");
      
			// test if save works fine
			if (empty ($c->id) || $c->id == 0) {
				$this->error("what the f.... should have saved and i have no IDea :(");
        $this->success = false;        
				//check for email
				$this->assert($c->loadEmail("daniel@hexerei.net"), "error trying to load by email  :( ");
        
				$this->assert(($c->domain_id == 15), 'domain_id is not equal after save');
				$this->assert(($c->email == 'daniel@hexerei.net'), 'email is not equal after save');

			} else {
				// remember id and clear object
				$myid = $c->getID();
  //              mydump($myid,"Subscriber");
				$c = 0;
				// reread and check values
				$c = new cSubscriber($myid);
//mydump($c,"Subscriber");
				$this->assert(($c->domain_id == 15), 'domain_id is not equal after save');
				$this->assert(($c->email == 'daniel@hexerei.net'), 'email is not equal after save'.$c->email);

				//set password
				$c->setPassword('test');
				$this->assert(($c->passwd == 'test'), 'setPassword didnot set any.');
				//update
				$this->assert($c->save(), "hmmm... should have UPDATED :( ");

				//delete record
				//$this->assert($c->delete(),"hmmm... should have deleted :( ");

			}

		}
		return $retVal;
	}

}


class testNewsletter extends xoxTestTask {
    function testNewsletter() {
        parent :: xoxTestTask('testNewsletter', 'xoxTestTask');
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

            // test isValid
            
	$this->assert(TRUE, "Assert auf TRUE");
            $this->assert(FALSE, "Assert auf FALSE");
            
            $c = new cNewsletter();
      //mydump($c,"Newsletter");
            //$this->assert(!$c->save(), "isValid accepted default values");
            $c->name = "newsletter ".date('YmdHis');
            //$this->assert(!$c->save(), "isValid accepted name only");
            $c->domain_id = 15;
            //mydump($c,"Newsletter");
            //$this->assert($c->isValid(), "hmmm... should have svalid :( ");
            $this->assert($c->save(), "hmmm... should have saved :( ");
      
            // test if save works fine
            if (empty ($c->id) || $c->id == 0) {
                $this->error("what the f.... should have saved and i have no IDea :(");
                $this->success = false;        
                //check for email
                
            } /*else {
                // remember id and clear object
                $myid = $c->getID();
                $c = 0;
                // reread and check values
                $c = new cNewsletter($myid);

                $this->assert(($c->domain_id == 15), 'domain_id is not equal after save');
                $this->assert(($c->name == 'newsletter 1'), 'name is not equal after save');

                
                //delete record
                //$this->assert($c->delete(),"hmmm... should have deleted :( ");

            }*/

        }
        return $retVal;
    }

}


class testIssue extends xoxTestTask {
    function testIssue() {
        parent :: xoxTestTask('testIssue', 'xoxTestTask');
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

            // test isValid
            

            $c = new cIssue();
      //mydump($c,"Issue");
            $this->assert(!$c->save(), "isValid accepted default values");
            $c->title = "Issue 1";
            $this->assert(!$c->save(), "isValid accepted name only");
            $c->newsletter_id = 15;
            $this->assert($c->isValid(), "hmmm... should have valid :( ");
            $this->assert($c->save(), "hmmm... should have saved :( ");
      
            // test if save works fine
            if (empty ($c->id) || $c->id == 0) {
                $this->error("what the f.... should have saved and i have no IDea :(");
                $this->success = false;        
                //check for email
                
            } else {
                // remember id and clear object
                $myid = $c->getID();
                $c = 0;
                // reread and check values
                $c = new cIssue($myid);

                $this->assert(($c->newsletter_id == 15), 'domain_id is not equal after save');
                $this->assert(($c->title == "Issue 1"), 'name is not equal after save');

                
                //delete record
                //$this->assert($c->delete(),"hmmm... should have deleted :( ");

            }

        }
        return $retVal;
    }

}

class testTopic extends xoxTestTask {
    function testTopic() {
        parent :: xoxTestTask('testTopic', 'xoxTestTask');
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

            // test isValid
            

            $c = new cTopic();
      //mydump($c,"Issue");
            $this->assert(!$c->save(), "isValid accepted default values");
            $c->name = "Issue 1";
            $this->assert(!$c->save(), "isValid accepted name only");
            $c->newsletter_id = 15;
            $this->assert($c->save(), "hmmm... should have saved :( ");
      
            // test if save works fine
            if (empty ($c->id) || $c->id == 0) {
                $this->error("what the f.... should have saved and i have no IDea :(");
                $this->success = false;        
                //check for email
                
            } else {
                // remember id and clear object
                $myid = $c->getID();
                $c = 0;
                // reread and check values
                $c = new cTopic($myid);

                $this->assert(($c->newsletter_id == 15), 'domain_id is not equal after save');
                $this->assert(($c->name == "Issue 1"), 'name is not equal after save');

                
                //delete record
                //$this->assert($c->delete(),"hmmm... should have deleted :( ");

            }

        }
        return $retVal;
    }

}



class testDomain extends xoxTestTask {
	function testDomain() {
		parent :: xoxTestTask('testDomain', 'xoxTestTask');
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

		}
		return $retVal;
	}

}

class testNewsletterSystem extends xoxTestBase {
	function testNewsletterSystem() {
		parent :: xoxTestBase('testNewsletterSystem', 'xoxTestBase');
	}
	function init() {
    if ( !$this->initialized ) {
       $this->initialized = true;
        $this->logger = new xoxLogger('debug', LOG_MSG_ALL);
        #$this->logger->buffered = true;
        $this->logger->setID($this->name);
        $this->logger->begin($this->name);
        return parent :: init();
    }
    return TRUE;
	}
	function run($tasks = 0) {
		if ($tasks != 0 && is_array($tasks)) {
			foreach ($tasks as $test) {
				//mydump($test);
				$this->tasks[$test]->run();
				//$test->run();
			}
		} else {
			$retVal = parent :: run();
		}
		$this->logger->end($this->initialized, 'ran all tests');
		#$this->logger->write_buffer('TEST FEWO CLASSES','daniel@hexerei.net');
		return $retVal;
	}

	function showTasks() {
		$html = '';
		if (is_array($this->tasks)) {
          
			$html .= '<fieldset><legend>Alle Newsletter Tests</legend>';
			$html .= '<div class="required">Markieren Sie die Checkboxen um den Test auszuwählen.</div><br />';
			$html .= '<table>';
			$html .= '<tr><td width="80">Auswahl</td><td width="300">Name</td><td>Klassenname</td><td>Status</td></tr>';
			$i = true;
			foreach ($this->tasks as $test) {
            (!empty($_GET['tests'][$test->name])||!empty($_GET['All'])?$check=' checked':$check='');
				$html .= '<tr'. ($i ? ' style="background:#ccebff;"' : '').'>'."\n";
				$html .= '<td><input type="checkbox" name="tests['.$test->name.']" value="'.$test->name.'" '.$check.'/>&nbsp;</td><td>';
				$html .= '<h2>'.$test->name.'</h2><br /></td><td>'.$test->classname.'</td><td>'.($test->success?'<div style="color:#008000">OK</div>':'<div style="color:#FF0000">FAILED</div>').'</td></tr>';
				($i ? $i = true : $i = false);
			}
			$html .= '</table></fieldset>';
		}

		return ($html);
	}
}

?>          
  
  
  