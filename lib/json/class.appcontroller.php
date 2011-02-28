<?php
/**
 * @class AppController
 */
require_once('class.jsonresponse.php');

class AppController {
	
    var $request;
	var $id;
	var $data;
	var $params;
	
	var $dbo = null;
	
	function AppController($dbo) {
		$this->dbo = $dbo;
	}

    /**
     * dispatch
     * Dispatch request to appropriate controller-action by convention according to the HTTP method.
     */
    function dispatch($request)
	{
        $this->request = $request;
        $this->id = $request->id;
		$this->data = $request->data;
        $this->params = $request->params;

		//print_r($request);

        if ($request->isRestful()) {
            return $this->dispatchRestful();
        }
        if ($request->action) {
            return $this->{$request->action}();
        }
    }

    function dispatchRestful()
	{
        switch ($this->request->method) {
            case 'GET':
                return $this->view();
                break;
            case 'POST':
                return $this->create();
                break;
            case 'PUT':
                return $this->update();
                break;
            case 'DELETE':
                return $this->destroy();
                break;
        }
    }

    /**
     * view
     * Retrieves rows from database.
     */
    function view()
	{
        $res 	= new JSONResponse();
		$start 	= (isset($this->params['start'])) ? $this->params['start'] : 0;
		$limit 	= (isset($this->params['limit'])) ? $this->params['limit'] : 25;
		$sort 	= (isset($this->params['sort'])) ? $this->params['sort'] : '';
		$sort  .= (isset($this->params['dir'])) ? ' '.$this->params['dir'] : '';
		$where 	= '';
		if (isset($this->params['query'])) {
			$parts = explode('|',$this->params['query']);
			if (count($parts)>1) {
				$fields = explode(':',$parts[0]);
				$wheres = array();
				foreach($fields as $field) {
					$wheres[] = "$field like '%$parts[1]%'";
				}
				$where = "(".implode(' OR ',$wheres).")";
			}
		}
		$total 	= 0;
        $res->data = $this->dbo->getall($total,$limit,$start,$sort,$where);
		$res->total = $total;
        $res->success = true;
        $res->message = "Daten geladen";
        return $res->to_json();
    }

    /**
     * create
     */
    function create()
	{
        $res = new JSONResponse();
		//if (isset($this->data['id'])) unset($this->data['id']);
        $rec = $this->dbo->create($this->data);
        if ($rec) {
            $res->success = true;
            $res->message = "Neuen Eintrag ".$rec->getID()." angelegt";
            $res->data = $rec->gethash();
        } else {
			$res->data = $this->data;
            $res->message = "Konnte den Eintrag nicht anlegen";
        }
        return $res->to_json();
    }

    /**
     * update
     */
    function update()
	{
        $res = new JSONResponse();
        $rec = $this->dbo->update($this->id, $this->data);
        if ($rec) {
            $res->data = $rec->gethash();
            $res->success = true;
            $res->message = "Eintrag $this->id aktualisiert";
        } else {
			$res->data = $this->data;
            $res->message = "Konnte den Eintrag $this->id nicht aktualisieren";
        }
        return $res->to_json();
    }

    /**
     * destroy
     */
    function destroy()
	{
        $res = new JSONResponse();
        if ($this->dbo->destroy($this->id)) {
            $res->success = true;
            $res->message = 'Eintrag gelöscht: ' . $this->id;
        } else {
            $res->message = 'Konnte den Eintrag nicht löschen';
        }
        return $res->to_json();
    }

}

?>