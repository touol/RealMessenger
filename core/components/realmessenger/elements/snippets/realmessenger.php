<?php
/** @var modX $modx */
/** @var array $scriptProperties */
/** @var RealMessenger $RealMessenger */
$RealMessenger = $modx->getService('RealMessenger', 'RealMessenger', MODX_CORE_PATH . 'components/realmessenger/model/', $scriptProperties);
if (!$RealMessenger) {
    return 'Could not load RealMessenger class!';
}
$RealMessenger->initialize($modx->context->key,$scriptProperties);


$OuterTpl = $modx->getOption('OuterTpl', $scriptProperties, 'tpl.RealMessenger.outer');
$ContactGroups = $modx->getOption('ContactGroups', $scriptProperties, '2');

$with_user_id = (int)$_GET['user_id'];
if((int)$with_user_id){
    //ишем или создаем чат с юзером
}

$messages = '';
$resp = $RealMessenger->get_chat_messages(['chat'=> $active_chat]);
if($resp['success']) $messages = $resp['data']['messages'];

$chats = '';
$resp = $RealMessenger->get_chats($active_chat);
if($resp['success']) $chats = $resp['data']['chats'];

$search_contact = '';
$search_contact = $RealMessenger->search_contact($ContactGroups);
if($resp['success']) $search_contact = $resp['data']['search_contact'];

$output = $RealMessenger->pdoTools->getChunk($OuterTpl,[
    'hash'=>$RealMessenger->config['hash'],
    'messages'=>$messages,
    'chats'=> $chats,
    'search_contact'=> $search_contact,
]);
return $output;
