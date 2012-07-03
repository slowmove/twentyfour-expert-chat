<?php

	require_once(__DIR__ . '/../../../../wp-config.php');

    // get question content
    $chat_id = isset($_POST["chat_id"]) ? intval($_POST["chat_id"]) : -1;
    $qname = isset($_POST["qname"]) ? $_POST["qname"] : "Anonym";
    $question = isset($_POST["question"]) ? $_POST["question"] : "";
    
    if ($chat_id != -1 && $question != '') 
    {		
        // post the question
        $expertchat = new ExpertChat();
        $result = $expertchat->post_question($chat_id, $qname, $question);

    	// get the id
    	$response = $result;
    }
    else
    {
        $response = array('error' => true, 'message' => 'id or message error');
    }
    
	// write it as json
	header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($response);

?>