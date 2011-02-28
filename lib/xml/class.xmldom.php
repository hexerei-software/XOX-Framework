<?
  require_once("class.stack.php");
  require_once("class.xmlelement.php");

/**
	XmlDom is an xml_parser which converts the Document into an tree of
	xmlelements. You can access them over the getDOM()-method, after you
	delivered an xml-String to the parse(..)-method
*/
class cXmlDom {
  
	/** PRIVATE the parser-class */
	var $xml_parser;
	/** the topmost node of the xml-dom */
  var $objCurrentElement;
  /** PRIVATE stack for tracking the nodes while parsing */
	var $stack;

/**
empty constructor
*/
  function XMLDOM() { }

/**
	Function which will parse the delivered xml-String into a xmldom
	and stores this in its internal variables. To retrieve the dom use the
	getDOM()-method
 	@param strXML the complete content of the xml document to parse
*/
  function parse($strXML){
    $this->xml_parser = xml_parser_create();
    $this->stack=new cStack();
    $this->objCurrentElement=null;

    xml_set_object($this->xml_parser,$this);
    xml_set_element_handler($this->xml_parser, "startElement", "endElement");
    xml_set_character_data_handler($this->xml_parser, "characterData");

    xml_parser_set_option($this->xml_parser,XML_OPTION_CASE_FOLDING, 0);

    xml_parse($this->xml_parser, $strXML);

    xml_parser_free($this->xml_parser);
  }

/** creates new xml element using the current element
		@note PRIVATE only used for internals please do not change without knowing how it works
 		@param parser reference to the parser instance used
 		@param name the element name of the current opening tag
 		@param attrs the attributes of the current element
*/
  function startElement($parser, $name, $attrs)
  {
    $tmp=new cXmlElement();
    $tmp->TagName   = $name;
    $tmp->arrAttrib = $attrs;

    if ($this->objCurrentElement!=null){
      $this->stack->push($this->objCurrentElement);
    }
    $this->objCurrentElement=$tmp;
  }

/** adds the new xml element to the current node of the dom
		@note PRIVATE only used for internals please do not change without knowing how it works
 		@param parser reference to the parser instance used
 		@param name the element name of the current closing tag
*/
  function endElement($parser, $name)
  {
    $tmp=$this->stack->pop();

    if ($tmp!=null){
      $tmp->addChild($this->objCurrentElement);
      $this->objCurrentElement=&$tmp;
    }
  }

/** decodes utf8 encoded character data
 		@param parser reference to the parser instance used
 		@param data the data to decode
		@note PRIVATE only used for internals please do not change without knowing how it works
*/
  function characterData($parser, $data) {
    $this->objCurrentElement->CharacterData.=utf8_decode($data);
  }

/**
		getDom() will return the xmldom, a single xmlelement with the childs
		You can traverse this dom by the methods of xmlelement-class
 		@return document object model
*/
  function getDOM() {
    return $this->objCurrentElement;
  }


}
?>