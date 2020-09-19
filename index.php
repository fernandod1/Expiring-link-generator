<?php
// You'd put this code at the top of any "protected" page you create

// Always start this first
session_start();

if ( isset( $_SESSION['user_id'] ) ) {
/*

IMPORTANT: DO NOT MODIFY THIS FILE.

*/

include('config.php');
include('functions.php');


//request_authentication($USERNAME,$PASSWORD);

include('templates/header.html'); 

$op = sanitize($_REQUEST['op']);
switch($op) {
	default: 	
		print_stats_counters();
	break; break;	
	case "add": 
		$data=array(
			'op'=>'adding',
			'id'=>'',
			'startcounter'=>'',
			'maxcounter'=>'',
			'url'=>'',
			'utmtags'=>'',
			'percent'=>''
		  );
		echo parse_template($data,"templates/form_add.html");
	break;
	case "adding":
		$data=verify_form();
		if($data!="ERROR"){
			$data['id']=create_counter($data);
			create_history_log_file($data['id'],date("Ymd"));
			echo '<br><div class="alert alert-success"><p style="color:black;">Counter ID '.$data['id'].' has been created.</p></div>';
			echo parse_template($data,"templates/counter_details.html");
		};		
	break;	
	case "edit": 
		$data=get_counter($_REQUEST['id']);
		echo parse_template($data,"templates/form_add.html");
	break;
	case "editing": 
		$data=verify_form();
		if($data!="ERROR"){		
			edit_counter($data,$op);
			echo '<br><div class="alert alert-success"><p style="color:black;">Counter ID '.$data['id'].' has been edited.</p></div>';
			echo parse_template($data,"templates/counter_details.html");
		}
	break;
	case "remove": 
		remove_counter($_REQUEST['id'],$op);
		echo '<br><div class="alert alert-success"><p style="color:black;">Counter ID '.$data['id'].' has been deleted.</p></div>';
	break;
	case "counter_code": 
		$data=array(
			'id'=>$_REQUEST['id'],
			'url'=>$URL_SCRIPT_FOLDER
		  );
		echo parse_template($data,"templates/counter_code.html");
	break;
	case "counter_link": 
		$data=array(
			'id'=>$_REQUEST['id'],
			'urlscriptfolder'=>$URL_SCRIPT_FOLDER
		  );
		echo parse_template($data,"templates/counter_link.html");
	break;
	case "counter_history":
		$data=get_counter($_REQUEST['id']);
		$data['html_history']=get_counter_history($_REQUEST['id']);
		echo parse_template($data,"templates/counter_history.html");
	break;
}

include('templates/footer.html'); 

} else {
    // Redirect them to the login page
    header("Location: login.php");
}
?>

