<?php 
// #######################################
// ######  Debug-MODULE !! functions are only needed while in progress and can be excluded after going online
// #######################################
function sqlMsg ($head = "", $text = "")
{
    if ($text != "" || $head != "") echo "<hr><br><b>$head</b> - $text";
    echo "<br>Error: <b>" . mysql_errno() . "</b> - " . mysql_error() . "<br>";
} 

function msg ($head = "", $text = "")
{
    echo "<hr><br><b>$head</b> - $text";
} 

function infod ($string)
{
    global $show_infod;
    if ($show_infod) infop ($string);
} 

function infop ($string)
{ 
    // echo htmlspecialchars("<br><b> $string </b><br>");
    echo ("<b> $string </b><br>");
} 

function infopp ($string)
{ 
    // echo htmlspecialchars("<br><b> $string </b><br>");
    echo ("<br><b> $string </b><br>");
} 
// ##################################################################################################
// #######################################
// ######  PHP Timer to optimize speed
// #######################################
/**
 * Usage
 * My PHP script to use this object now looks like this:
 * 
 * $timer = new PHP_timer;
 * $timer->start();
 * $timer->addmarker("marker 1");
 * $timer->stop();
 * $timer->debug();
 * $timer->showtime();
 * 
 * Usage End
 */

class PHP_timer {
    // array to store the information that we collect during the script
    // this array will be manipulated by the functions within our object
    var $points = array(); 
    // call this function at the beginning of the script
    function start()
    { 
        // see the addmarker() function later on
        $this->addmarker("Start");
    } 
    // end function start()
    // call this function at the end of the script
    function stop()
    { 
        // see the addmarker() function later on
        $this->addmarker("Stop");
    } 
    // end function stop()
    // this function is called to add a marker during the scripts execution
    // it requires a descriptive name
    function addmarker($name)
    { 
        // call the jointime() function and pass it the output of the microtime() function
        // as an argument
        $markertime = $this->jointime(microtime()); 
        // $ae (stands for Array Elements) will contain the number of elements
        // currently in the $points array
        $ae = count($this->points); 
        // store the timestamp and the descriptive name in the array
        $this->points[$ae][0] = $markertime;
        $this->points[$ae][1] = $name;
    } 
    // end function addmarker()
    // this function manipulates the string that we get back from the microtime() function
    function jointime($mtime)
    { 
        // split up the output string from microtime() that has been passed
        // to the function
        $timeparts = explode(" ", $mtime); 
        // concatenate the two bits together, dropping the leading 0 from the
        // fractional part
        $finaltime = $timeparts[1] . substr($timeparts[0], 1); 
        // return the concatenated string
        return $finaltime;
    } 
    // end function jointime()
    // this function simply give the difference in seconds betwen the start of the script and
    // the end of the script
    function showtime()
    { 
        // echo bcsub($this->points[count($this->points)-1][0],$this->points[0][0],6);
        echo "<br>- <u>From Start to End: <b>" . round ($this->points[count($this->points)-1][0] - $this->points[0][0], 5);
        echo "</b> sec.</u><br>";
    } 
    // end function showtime()
    // this function displays all of the information that was collected during the
    // course of the script
    function debug()
    {
        echo "Script execution debug information:";
        echo "<table border=0 cellspacing=5 cellpadding=5>\n"; 
        // the format of our table will be 3 columns:
        // Marker name, Timestamp, difference
        echo "<tr><td><b>Marker</b></td><td><b>Time</b></td><td><b>Diff in sec.</b></td></tr>\n"; 
        // the first row will have no difference since it is the first timestamp
        echo "<tr>\n";
        echo "<td>" . $this->points[0][1] . "</td>";
        echo "<td>" . $this->points[0][0] . "</td>";
        echo "<td>-</td>\n";
        echo "</tr>\n"; 
        // our loop through the $points array must start at 1 rather than 0 because we have
        // already written out the first row
        for ($i = 1; $i < count($this->points);$i++) {
            echo "<tr>\n";
            echo "<td>" . $this->points[$i][1] . "</td>";
            echo "<td>" . $this->points[$i][0] . "</td>";
            echo "<td>"; 
            // write out the difference between this row and the previous row
            // echo bcsub($this->points[$i][0],$this->points[$i-1][0],6);
            echo round ($this->points[$i][0] - $this->points[$i-1][0], 4);
            echo "</td>";
            echo "</tr>\n";
        } 
        echo "</table>";
    } // end function debug()
} // end class PHP_timer
// $timer = new PHP_timer;
// $timer->start();
// ##################################################################################################
// #######################################
// ######  Print Array form PHP Net
// #######################################
function dump_array($array)
{
    if (gettype($array) == "array") {
        echo "<ul>";
        while (list($index, $subarray) = each($array)) {
            echo "<li><b>$index </b><code>=&gt;</code> ";
            dump_array($subarray);
            echo "</li>";
        } 
        echo "</ul>";
    } else echo $array;
} 
// ##################################################################################################
// #######################################
// ######  MyDump form PHP Net
// #######################################
function mydump1 ($thing, $maxdepth, $depth)
{
    $fmt = sprintf ("%%%ds", 4 * $depth);
    $pfx = sprintf ($fmt, "");
    $type = gettype($thing);
    if ($type == 'array') {
        $n = sizeof($thing);
        echo "$pfx<B>array($n) =&gt;</B><BR />";
        foreach (array_keys($thing) as $key) {
            echo " $pfx<B>[$key] =&gt;</B>\n";
            mydump1 ($thing[$key], $maxdepth, $depth + 1);
        } 
    } else if ($type == 'string') {
        $n = strlen($thing);
        echo "$pfx<B>string($n) =&gt;</B>\n";
        echo "$pfx\"" . htmlentities($thing) . "\"\n";
    } else if ($type == 'object') {
        $name = get_class($thing);
        echo "$pfx<B>object($name) =&gt;</B>\n";
        $methodArray = get_class_methods($name);
        foreach (array_keys($methodArray) as $m) {
            echo " $pfx<B>method($m) =&gt;</B> $methodArray[$m]\n";
        } 
        $classVars = get_class_vars($name);
        foreach (array_keys($classVars) as $v) {
            echo " $pfx<B>default =&gt; $v =&gt;</B>\n";
            mydump1($classVars[$v], $maxdepth, $depth + 2);
        } 
        $objectVars = get_object_vars($thing);
        foreach (array_keys($objectVars) as $v) {
            echo " $pfx<B>$v =&gt;</B>\n";
            mydump1($objectVars[$v], $maxdepth, $depth + 2);
        } 
    } else if ($type == 'boolean') {
        if ($thing) {
            echo "$pfx<B>boolean(true)</B><BR />";
        } else {
            echo "$pfx<B>boolean(false)</B><BR />";
        } 
    } else {
        echo "$pfx<B>$type($thing)</B><BR />";
    } 
} 

