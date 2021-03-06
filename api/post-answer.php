<?php

	require_once(dirname(__file__) . '/../../../../wp-config.php');

    if ( is_user_logged_in() ) 
    {
        // get the answer content
        $questionId = isset($_POST["questionId"]) ? intval($_POST["questionId"]) : -1;
        $answer = isset($_POST["answer"]) ? $_POST["answer"] : "";
        
        if ($questionId != -1 && $answer != '') 
        {
            // set the answer
            $expertchat = new ExpertChat();
            $result = $expertchat->set_answer($questionId, $answer);

        	// get the details for one chat
        	$response = $result;
	    }
	    else
	    {
	        $response = array('error' => true, 'message' => 'id or message error');
	    }

    }
    else
    {
        $response = array('error' => true, 'message' => 'please login');
    }
    
	// write it as json
	header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($response);

?>