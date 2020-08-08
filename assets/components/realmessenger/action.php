<?php
if (empty($_REQUEST['action']) and empty($_REQUEST['gtsnotify_action'])) {
    $message = 'Access denied action.php';
    echo json_encode(
            ['success' => false,
            'message' => $message,]
            );
    return;
}



define('MODX_API_MODE', true);
require dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';

$RealMessenger = $modx->getService('RealMessenger', 'RealMessenger', MODX_CORE_PATH . 'components/realmessenger/model/', []);

if (!$RealMessenger) {
    $message =  'Could not create RealMessenger!';
	echo json_encode(
		['success' => false,
		'message' => $message,]
		);
	return;
}

$modx->lexicon->load('gtsnotify:default');

$response = $RealMessenger->handleRequest($_REQUEST['action'],$_REQUEST);

echo json_encode($response);