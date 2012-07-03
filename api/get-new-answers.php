<?php

 	require_once(__DIR__ . '/../../../../wp-config.php');
	
	//$chatid = $_GET["chatid"];
	//$latest = $_GET["latest"];
	$chatid = $_POST["chatid"];
	$latest = $_POST["latest"];    
    
    $expertchat = new ExpertChat();
    $response = $expertchat->get_new_answered_questions($chatid, $latest);

    // write it as json
	header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($response);
    
?>