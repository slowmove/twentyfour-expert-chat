<?php

	require_once(__DIR__ . '/../../../../wp-config.php');

    if ( is_user_logged_in() ) 
    {
        // get the answer content
        $questionId = isset($_POST["questionId"]) ? intval($_POST["questionId"]) : -1;
        
        if ($questionId != -1) 
        {
            // send the email
            $expertchat = new ExpertChat();
            $result = $expertchat->deny_question($questionId);

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