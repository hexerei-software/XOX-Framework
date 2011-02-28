<?php

$basedir = dirname(__FILE__);
$rootdir = dirname($basedir);

require_once("$rootdir/functions.php");
require_once("$basedir/http.php");

class cHttpParser
{
	var $aText  = array();
	var $protocol = 'http';
	var $host   = '';
	var $source = '';
	var $page   = '';
	var $pagecontent = '';

	function cHttpParser($host,$source,$protocol='http')
	{
		$this->host   	= $host;
		$this->source		= $source;
		$this->protocol	= $protocol;
	}  //  cHttpParser

	function ReadPage()
	{
		$this->pagecontent="";

		set_time_limit(0);
		
		$http=new http_class;
		$http->timeout=0;
		$http->data_timeout=0;
		$http->debug=0;
		$http->html_debug=1;

		$url=$this->protocol."://".$this->host."/";

		$error=$http->GetRequestArguments($url,$arguments);
		//$arguments["Headers"]["Pragma"]="nocache";
		$arguments["RequestURI"] = $this->source;

		//echo HtmlEntities($arguments["HostName"]);
		flush();
		$error=$http->Open($arguments);

		if($error=="")
		{
			//echo "Sending request for page: ";
			//echo HtmlEntities($arguments["RequestURI"]);

			flush();
			$error=$http->SendRequest($arguments);

			if($error=="")
			{
				//echo "<H2><LI>Request:</LI</H2>\n<PRE>\n".HtmlEntities($http->request)."</PRE>\n";
				//$dummy = $http->request;
				//echo "<H2><LI>Request headers:</LI</H2>\n<PRE>\n";
				for(Reset($http->request_headers),$header=0;$header<count($http->request_headers);Next($http->request_headers),$header++)
				{
					$header_name=Key($http->request_headers);
					if(GetType($http->request_headers[$header_name])=="array")
					{
						for($header_value=0;$header_value<count($http->request_headers[$header_name]);$header_value++)
							;//echo $header_name.": ".$http->request_headers[$header_name][$header_value],"\r\n";
					}
					else
						;//echo $header_name.": ".$http->request_headers[$header_name],"\r\n";
				}
				//echo "</PRE>\n";
				flush();

				$headers=array();
				$error=$http->ReadReplyHeaders($headers);
				if($error=="")
				{
					$redirect = 0;
					//echo "<H2><LI>Response headers:</LI</H2>\n<PRE>\n";
					for(Reset($headers),$header=0;$header<count($headers);Next($headers),$header++)
					{
						$header_name=Key($headers);
						if ( preg_match("/302/",$header_name ) )
						{
							$redirect = 1;
						}
						if(GetType($headers[$header_name])=="array")
						{
							$fp1 = fopen("redirect.txt","a");
							fwrite($fp1,"\r\n");
							for($header_value=0;$header_value<count($headers[$header_name]);$header_value++)
							{
								;
								fwrite($fp1,$headers[$header_name][$header_value]."\r\n");
								//echo "!".$header_name."!".": ".$headers[$header_name][$header_value],"\r\n\n";
								//echo "<br>";
								preg_match_all("@\/\/(.*?)\/@",$headers[$header_name][$header_value],$temp);
								//echo $temp[1][0];
								$this->source = $headers[$header_name][$header_value];
								$this->source = preg_replace("@.*?\/\/.*?\/@","/",$this->source);
								$this->host = $temp[1][0];
								//echo $this->host."<br>";
								//echo $this->source."<br>";
							}
							fclose($fp1);
						}
						else
						{
							//echo $header_name.": ".$headers[$header_name],"\r\n";
							if ( $header_name == "location" )
							{
								//echo "Neue Adresse: ".$headers[$header_name],"\r\n";
								preg_match_all("@\/\/(.*?)\/@",$headers[$header_name],$temp);
								//echo $temp[1][0];
								$this->source = $headers[$header_name];
								$this->source = preg_replace("@.*?\/\/.*?\/@","/",$this->source);
								$this->host = $temp[1][0];
								//echo $this->host."<br>";
								//echo $this->source."<br>";
							}
						}
					}
					flush();

					if ( $redirect == 0)
					{
						for(;;)
						{
							$error=$http->ReadReplyBody($body,2048);
							if($error!=""|| strlen($body)==0)
								break;
							$this->pagecontent = $this->pagecontent.$body;
						}
						flush();
					}
				}
			}
			$http->Close();
		}
		if(strlen($error))
		{
			echo "<CENTER><H2>Error: ",$error,"</H2><CENTER>\n";
			$time = $datum = date("Y.m.d.H.i.s",time());
			$fp = fopen("err.txt","a");
			fwrite($fp,"Am: ".$time."\r\n");
			fwrite($fp,"Server: ".$this-host."\r\n");
			fwrite($fp,"Fehler: ".$error."\r\n");
			fwrite($fp,"\r\n");
			fclose($fp);
		}
		if ( $redirect == 1 )
		{
			$this->ReadPage();
		}
	}  //  ReadPage

	function ParsePage() {
		//
	}  //  parse()

  function savefile($filename) {
		echo @file_get_contents("save.html");
  }
}

?>