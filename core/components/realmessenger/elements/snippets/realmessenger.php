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
$FormTpl = $modx->getOption('FormTpl', $scriptProperties, 'tpl.RealMessenger.form');



$with_user_id = (int)$_GET['user_id'];
if((int)$with_user_id){
    //ишем или создаем чат с юзером
}

$messages = '';
$resp = $RealMessenger->get_chat_messages(['chat'=> $active_chat]);
$messages = $RealMessenger->pdoTools->getChunk($MessagesTpl, ['messages'=>$resp['data']['messages']]);

$form = $RealMessenger->pdoTools->getChunk($FormTpl, []);

$chats = '';
$resp = $RealMessenger->get_chats($active_chat);
$chats = $RealMessenger->pdoTools->getChunk($ChatsTpl, ['chats'=>$resp['data']['chats']]);

$search_contact = '';
$resp = $RealMessenger->search_contact($ContactGroups);
$search_contact = $resp['data']['search_contact'];

$output = $RealMessenger->pdoTools->getChunk($OuterTpl,[
    'hash'=>$RealMessenger->config['hash'],
    'messages'=>$messages,
    'form'=>$form,
    'chats'=> $chats,
    'search_contact'=> $search_contact,
]);
return $output;
