<?
/**
	This Class implements a simple stack you can push, peek and pop
	this stack like any other stack. This class is used for the small
	XML-Parser. I had coded it, before I saw that php-array can
	be Stacks, too. So this is not neccesary, but It is more readable
	@author Lutz Dornbusch
	@date 2004-08-01
*/

class cStack {

  /** the height(or depth, as you like) of the stack */
	var $depth;
	/** Data inside stack[the stack itself] */
  var $arrDaten; 

  /** constructs new stack with default values */
	function cStack() {
     $this->arrDaten= array();
     $this->depth=0;
  }

/**
		push function pushes a new object to the stack and increases
		its internal pointer
 		@param object the object to append to the stack
*/
  function push($object)
  {
    $this->arrDaten[$this->depth]=$object;
    $this->depth++;
  }

/**
		pop function removes the most upper object from stack, decreases
		the counter and returns this object
 		@return the next object from the stack or null if stack is empty
*/
  function pop()
  {
    $this->depth--;
    if ($this->depth>-1){
      return $this->arrDaten[$this->depth];
    }else {
      return null;
    }
  }
/**
		returns the most upper element of the stack without changing
		the state of the stack
 		@return the last object on the stack
*/
  function peek() {
    return $this->arrDaten[$this->depth-1];
  }

/** get size of stack
		@return the nuber of elements on stack
*/
  function getSize(){
    return $this->depth;
  }
}
?>