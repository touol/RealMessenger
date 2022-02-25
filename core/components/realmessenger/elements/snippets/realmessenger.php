<?php
/** @var modX $modx */
/** @var array $scriptProperties */
/** @var RealMessenger $RealMessenger */
$ContactGroups = $modx->getOption('ContactGroups', $scriptProperties, '2');
$scriptProperties['ContactGroups'] = $ContactGroups;
$ContactGroupsPageIds = $modx->getOption('ContactGroupsPageIds', $scriptProperties, '');
$scriptProperties['ContactGroupsPageIds'] = $ContactGroupsPageIds;

$RealMessenger = $modx->getService('RealMessenger', 'RealMessenger', MODX_CORE_PATH . 'components/realmessenger/model/', $scriptProperties);
if (!$RealMessenger) {
    return 'Could not load RealMessenger class!';
}

$RealMessenger->initialize($modx->context->key,$scriptProperties);


$OuterTpl = $modx->getOption('OuterTpl', $scriptProperties, 'tpl.RealMessenger.outer');
$ChatsTpl = $modx->getOption('ChatsTpl', $scriptProperties, 'tpl.RealMessenger.chats');
$MessagesTpl = $modx->getOption('MessagesTpl', $scriptProperties, 'tpl.RealMessenger.messages');
//$LastMessagesTpl = $modx->getOption('LastMessagesTpl', $scriptProperties, 'tpl.RealMessenger.last_messages');
$FormTpl = $modx->getOption('FormTpl', $scriptProperties, 'tpl.RealMessenger.form');


if(isset($_GET['user_id'])) $with_user_id = (int)$_GET['user_id'];
if(isset($_GET['doctor_id'])) $with_user_id = (int)$_GET['doctor_id'];

if($with_user_id){
    //ишем или создаем чат с юзером
    $resp = $RealMessenger->find_or_new_chat(['new_chat_user_id'=>$with_user_id]);
    if($resp['success']) $active_chat = $resp['data']['active_chat'];
}

$messages = '';
$resp = $RealMessenger->get_chat_messages(['chat'=> $active_chat]);
$messages = $RealMessenger->pdoTools->getChunk($MessagesTpl, [
    'messages'=>$resp['data']['messages'],
    'user'=>$resp['data']['user'],
    'users'=>$resp['data']['users']
]);

$form = $RealMessenger->pdoTools->getChunk($FormTpl, ['chat'=> $active_chat]);

$chats = '';
$resp = $RealMessenger->get_chats($active_chat);
$chats = $RealMessenger->pdoTools->getChunk($ChatsTpl, ['chats'=>$resp['data']['chats']]);

$search_contact = '';
$resp = $RealMessenger->search_contact();
$search_contact = $resp['data']['search_contact'];

$search_chat = $RealMessenger->search_chat();

$output = $RealMessenger->pdoTools->getChunk($OuterTpl,[
    'hash'=>$RealMessenger->config['hash'],
    'messages'=>$messages,
    'form'=>$form,
    'chats'=> $chats,
    'search_contact'=> $search_contact,
    'search_chat'=> $search_chat,
]);
return $output;
