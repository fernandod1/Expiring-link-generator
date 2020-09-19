<?php 
$id = (int)(isset($_REQUEST['id'])?$_REQUEST['id']:"");
if (isset($id)){
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Credentials: true");
  header('Access-Control-Allow-Methods: GET');
  include('config.php');
  $filename=$GLOBALS['PATH_LOGS_COUNTERS']."".$id.".txt";
  $row=@file_get_contents($filename);
  $fields=explode('|', $row);
  $arr = array($fields[1]);
  echo $_GET['callback']."(".json_encode($arr).");";
}
?>