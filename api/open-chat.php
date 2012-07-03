<?php

	require_once(__DIR__ . '/../../../../wp-config.php');

    if ( is_user_logged_in() ) 
    {
        // close the chat
        $expertchat = new ExpertChat();
        $chatid = $expertchat->get_future_chats();
        if( count($chatid) ):
            $nextChatId = $chatid[0]->id;
            $result = $expertchat->open_chat($nextChatId);
        else:
            $result = array("error" => true, "message" => "Det finns inga framtida chattar");
        endif;

        // get the response
        $response = $result;
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