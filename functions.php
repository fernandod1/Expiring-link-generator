<?php
/*

IMPORTANT: DO NOT MODIFY THIS FILE.

*/

function request_authentication($USERNAME,$PASSWORD){
    $valid_passwords = array ($USERNAME => $PASSWORD);
    $valid_users = array_keys($valid_passwords);    
    $user = $_SERVER['PHP_AUTH_USER'];
    $pass = $_SERVER['PHP_AUTH_PW'];    
    $validated = (in_array($user, $valid_users)) && ($pass == $valid_passwords[$user]);    
    if (!$validated) {
      header('WWW-Authenticate: Basic realm="My Realm"');
      header('HTTP/1.0 401 Unauthorized');
      die ("Not authorized");
    }
}

function parse_template($array,$template){
    if (file_exists($template)){
        $html=@file_get_contents($template);
        foreach ($array as $key => $value) {
            $html = str_replace('{'.$key.'}', $value, $html);
        }
    }else{
        $html = '<br><div class="alert alert-danger"><p style="color:black;">Error: template filename '.$template.' does not exist.</p></div>';
    }
    return $html;
}

function get_last_id_counter(){
    $idmax=0;
    $files = array_diff(scandir($GLOBALS['PATH_LOGS_COUNTERS']), array('.', '..'));
    foreach($files as $each) {
        $id = explode('.txt', $each);
        if($id[0]>$idmax){$idmax=$id[0];}
    }
    return $idmax;
}

function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function verify_form(){
    $errorMessage = "";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = sanitize($_POST["id"]);
        $url = sanitize($_POST["url"]);
        $startcounter = intval(sanitize($_POST["startcounter"]));
        $maxcounter = intval(sanitize($_POST["maxcounter"]));
        $utmtags = ($_POST["utmtags"]);
                
    }    
    if (empty($url)) {
        $errorMessage = '<br><div class="alert alert-danger"><p style="color:black;">Error: URL is required.</p></div>';
        echo $errorMessage;
    } else {
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            $errorMessage = '<br><div class="alert alert-danger"><p style="color:black;">Error: Invalid URL. It must starts with http.</p></div>';
            echo $errorMessage;
        }
    }
    if (empty($maxcounter)) {
        $errorMessage = '<br><div class="alert alert-danger"><p style="color:black;">Error: Max. counter value upon deactivation must be set.</p></div>';
        echo $errorMessage;
    }
    if ($maxcounter<=$startcounter) {
        $errorMessage = '<br><div class="alert alert-danger"><p style="color:black;">Error: Max. counter value must be higher than initial counter value.</p></div>';
        echo $errorMessage;
    }
    if(!empty($errorMessage)){
        return "ERROR";
    } else{
        $data=array(
			'op'=>'verified_form',
			'id'=>$id,
			'startcounter'=>$startcounter,
			'maxcounter'=>$maxcounter,
			'url'=>$url,
            'utmtags'=>$utmtags,
            'percent'=>round($startcounter/$maxcounter,2)*100
		  );
        return $data;
    }
}

function create_counter($data){
    if($data['id']==""){$data['id']=get_last_id_counter()+1;}
    $filename_counter=$GLOBALS['PATH_LOGS_COUNTERS']."".$data['id'].".txt";
    $row=$data['id']."|".$data['startcounter']."|".$data['maxcounter']."|".$data['url']."|".$data['utmtags']."";
    file_put_contents($filename_counter,$row);
    return $data['id'];
}

function edit_counter($data, $op){
    remove_counter($data['id'],$op);
    create_counter($data);
}

function print_stats_counters() {
    $files = array_diff(scandir($GLOBALS['PATH_LOGS_COUNTERS']), array('.', '..'));
    foreach($files as $each) {
        $id = explode('.txt', $each);
        $data=get_counter($id[0]);
        $data['percent']=round($data['startcounter']/$data['maxcounter'],2)*100;
		echo parse_template($data,"templates/counter_details.html");
    }
}

function get_counter($id){
    $row = file_get_contents($GLOBALS['PATH_LOGS_COUNTERS']."".$id.".txt");
    $field = explode('|', $row);
    $data=array(
        'op'=>'editing',
        'id'=>$field[0],
        'startcounter'=>$field[1],
        'maxcounter'=>$field[2],
        'url'=>$field[3],
        'utmtags'=>$field[4],
        'percent'=>round($field[1]/$field[2],2)*100
      );
      return $data;
}

