<?php 

  function ms_open($spath, $sname) {
    $time = time();
    $sessid = session_id();
    $result = executeQuery("SELECT * FROM currentsession WHERE sessionID='$sessid'");
    if ($result->numrows()==0) {
      executeSQL("INSERT INTO currentsession (sessionID, laccess) VALUES ('$sessid', '$time')");
    } else {
      executeSQL("UPDATE currentsession SET laccess = '$time' WHERE sessionID = '$sessid'");
    }
    return TRUE;
  }

  function ms_read($sessid) {
    $result = executeQuery("SELECT * FROM currentsession WHERE sessionID='$sessid'");
    if ($row=$result->firstrow()) {
      session_decode($row["variables"]);
      return TRUE;
    } else {
      return FALSE;
    }
  }

  function ms_write($sessid, $varis) {
    return executeSQL("UPDATE currentsession SET variables = '$varis' WHERE sessionID = '$sessid'");
  }

  function ms_destroy($sessid) {
    return executeSQL("DELETE FROM currentsession WHERE sessionID = '$sessid'");
  }

  function ms_gc($sesslt) {
    $tStamp = time() - $sesslt;
    return executeSQL("DELETE FROM currentsession WHERE laccess < '$tStamp'");
  }

  function ms_close() {
  }

  //session_module_name("user");
  //session_set_save_handler('ms_open', 'ms_close', 'ms_read', 'ms_write', 'ms_destroy', 'ms_gc');
  session_name('XOXSESSION');

  session_start();

?>
