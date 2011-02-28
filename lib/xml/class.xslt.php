<?php

class XSLT {

	static public function Transform($xml,$xsl) {
	
		$xml_file = "file://" . $xml;
		$xmlDoc = new DOMDocument();
		$xmlDoc->load($xml_file);

		$xsl_file = "file://" . $xsl;
		$xslDoc = new DOMDocument();
		$xslDoc->load($xsl_file);

		$proc = new XSLTProcessor();
		$proc->importStylesheet($xslDoc);
		return $proc->transformToXML($xmlDoc);

	}
}

?>