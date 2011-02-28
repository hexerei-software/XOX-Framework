<?php
/**
	XMLElement is a class representing a xml-Node
	There are methods for accessing childs, attributes.
	The Class cXmlDom builds a complete DOM by any
	xml-String. A XmlElement can also render itself back
	to xml. It will be utf-8 encoded.
	@author	Lutz Dornbusch
	@date	2004-08-01
*/
class cXmlElement {

  /** the Tagname */
	var $TagName="";
  /** the childs of this Node */
	var $arrChilds=array();
	/** the attributes of this node */
  var $arrAttrib=array();
	/** the Characterdata of this node */
  var $CharacterData="";

/**
	adds an child to this node. You have to give the child
	as reference
	@param objChild the child which will be added
*/
  function addChild(&$objChild){
    $this->arrChilds[] = &$objChild;
  }

/** get number of children of this node
	@return the number of childs this node has
*/
  function getNumberOfChilds(){
    return count($this->arrChilds);
  }

/** get child by index
	@return child identified by given index
	@note you receive a Reference, so you will manipulate
	not a copy, but directly on the xml dom
*/
  function &getChild($index){
    return $this->arrChilds[$index];
  }

/** get value of a given attribute
		@param Attrib the name of the attribute
		@return the value of this attribute
*/
  function getAttribute($Attrib){
    if (array_key_exists($Attrib, $this->arrAttrib)){
      return utf8_decode($this->arrAttrib[$Attrib]);
    } else {
     return null;
    }
  }

/** get first child matching given name
 		@param childname
		@return the first child with matching tagname
*/
  function findChild($childname){
    $retVal=null;
    for ($i=0;$i<$this->getNumberOfChilds();$i++){
      $tmp=$this->getChild($i);
      if (strtolower($tmp->TagName)==strtolower($childname)){
        $retVal=$tmp;
        break;
      }
    }
    return $retVal;
  }

/**
 		renders the node as xml string
		@return a string which is a xml-representation[UTF-8-encoded]
*/
  function render2xml(){
  	$retVal='<'.utf8_encode($this->TagName);
		foreach ($this->arrAttrib as $key => $value) {
	  	$retVal.= ' '.utf8_encode($key).'="'.utf8_encode($value).'"';
		}
		$retVal.= '>';
  	for ($i=0;$i<$this->getNumberOfChilds();$i++){
  		$tmp=$this->getChild($i);
  		$retVal.=$tmp->render2xml();
  	}
  	$retVal.='</'.utf8_encode($this->TagName).'>';
    return $retVal;
  }
}
?>