function mydump ($thing, $msg = "", $maxdepth = -1)
{
    if ($msg) msg ($msg);
    echo "<PRE>\n";
    mydump1 ($thing, $maxdepth, 0);
    echo "</PRE>\n";
} 
// ##################################################################################################
// #######################################
// ######  MyDump form PHP Net
// #######################################
function myMyDump ($data, $text = "", $functions = 1)
{ 
    // pray - short for print_array
    // Traverse the argument array/object recursively and print an ordered list.
    // Optionally show function names (in an object)
    // NB: This is a *** HUGE SECURITY HOLE *** in the wrong hands!!
    // It prints all the variables it can find
    // If the argument is $GLOBALS this will include your database connection information, magic keys and session data!!
    if ($functions != 0) {
        $sf = 1;
    } else {
        $sf = 0 ;
    } // This kluge seemed necessary on one server.
    if (isset ($data)) {
        if ($text != "") echo "<hr /><br /><b><font color=\"red\">$text</font></b>";
        if (is_array($data) || is_object($data)) {
            if (count ($data)) {
                echo "<OL>\n";
                while (list ($key, $value) = each ($data)) {
                    $type = gettype($value);
                    if ($type == "array" || $type == "object") {
                        printf ("<li>(%s) <b>%s</b>:\n", $type, $key);
                        myMyDump ($value, "", $sf);
                    } elseif (eregi ("function", $type)) {
                        if ($sf) {
                            printf ("<li>(%s) <b>%s</b> </LI>\n", $type, $key, $value); 
                            // There doesn't seem to be anything traversable inside functions.
                        } 
                    } else {
                        if (!$value) {
                            $value = "(none)";
                        } 
                        printf ("<li>(%s) <b>%s</b> = \"%s\"</LI>\n", $type, $key, htmlentities($value));
                    } 
                } 
                echo "</OL>end.\n";
            } else {
                echo "(empty)";
            } 
        } else var_dump ($data);
    } 
} // function
// ##################################################################################################
// #######################################
// ######  MyDump form PHP Net
// #######################################
function dp($call, $cname)
{ 
    // call: the variable you want to print_r
    // cname: the label for your debugging output
    // global $debug;
    // if ($debug){
    echo $cname . ":<pre>";
    if (!is_array($call) && !is_object($call)) {
        $call = htmlspecialchars($call);
    } 
    print_r($call);
    if (is_array($call) || is_object($call)) {
        reset($call);
    } 
    echo "</pre><hr>"; 
    // }
} 

function pre ($func, $para1, $para2=""){
	echo "<pre>";
	echo "<hr><b>$para2</b><br />";
	$func ($para1);
	echo "</pre>";
	
}
?>
