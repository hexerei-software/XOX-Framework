<?php


require_once ("test.inc.classes.php");
require_once ("test.themes.php");

$nlt = new testThemes();
$ts = new testSubscriber();
$td = new testDomain();
$tn = new testNewsletter();
$ti = new testIssue();
$tt = new testTopic();

$test = new testNewsletterSystem();
//mydump($test,"first");
$test->addTask($nlt);
$test->addTask($ts);
$test->addTask($td);
$test->addTask($tn);
$test->addTask($ti);
$test->addTask($tt);

//mydump($_GET,"get");
if(!empty($_GET['All']) || !empty($_GET['runTest']) || !empty($_GET['tests'])){
if (!empty($_GET['All'])) {
    $test->init();
    $test->run();
}
if (!empty($_GET['runTest']) && !empty($_GET['tests'])) {

	$test->init();
	$test->run($_GET['tests']);
	//mydump($test,"second");
}
}
//mydump($test,"test");
echo '<script type="text/javascript">';
echo "<!-- \n";
echo 'function deselectAll(bool){';
echo 'for (var el in document.forms[0].elements){';
echo ' if(document.forms[0].elements[el].type == \'checkbox\'){';
echo '  if (document.forms[0].elements[el].checked == bool){';
echo '     document.forms[0].elements[el].checked = !bool;}}} ';
echo '}'."\n";
echo '//--></script>';

echo ("\n".'<form action="index.php" method="GET" enctype="text/plain" name"testing">');
echo ('<input type="hidden" name="p" value="de/6/3"/>');
echo ($test->showTasks());
echo '<input type="submit" name="runTest" value="run"/><br />';
echo '<input type="submit" name="All" value="All Tests"/>&nbsp;';
echo '<input type="button" name="deselect" value="Deselect all Tests" onclick="deselectAll(true); '."\n";
echo ' "/>&nbsp;';
echo "</form>";

//phpinfo();

?>




