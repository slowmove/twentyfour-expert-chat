<?php

require_once(dirname(__file__) . '/../../../../wp-config.php');

$users = ExpertChat::get_blog_user($_POST['siteid']);

// write it as json
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

echo json_encode($users);

?>