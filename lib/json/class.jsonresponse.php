<?php
/**
 * @class Response
 * A simple JSON Response class.
 */
class JSONResponse {
	
    var $success;
	var $data;
	var $total;
	var $message;
	
	var $errors;
	var $tid;
	var $trace;

    function JSONResponse($params = array()) {
        $this->success  = isset($params["success"]) ? $params["success"] : false;
        $this->message  = isset($params["message"]) ? $params["message"] : '';
        $this->data     = isset($params["data"])    ? $params["data"]    : array();
        $this->total    = isset($params["total"])   ? $params["total"]   : count($this->data);
    }

    function to_json() {
        return json_encode(array(
            'success'   => $this->success,
            'message'   => $this->message,
            'data'      => $this->data,
            'total'     => $this->total
        ));
    }
}

?>