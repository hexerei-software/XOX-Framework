<?
class iniRWC {

var $readError;		#VARIABLE FOR READ ERRORS
var $writeError;	#VARIABLE FOR WRITE ERRORS

##################
###READ SECTION###
##################

#FILE READING FUNCTION 
function ReadFile($filename) {
$file=fopen($filename, "r"); #OPEN FILE FOR READING
if($file) {
	while(!feof($file)) {
		$rf[]=fgets($file, 1024);	#ADDING STRING TO THE ARRAY
	}
	fclose($file);
	return $rf;
} else {
	$this->readError="ERROR: Cannot open file $filename";	#ERROR IF CANNOT OPEN FILE
	return FALSE;
}
}

#PARSING INI FILE
function iniParse($file) {

for($i=0; $i<count($file); $i++) {
	$file[$i]=str_replace("\r", "", $file[$i]);	#REMOVE '\r' CHAR
	$file[$i]=str_replace("\n", "", $file[$i]);	#REMOVE '\n' CHAR
	$file[$i]=preg_replace("[^\[(.*)]", "", $file[$i]);	#CLEAR STRING IF IT STARTS WITH ';'
	$file[$i]=preg_replace("[^\;(.*)]", "", $file[$i]);	#CLEAR STRING IF IT STARTS WITH '['
	if($file[$i] != "") {	#IF STRING IS NOT EMPTY
		$param=explode("=", $file[$i]);	#PARSING ON '='
		$param[0]=str_replace(" ", "", $param[0]);		#DELETE ' ' FROM KEY
		$param[0]=str_replace("\t", "", $param[0]);		#DELETE \t FROM KEY
		$param[1]=preg_replace("[^(\t*)]", "", $param[1]);	#DELETE '\t' FROM START OF VALUE
		$param[1]=preg_replace("[^(\ *)]", "", $param[1]);	#DELETE ' ' FROM START OF VALUE
		$out[$param[0]]=$param[1];	#ADDING TO HASH KEY AND ITS VALUE
	}
}

return $out; #RETURN HASH
}

#PARSING PHP FILE
function phpParse($file) {

for($i=1; $i<count($file)-1; $i++) {
	$file[$i]=str_replace("\r", "", $file[$i]);	#REMOVE '\r' CHAR
	$file[$i]=str_replace("\n", "", $file[$i]);	#REMOVE '\n' CHAR
	if($file[$i] != "") {	#IF STRING IS NOT EMPTY
		$param=explode("=", $file[$i]);	#PARSING ON '='
		$param[0]=str_replace("\$", "", $param[0]);	#DELETE '$' FROM KEY
		$param[0]=str_replace(" ", "", $param[0]);	#DELETE ' ' FROM KEY
		$param[0]=str_replace("\t", "", $param[0]);	#DELETE \t FROM KEY
		eregi("\"(.*)\";", $param[1], $varvarr);	#GET VALUE OF VARIABLE
		$param[1]=$varvarr[1];
		$param[1]=str_replace("\\\"", "\"", $param[1]);	#CHANGE '\"' TO '"'
		$param[1]=str_replace("\\n", "\n", $param[1]);	#CHANGE '\\n' TO '\n'
		$param[1]=str_replace("\\r", "\r", $param[1]);	#CHANGE '\\r' TO '\r'
		$out[$param[0]]=$param[1];	#ADDING TO HASH KEY AND ITS VALUE
	}
}

return $out; #RETURN HASH
}

#MAIN FUNCTION OF READING INI
function ReadIni($filename) {

$file=$this->ReadFile($filename);	#READ INI FILE
if(!$file) {
return FALSE;	#IF WAS A ERROR THEN RETURN FALSE
}

return $this->iniParse($file);	#PARSE AND RETURN INI HASH
}

#MAIN FUNCTION OF READING PHP
function ReadPhp($filename) {

$file=$this->ReadFile($filename);	#READ PHP FILE
if(!$file) {
return FALSE;	#IF WAS A ERROR THEN RETURN FALSE
}

return $this->phpParse($file);	#PARSE AND RETURN INI HASH
}


###################
###WRITE SECTION###
###################

#WRITE SIMPLE INI FILE
function WriteIni($filename, $inihash) {

if($inihash) {
	$file=fopen($filename, "w");
	if($file) {
		foreach($inihash as $key=>$value) {
			fwrite($file, "$key=$value\n");	#WRITE STRING TO FILE
		}
	} else {
		$this->writeError="ERROR: cannot open file $filename for writing";	#ERROR IF CANT WRITE TO FILE
		return FALSE;
	}
	fclose($file);
	return TRUE;
} else {
	$this->writeError="ERROR: Your INI-hash is empty";	#ERROR IF HASH IS EMPTY
	return FALSE;
}

}


###PHP SECTION

#WRITE PHP INCLUDE-LIKE FILE

function WritePhp($filename, $inihash, $prefix="") {

if($inihash) {
	$file=fopen($filename, "w");
	if($file) {
		fwrite($file, "<?\n");
		foreach($inihash as $key=>$value) {
			if(!ereg("[^_a-zA-Z0-9]", $key, $f)) {	#IF VAR NAME CONTAINS ONLY a-z, A-Z, 0-9 AND '_' CHAR
				$value=str_replace("\n", "\\n", $value);	#ADD SLASH BEFORE '\n' CHAR
				$value=str_replace("\r", "\\r", $value);	#ADD SLASH BEFORE '\r' CHAR
				$value=str_replace("\"", "\\\"", $value);	#ADD SLASH BEFORE '"' CHAR
				fwrite($file, "\$"."$prefix$key=\"$value\";\n");	#WRITE STRING TO FILE
			}
		}
		fwrite($file, "\n?>");
	} else {
		$this->writeError="ERROR: cannot open file $filename for writing";	#ERROR IF CANT WRITE TO FILE
		return FALSE;
	}
	fclose($file);
	return TRUE;
} else {
	$this->writeError="ERROR: Your INI-hash is empty";	#ERROR IF HASH IS EMPTY
	return FALSE;
}

}


####################
###CHANGE SECTION###
####################

#CHANGE OF SIMPLE INI FILE
function ChangeIni($filename, $inihash) {
$hash=$this->ReadIni($filename);	#READ AND PARSE INI FILE
if($inihash) {
	foreach($inihash as $key=>$value) {
		$hash[$key]=$value;	#CHANGE VALUE OF HASH
	}
	$this->writeIni($filename, $hash);	#WRITE TO INI FILE
	return TRUE;
} else {
	$this->writeError="ERROR: Your INI-hash is empty";	#ERROR IF HASH IS EMPTY
	return FALSE;
}

}

#CHANGE OF PHP FILE
function ChangePhp($filename, $inihash) {
$hash=$this->ReadPhp($filename);	#READ AND PARSE PHP FILE
if($inihash) {
	foreach($inihash as $key=>$value) {
		$hash[$key]=$value;	#CHANGE VALUE OF HASH
	}
	$this->writePhp($filename, $hash);	#WRITE TO PHP FILE
	return TRUE;
} else {
	$this->writeError="ERROR: Your INI-hash is empty";	#ERROR IF HASH IS EMPTY
	return FALSE;
}

}

}

?>