function remove_counter($idcounter,$op){
    $filename_counter=$GLOBALS['PATH_LOGS_COUNTERS']."".$idcounter.".txt";
    // Remove counter log
    if (file_exists($filename_counter)) {
        unlink($filename_counter);        
    } else {
        echo '<br><div class="alert alert-danger"><p style="color:black;">Error: no counter ID '.$idcounter.' found to be removed.</p></div>';
    }
    if($op=="remove"){
        // History logs
        $path=$GLOBALS['PATH_LOGS_HISTORY']."".$idcounter;
        array_map('unlink', glob("$path/*.*"));
        if (!@rmdir($path)) {
            echo '<br><div class="alert alert-danger"><p style="color:black;">Error: History logs file of counter ID '.$idcounter.' could not be removed.</p></div>';
        }

    }
}

function increase_counter($idcounter){
    $message="";
    $idcounter=(int)sanitize($idcounter);
    $filename=$GLOBALS['PATH_LOGS_COUNTERS']."".$idcounter.".txt";
    $row=@file_get_contents($filename);
    $field=explode('|', $row);
    if(empty($row)){
        $message = '<br><div class="alert alert-danger"><p style="color:black;">Error: Counter ID '.$idcounter.' does not exists.</p></div>';
    }else{
        if($field[1]<$field[2]){
            $field[1]++;
            $row=$field[0]."|".$field[1]."|".$field[2]."|".$field[3]."|".$field[4]."";
            file_put_contents($filename,$row);            
        }else{
            header('Location: '.$GLOBALS['URL_REDIRECT_CAMPAIGN_FINISHED'].'');
            exit();
        }
    }
    return $message;
}

function new_user_click($idcounter){
    if(check_cookie()){
        return false;
    }else{
        set_cookie($GLOBALS['COOKIE_EXPIRATION_TIME']);
        return true;
    }
}

function process_click($id){
    $idcounter = (int)(isset($id)?$id:"");    
    if (!empty($idcounter)){
        $data=get_counter($idcounter);
        if(new_user_click($idcounter)){ 
            $msg=increase_counter($idcounter);
            if(empty($msg)){
                increase_history_log_file($idcounter);
                if(!empty($data['utmtags'])){
                    $data['url'] = $data['url']."?".$data['utmtags'];
                } 
                header('Location: '.$data['url'].'');                
            } else{echo $msg;}
        }else{
            //echo '<br><div class="alert alert-danger"><p style="color:black;">Error: User already clicked before. Not redirected to website.</p></div>';
            header('Location: '.$data['url'].'');       
        }
    }
}

function create_history_log_file($idcounter,$date){
    $filename=$GLOBALS['PATH_LOGS_HISTORY']."".$idcounter."/".$date.".txt";
    if (!file_exists($GLOBALS['PATH_LOGS_HISTORY']."".$idcounter)) {
        mkdir($GLOBALS['PATH_LOGS_HISTORY']."".$idcounter, 0755, true);
    }
    if (!file_exists($filename)) {  
        file_put_contents($filename,0);
    }
}

function increase_history_log_file($idcounter){ 
    $idcounter=(int)sanitize($idcounter);
    $filename=$GLOBALS['PATH_LOGS_HISTORY']."".$idcounter."/".date("Ymd").".txt";
    if (file_exists($filename)) {         
        $counter_value=@file_get_contents($filename);
        if($counter_value==""){
            file_put_contents($filename,0);
        }else{
            file_put_contents($filename,$counter_value+1);    
        }
    } else{
        create_history_log_file($idcounter,date("Ymd"));
        increase_history_log_file($idcounter);
    }
}

function get_counter_history($idcounter){
    $files = array_diff(scandir($GLOBALS['PATH_LOGS_HISTORY']."".$idcounter."/"), array('.', '..'));
    $x=0;
    foreach($files as $each) {
        $counter_value=@file_get_contents($GLOBALS['PATH_LOGS_HISTORY']."".$idcounter."/".$each);
        $date = explode('.txt', $each);
        $date = DateTime::createFromFormat('Ymd', $date[0]);
        $background_color = ($x%2 == 0)? '': 'active';
        $html_history .= '<tr class='.$background_color.'><td>'.$date->format('Y/m/d').'</td><td>'.$counter_value.'</td></tr>';
        $x++; 
    }
    return $html_history;
}

function check_cookie(){
    $active = false;
    if( isset($_COOKIE['counter_campaign']) )
        $active = true;
    return $active;
}

function set_cookie($time){
    setcookie('counter_campaign', 1, time() + ($time) );
}

?>

