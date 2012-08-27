<?php

	require_once(dirname(__file__) . '/../../../../wp-config.php');

    if ( is_user_logged_in() ) 
    {
        $chatid = isset($_POST["chatid"]) ? intval($_POST["chatid"]) : -1;
        
        if ($chatid != -1)
        {
            // close the chat
            $expertchat = new ExpertChat();
            $result = $expertchat->close_chat($chatid);

        	// get the response
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