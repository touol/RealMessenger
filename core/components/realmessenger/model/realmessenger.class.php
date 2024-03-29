<?php

class RealMessenger
{
    /** @var modX $modx */
    public $modx;

    /** @var pdoFetch $pdoTools */
    public $pdoTools;

    /** @var array() $config */
    public $config = array();

    /** @var array $initialized */
    public $initialized = array();

    /** @var modError|null $error = */
    public $error = null;
    
    public $gtsNotify;

    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/realmessenger/';
        $assetsUrl = MODX_ASSETS_URL . 'components/realmessenger/';

        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',
            'customPath' => $corePath . 'custom/',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'actionUrl' => $assetsUrl . 'action.php',

            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
        ], $config);

        $this->modx->addPackage('realmessenger', $this->config['modelPath']);
        $this->modx->lexicon->load('realmessenger:default');

        $this->config['hash'] = sha1(json_encode($this->config));

        if ($this->pdoTools = $this->modx->getService('pdoFetch')) {
            $this->pdoTools->setConfig($this->config);
        }
        
        $this->gtsNotify = $this->modx->getService('gtsNotify', 'gtsNotify', MODX_CORE_PATH . 'components/gtsnotify/model/', []);

    }

    /**
     * Initializes component into different contexts.
     *
     * @param string $ctx The context to load. Defaults to web.
     * @param array $scriptProperties Properties for initialization.
     *
     * @return bool
     */
    public function initialize($ctx = 'web', $scriptProperties = array())
    {
        $this->config = array_merge($this->config, $scriptProperties);

        $this->config['pageId'] = $this->modx->resource->id;

        switch ($ctx) {
            case 'mgr':
                break;
            default:
                if (!defined('MODX_API_MODE') || !MODX_API_MODE) {

                    $config = $this->makePlaceholders($this->config);
                    if ($css = $this->modx->getOption('realmessenger_frontend_css')) {
                        $this->modx->regClientCSS(str_replace($config['pl'], $config['vl'], $css));
                    }

                    $config_js = preg_replace(array('/^\n/', '/\t{5}/'), '', '
                            RealMessenger = {};
                            RealMessengerConfig = ' . $this->modx->toJSON($this->config) . ';
                    ');


                    $this->modx->regClientStartupScript("<script type=\"text/javascript\">\n" . $config_js . "\n</script>", true);
                    if ($js = trim($this->modx->getOption('realmessenger_frontend_js'))) {

                        if (!empty($js) && preg_match('/\.js/i', $js)) {
                            $this->modx->regClientScript(preg_replace(array('/^\n/', '/\t{7}/'), '', '
                            <script type="text/javascript">
                                if(typeof jQuery == "undefined") {
                                    document.write("<script src=\"' . $this->config['jsUrl'] . 'web/lib/jquery.min.js\" type=\"text/javascript\"><\/script>");
                                }
                            </script>
                            '), true);
                            $this->modx->regClientScript(str_replace($config['pl'], $config['vl'], $js));

                        }
                    }
                    $this->modx->regClientScript($this->config['jsUrl'] . 'web/vendor/emoji-picker/fgEmojiPicker.js');
                    //$this->modx->regClientScript($this->config['jsUrl'] . 'web/vendor/jquery-emoji-picker/js/jquery.emojis.js');
                    //$this->modx->regClientCSS($this->config['jsUrl'] . 'web/vendor/Emojiarea/assets/css/style.css');
                    //$this->modx->regClientCSS($this->config['jsUrl'] . 'web/vendor/jquery-emoji-picker/css/jquery.emojipicker.tw.css');

                    if(empty($this->config['ContactGroupsPageIds'])) $this->config['ContactGroupsPageIds'] = $this->modx->resource->id;
                    $ContactGroups = explode(',',$this->config['ContactGroups']);
                    $ContactGroups0 = [];
                    foreach($ContactGroups as $cg){
                        $cg = trim($cg);
                        if((int)$cg > 0){
                            $ContactGroups0[] = $cg;
                        }else{
                            if($g = $this->modx->getObject('modUserGroup',['name'=>$cg])){
                                $ContactGroups0[] = $g->id;
                            }
                        }
                    }
                    $this->config['ContactGroups'] = implode(',',$ContactGroups0);
                    $_SESSION['RealMessenger'][$this->config['hash']] = $this->config;
                    /*if(isset($_SESSION['getTables'][$this->config['hash']][$gts_class][$gts_name]))
                    return $_SESSION['getTables'][$this->config['hash']][$gts_class][$gts_name];*/

                }

                break;
        }
        return true;
    }

    public function handleRequest($action, $data = array())
    {
        
        switch($action){
            case 'get_chat_messages': 
                return $this->get_chat_messages($data);
                break;
            case 'close_chat': 
                return $this->close_chat($data);
                break;
            case 'save_message': 
                return $this->save_message($data);
                break;
            case 'send_read_messages': 
                return $this->send_read_messages($data);
                break;
            case 'find_or_new_chat': 
                return $this->find_or_new_chat($data);
                break;
            case 'add_chat': 
                return $this->add_chat($data);
                break;
            case 'autocomplect_search_contact':
                switch($data['search_goal']){
                    case 'chat':
                        return $this->autocomplect_search_chat($data);
                        break;
                    default:
                        return $this->autocomplect_search_contact($data);
                }
                break;
            default:
                return $this->error("Метод $action в классе RealMessenger не найден!");
        }
    }

    public function close_chat($data)
    {
        //$chat = $data['chat'];
        $user_id = $this->modx->user->id;
        $hash = $data['hash'];
        if(empty($hash)) $hash = $this->config['hash'];
        if($chat = $this->modx->getObject('RealMessengerChat',['id'=>(int)$data['chat'],'closed'=>0])
            and $ChatUser = $this->modx->getObject('RealMessengerChatUser',['chat'=>(int)$data['chat'],'user_id'=>$user_id])
            ){
                $ChatUser->timestamp = date('Y-m-d H:i:s');
                $ChatUser->closed = true;
                $ChatUser->save();
            
            return $this->success('');    
        }
        return $this->error("error!");
    }

    public function send_read_messages($data)
    {
        return $this->get_chat_messages($data);
    }

    public function save_message($data)
    {
        //$chat = $data['chat'];
        $user_id = $this->modx->user->id;
        $hash = $data['hash'];
        //$this->modx->log(1,"save_message " . print_r($data,1));
        $data0 = [];
        foreach($data['data'] as $v){
            $data0[$v['name']] = $v['value'];
        }
        $data = $data0;
        //$this->modx->log(1,"save_message " . print_r($data,1));
        if(empty($hash)) $hash = $this->config['hash'];
        if($chat = $this->modx->getObject('RealMessengerChat',['id'=>(int)$data['chat'],'closed'=>0])
            and $ChatUser = $this->modx->getObject('RealMessengerChatUser',['chat'=>(int)$data['chat'],'user_id'=>$user_id])
            ){
            
            //$ip = $this->modx->request->getClientIp();
            $message = [
                'chat'=>$chat->id,
                'raw'=> $data['text'],
                'text'=> $this->Jevix($data['text'], 'RealMessenger'),
                'ip' => '',//$ip['ip'],
                'createdon' => date('Y-m-d H:i:s'),
                'createdby' => $user_id,
                'file_ids'=> $this->Jevix($data['file_ids'], 'RealMessenger'),
            ];
            if ($this->gtsNotify) {
                if($notify = $this->gtsNotify->create_notify($message)){
                    $message['notify_id'] = $notify->id;
                    $messenger_users = $this->prepCommentNotifyes($chat,$notify,$hash);
                }
            }
            if($mes = $this->modx->newObject('RealMessengerMessage', $message)){
                $mes->save();
                
                //return $this->success('',['message'=>$mes->toArray()]);
                if(isset($_SESSION['RealMessenger'][$hash]['MessageTpl'])){
                    $MessageTpl = $_SESSION['RealMessenger'][$hash]['MessageTpl'];
                }else{
                    $MessageTpl = 'tpl.RealMessenger.message';
                }
                if(isset($_SESSION['RealMessenger'][$hash]['LastMessageTpl'])){
                    $LastMessageTpl = $_SESSION['RealMessenger'][$hash]['LastMessageTpl'];
                }else{
                    $LastMessageTpl = 'tpl.RealMessenger.last_message';
                }
                
                $default = array(
                    'class' => 'RealMessengerMessage',
                    'where' => [
                        'RealMessengerMessage.chat'=>$chat->id,
                        'RealMessengerMessage.deleted'=> 0,
                        'RealMessengerMessage.id'=> $mes->id,
                    ],
                    'leftJoin' => [
                        'modUser'=>[
                            'class'=>'modUser',
                            'on'=>'modUser.id = RealMessengerMessage.createdby',
                        ],
                        'modUserProfile'=>[
                            'class'=>'modUserProfile',
                            'on'=>'modUserProfile.internalKey = RealMessengerMessage.createdby',
                        ],
                    ],
                    'select' => [
                        'RealMessengerMessage'=>'*',
                        'modUser'=>$this->modx->getSelectColumns('modUser','modUser','',array('username')),
                        'modUserProfile'=>$this->modx->getSelectColumns('modUserProfile','modUserProfile','',array(
                            'id','internalKey','blocked','blockeduntil','blockedafter','logincount','thislogin','failedlogincount'
                            ,'sessionid'
                            ),true),
                    ],
                    'sortby'=>['RealMessengerMessage.id'=>'DESC'],
                    'limit'=>50,
                    'return' => 'data',
                );
                
                $this->pdoTools->setConfig($default, false);
                $rows = $this->pdoTools->run();
                $output = [];
                foreach($rows as $row){
                    $row['ownmessage'] = 1;
                    $output[] = $this->pdoTools->getChunk($MessageTpl,$row);
                }
                $message0 = [
                    'ownmessage'=>$row['ownmessage'],
                    'last_message'=>$this->pdoTools->getChunk($LastMessageTpl,$row)
                ];
                //получить кол-во последних сообщений в чате и дописать в notify channel при send
                if($notify){
                    $user_data = [];
                    $last_user_id = 'a';
                    foreach($messenger_users as $m_user){
                        //считаем новые сообщения
                        if($last_user_id != $m_user['user_id']){
                            $query = $this->modx->newQuery('RealMessengerMessage');
                            $query->where(array(
                                array(
                                    'chat:=' => $chat->id,
                                    array(
                                        'AND:createdon:>' => $m_user['timestamp'],
                                        'OR:editedon:>' => $m_user['timestamp'],
                                    ),
                                ),
                            ));
                            $user_data[$m_user['user_id']][$chat->id]['chat_count'] = $this->modx->getCount('RealMessengerMessage',$query);
                            $last_user_id = $m_user['user_id'];
                        }
                    }
                    $message0['messages'] = implode("\r\n",$output);

                    $notify->json = json_encode($message0);
                    $notify->save();
                    $notify->send($user_data);
                }
                $ChatUser->timestamp = date('Y-m-d H:i:s');
                $ChatUser->save();

                if($ChatUsers = $this->modx->getIterator("RealMessengerChatUser",['chat'=>$chat->id])){
                    foreach($ChatUsers as $ChatUser){
                        $ChatUser->closed = false;
                        $ChatUser->save();
                    }
                }
                return $this->success('',array('messages'=>implode("\r\n",$output)));
            }    
        }
        return $this->error("error!");
    }

    public function prepCommentNotifyes($chat, $notify,$hash) {
        $owner_uid = $this->modx->user->id;
        if(isset($_SESSION['RealMessenger'][$hash]['ContactGroupsPageIds'])){
            $ContactGroupsPageIds = $_SESSION['RealMessenger'][$hash]['ContactGroupsPageIds'];
        }else{
            $ContactGroupsPageIds = '';
        }
        if(isset($_SESSION['RealMessenger'][$hash]['ContactGroups'])){
            $ContactGroups = $_SESSION['RealMessenger'][$hash]['ContactGroups'];
        }else{
            $ContactGroups = '';
        }

        $ContactGroups = explode(",",$ContactGroups);
        $ContactGroupsPageIds = explode(",",$ContactGroupsPageIds);
        $ContactGroupsPageIds0 = [];
        foreach($ContactGroups as $cgk => $cg){
            if(isset($ContactGroupsPageIds[$cgk])){
                $ContactGroupsPageIds0[$cgk] = $ContactGroupsPageIds[$cgk];
            }else{
                $ContactGroupsPageIds0[$cgk] = $ContactGroupsPageIds[0];
            }
        }

        $default = array(
            'class' => 'RealMessengerChatUser',
            'where' => [
                //'RealMessengerChat.single'=>1,
                'RealMessengerChatUser.chat'=>$chat->id,
                'RealMessengerChatUser.user_id:!='=>$owner_uid,
                'modUserGroupMember.user_group:IN'=>$ContactGroups,
            ],
            'leftJoin' => [
                'modUserGroupMember'=>[
                    'class'=>'modUserGroupMember',
                    'on'=>'modUserGroupMember.member = RealMessengerChatUser.user_id',
                ],
            ],
            'select' => [
                'RealMessengerChatUser'=>'RealMessengerChatUser.user_id,RealMessengerChatUser.timestamp',
                'modUserGroupMember'=>'modUserGroupMember.user_group',
            ],
            'groupby'=>'RealMessengerChatUser.user_id',
            'sortby'=>[
                'RealMessengerChatUser.user_id'=>'ASC',
            ],
            'limit'=>100,
            'return' => 'data',
        );
        
        $this->pdoTools->setConfig($default, false);
        $messenger_users = $this->pdoTools->run();

        //$messenger_users = $chat->getMany('ChatUsers');
        
        
        if (!$notify) {
            return;// 'Could not load gtsNotify class!';
        }
        foreach($messenger_users as $chatuser){
            foreach($ContactGroups as $cgk => $cg){    
                if($chatuser['user_group'] == $cg)
                    $url = $this->modx->makeUrl($ContactGroupsPageIds0[$cgk], '', array('user_id' => $owner_uid));
            }
            //$this->modx->log(1,"notify->addPurpose {$chatuser['user_id']} $url ".print_r($messenger_users,1));
            $notify->addPurpose($chatuser['user_id'],'RealMessenger',$url);
        }
        return $messenger_users;
    }
    
    public function find_or_new_chat($data)
    {
        //$chat = $data['chat'];
        $user_id = $this->modx->user->id;
        $hash = $data['hash'];
        $new_chat_user_id = $data['new_chat_user_id'];
        if(empty($hash)) $hash = $this->config['hash'];
        
        if(isset($_SESSION['RealMessenger'][$hash]['ContactGroups'])){
            $ContactGroups = $_SESSION['RealMessenger'][$hash]['ContactGroups'];
        }else{
            $ContactGroups = '2';
        }

        $default = array(
            'class' => 'RealMessengerChatUser',
            'where' => [
                'RealMessengerChat.single'=>1,
                'RealMessengerChatUser.user_id'=>$user_id,
                'RealMessengerChatUser2.user_id'=>$new_chat_user_id,
            ],
            'leftJoin' => [
                'RealMessengerChatUser2'=>[
                    'class'=>'RealMessengerChatUser',
                    'on'=>'RealMessengerChatUser.chat = RealMessengerChatUser2.chat',
                ],
                'RealMessengerChat'=>[
                    'class'=>'RealMessengerChat',
                    'on'=>'RealMessengerChatUser.chat = RealMessengerChat.id',
                ],
            ],
            'select' => [
                'RealMessengerChat'=>'*',
            ],
            'limit'=>1,
            'return' => 'data',
        );
        
        $this->pdoTools->setConfig($default, false);
        $rows = $this->pdoTools->run();
        if(count($rows) == 0){ // чата не найдено. создаем новый
            //добавить проверку на группу надо еще
            if($ContactGroups and $ContactGroups != '2'){
                if(!$cg = $this->modx->getObject('modUserGroupMember',[
                    'member'=>$new_chat_user_id,
                    'user_group:IN'=>explode(',',$ContactGroups),
                    ])){
                        return $this->error("user is not ContactGroups member!");
                }
            }
            if($chat = $this->modx->newObject('RealMessengerChat',['createdon'=>date('Y-m-d H:i:s'),'createdby'=>$user_id])){
                if($chat->save()){
                    if($ChatUserOun = $this->modx->newObject('RealMessengerChatUser',['user_id'=>$user_id,'chat'=>$chat->id,'timestamp'=>date('Y-m-d H:i:s')])){
                        if($ChatUserOun->save()){
                            if($ChatUser = $this->modx->newObject('RealMessengerChatUser',['user_id'=>$new_chat_user_id,'chat'=>$chat->id,'timestamp'=>date('Y-m-d H:i:s')])){
                                $ChatUser->save();
                                $rows = $this->pdoTools->run();
                            }
                        }
                    }
                }
            }
        }

        if(count($rows) > 0){  
            if(isset($_SESSION['RealMessenger'][$hash]['MessagesTpl'])){
                $MessagesTpl = $_SESSION['RealMessenger'][$hash]['MessagesTpl'];
            }else{
                $MessagesTpl = 'tpl.RealMessenger.messages';
            }
            if(isset($_SESSION['RealMessenger'][$hash]['ChatTpl'])){
                $ChatTpl = $_SESSION['RealMessenger'][$hash]['ChatTpl'];
            }else{
                $ChatTpl = 'tpl.RealMessenger.chat';
            }
            $output = [];
            $active_chat = 0;
            foreach($rows as $row){
                //собеседники
                $default_users = array(
                    'class' => 'RealMessengerChat',
                    'where' => [
                        'RealMessengerChatUser.user_id:!='=>$user_id,
                        'RealMessengerChat.id'=> $row['id'],
                    ],
                    'leftJoin' => [
                        'RealMessengerChatUser'=>[
                            'class'=>'RealMessengerChatUser',
                            'on'=>'RealMessengerChatUser.chat = RealMessengerChat.id',
                        ],
                        'modUser'=>[
                            'class'=>'modUser',
                            'on'=>'modUser.id = RealMessengerChatUser.user_id',
                        ],
                        'modUserProfile'=>[
                            'class'=>'modUserProfile',
                            'on'=>'modUserProfile.internalKey = RealMessengerChatUser.user_id',
                        ],
                    ],
                    'select' => [
                        //'RealMessengerChat'=>'*',
                        'modUser'=>$this->modx->getSelectColumns('modUser','modUser','',array('id','username')),
                        'modUserProfile'=>$this->modx->getSelectColumns('modUserProfile','modUserProfile','',array(
                            'id','internalKey','blocked','blockeduntil','blockedafter','logincount','thislogin','failedlogincount'
                            ,'sessionid'
                            ),true),
                    ],
                    'sortby'=>['RealMessengerChatUser.id'=>'ASC'],
                    'limit'=>10,
                    //'groupby'=>'RealMessengerChat.id',
                    'return' => 'data',
                );
                $this->pdoTools->setConfig($default_users, false);
                $row['users'] = $this->pdoTools->run();
                $row['user'] = $row['users'][0];
                unset($row['users'][0]);

                $row['class'] = 'active';
                $output[] = $this->pdoTools->getChunk($ChatTpl,$row);
                $active_chat = $row['id'];
            }
            
            $messages = '';
            $resp = $this->get_chat_messages(['chat'=> $active_chat]);
            if($ChatUser = $this->modx->getObject("RealMessengerChatUser",['chat'=>$active_chat,'user_id'=>$user_id])){
                $ChatUser->closed = false;
                $ChatUser->save();
            }
            $messages = $this->pdoTools->getChunk($MessagesTpl, ['messages'=>$resp['data']['messages']]);

            return $this->success('',[
                'active_chat'=>$active_chat, 
                'chat'=>implode("\r\n",$output),
                'messages'=>$messages,
                ]);
        }else{
            return $this->error("error no chat find!");
        }

        return $this->error("error!");
    }
    public function add_chat($data)
    {
        //$chat = $data['chat'];
        $user_id = $this->modx->user->id;
        $hash = $data['hash'];
        $chat = (int)$data['chat'];
        $new_chat_user_id = $data['new_chat_user_id'];
        if(empty($hash)) $hash = $this->config['hash'];
        
        if(isset($_SESSION['RealMessenger'][$hash]['ContactGroups'])){
            $ContactGroups = $_SESSION['RealMessenger'][$hash]['ContactGroups'];
        }else{
            $ContactGroups = '2';
        }

        $default = array(
            'class' => 'RealMessengerChatUser',
            'where' => [
                'RealMessengerChat.id'=>$chat,
            ],
            'leftJoin' => [
                'RealMessengerChatUser2'=>[
                    'class'=>'RealMessengerChatUser',
                    'on'=>'RealMessengerChatUser.chat = RealMessengerChatUser2.chat',
                ],
                'RealMessengerChat'=>[
                    'class'=>'RealMessengerChat',
                    'on'=>'RealMessengerChatUser.chat = RealMessengerChat.id',
                ],
            ],
            'select' => [
                'RealMessengerChat'=>'*',
            ],
            'limit'=>1,
            'return' => 'data',
        );
        
        $this->pdoTools->setConfig($default, false);
        $rows = $this->pdoTools->run();
        if(count($rows) == 0){ // чата не найдено. создаем новый
            //добавить проверку на группу надо еще
            if($ContactGroups and $ContactGroups != '2'){
                if(!$cg = $this->modx->getObject('modUserGroupMember',[
                    'member'=>$new_chat_user_id,
                    'user_group:IN'=>explode(',',$ContactGroups),
                    ])){
                        return $this->error("user is not ContactGroups member!");
                }
            }
            if($chat = $this->modx->newObject('RealMessengerChat',['createdon'=>date('Y-m-d H:i:s'),'createdby'=>$user_id])){
                if($chat->save()){
                    if($ChatUserOun = $this->modx->newObject('RealMessengerChatUser',['user_id'=>$user_id,'chat'=>$chat->id,'timestamp'=>date('Y-m-d H:i:s')])){
                        if($ChatUserOun->save()){
                            if($ChatUser = $this->modx->newObject('RealMessengerChatUser',['user_id'=>$new_chat_user_id,'chat'=>$chat->id,'timestamp'=>date('Y-m-d H:i:s')])){
                                $ChatUser->save();
                                $rows = $this->pdoTools->run();
                            }
                        }
                    }
                }
            }
        }

        if(count($rows) > 0){  
            if(isset($_SESSION['RealMessenger'][$hash]['MessagesTpl'])){
                $MessagesTpl = $_SESSION['RealMessenger'][$hash]['MessagesTpl'];
            }else{
                $MessagesTpl = 'tpl.RealMessenger.messages';
            }
            if(isset($_SESSION['RealMessenger'][$hash]['ChatTpl'])){
                $ChatTpl = $_SESSION['RealMessenger'][$hash]['ChatTpl'];
            }else{
                $ChatTpl = 'tpl.RealMessenger.chat';
            }
            $output = [];
            $active_chat = 0;
            foreach($rows as $row){
                //собеседники
                $default_users = array(
                    'class' => 'RealMessengerChat',
                    'where' => [
                        'RealMessengerChatUser.user_id:!='=>$user_id,
                        'RealMessengerChat.id'=> $row['id'],
                    ],
                    'leftJoin' => [
                        'RealMessengerChatUser'=>[
                            'class'=>'RealMessengerChatUser',
                            'on'=>'RealMessengerChatUser.chat = RealMessengerChat.id',
                        ],
                        'modUser'=>[
                            'class'=>'modUser',
                            'on'=>'modUser.id = RealMessengerChatUser.user_id',
                        ],
                        'modUserProfile'=>[
                            'class'=>'modUserProfile',
                            'on'=>'modUserProfile.internalKey = RealMessengerChatUser.user_id',
                        ],
                    ],
                    'select' => [
                        //'RealMessengerChat'=>'*',
                        'modUser'=>$this->modx->getSelectColumns('modUser','modUser','',array('id','username')),
                        'modUserProfile'=>$this->modx->getSelectColumns('modUserProfile','modUserProfile','',array(
                            'id','internalKey','blocked','blockeduntil','blockedafter','logincount','thislogin','failedlogincount'
                            ,'sessionid'
                            ),true),
                    ],
                    'sortby'=>['RealMessengerChatUser.id'=>'ASC'],
                    'limit'=>10,
                    //'groupby'=>'RealMessengerChat.id',
                    'return' => 'data',
                );
                $this->pdoTools->setConfig($default_users, false);
                $row['users'] = $this->pdoTools->run();
                $row['user'] = $row['users'][0];
                unset($row['users'][0]);

                $row['class'] = 'active';
                $output[] = $this->pdoTools->getChunk($ChatTpl,$row);
                $active_chat = $row['id'];
            }
            
            $messages = '';
            $resp = $this->get_chat_messages(['chat'=> $active_chat]);
            if($ChatUser = $this->modx->getObject("RealMessengerChatUser",['chat'=>$active_chat,'user_id'=>$user_id])){
                $ChatUser->closed = false;
                $ChatUser->save();
            }
            $messages = $this->pdoTools->getChunk($MessagesTpl, ['messages'=>$resp['data']['messages']]);

            return $this->success('',[
                'active_chat'=>$active_chat, 
                'chat'=>implode("\r\n",$output),
                'messages'=>$messages,
                ]);
        }else{
            return $this->error("error no chat find!");
        }

        return $this->error("error!");
    }
    public function get_chat_messages($data)
    {
        //$chat = $data['chat'];
        $user_id = $this->modx->user->id;
        $hash = $data['hash'];
        if(empty($hash)) $hash = $this->config['hash'];
        if($chat = $this->modx->getObject('RealMessengerChat',['id'=>(int)$data['chat'],'closed'=>0])
            and $ChatUser = $this->modx->getObject('RealMessengerChatUser',['chat'=>(int)$data['chat'],'user_id'=>$user_id])
            ){
                $ChatUser->timestamp = date('Y-m-d H:i:s');
                $ChatUser->save();

            $default = array(
                'class' => 'RealMessengerMessage',
                'where' => [
                    'RealMessengerMessage.chat'=>$chat->id,
                    'RealMessengerMessage.deleted'=> 0,
                ],
                'leftJoin' => [
                    'modUser'=>[
                        'class'=>'modUser',
                        'on'=>'modUser.id = RealMessengerMessage.createdby',
                    ],
                    'modUserProfile'=>[
                        'class'=>'modUserProfile',
                        'on'=>'modUserProfile.internalKey = RealMessengerMessage.createdby',
                    ],
                ],
                'select' => [
                    'RealMessengerMessage'=>'*',
                    'modUser'=>$this->modx->getSelectColumns('modUser','modUser','',array('username')),
                    'modUserProfile'=>$this->modx->getSelectColumns('modUserProfile','modUserProfile','',array(
                        'id','internalKey','blocked','blockeduntil','blockedafter','logincount','thislogin','failedlogincount'
                        ,'sessionid'
                        ),true),
                ],
                'sortby'=>['RealMessengerMessage.id'=>'DESC'],
                'limit'=>50,
                'return' => 'data',
            );
            
            $this->pdoTools->setConfig($default, false);
            $rows = $this->pdoTools->run();
            //if(count($rows) > 0){
            if(isset($_SESSION['RealMessenger'][$hash]['MessageTpl'])){
                $MessageTpl = $_SESSION['RealMessenger'][$hash]['MessageTpl'];
            }else{
                $MessageTpl = 'tpl.RealMessenger.message';
            }
            $output = [];
            $rows = array_reverse($rows);
            $notify_ids = [];
            foreach($rows as $row){
                if($user_id == $row['createdby']) $row['ownmessage'] = 1;
                $output[] = $this->pdoTools->getChunk($MessageTpl,$row);
                $notify_ids[] = $row['notify_id'];
            }

            if($this->gtsNotify){
                $user_data = []; //$user_data[$m_user['user_id']][$chat->id]['chat_count']
                $user_data[$user_id][$chat->id]['chat_count'] = 0;
                $user_data[$user_id][$chat->id]['find_or_new_chat'] = 1;
                $this->gtsNotify->remove_channel_notifys($notify_ids,'RealMessenger',$user_data);
            }
            //собеседники
            $default_users = array(
                'class' => 'RealMessengerChat',
                'where' => [
                    'RealMessengerChatUser.user_id:!='=>$user_id,
                    'RealMessengerChat.id'=> $chat->id,
                ],
                'leftJoin' => [
                    'RealMessengerChatUser'=>[
                        'class'=>'RealMessengerChatUser',
                        'on'=>'RealMessengerChatUser.chat = RealMessengerChat.id',
                    ],
                    'modUser'=>[
                        'class'=>'modUser',
                        'on'=>'modUser.id = RealMessengerChatUser.user_id',
                    ],
                    'modUserProfile'=>[
                        'class'=>'modUserProfile',
                        'on'=>'modUserProfile.internalKey = RealMessengerChatUser.user_id',
                    ],
                ],
                'select' => [
                    //'RealMessengerChat'=>'*',
                    'modUser'=>$this->modx->getSelectColumns('modUser','modUser','',array('id','username')),
                    'modUserProfile'=>$this->modx->getSelectColumns('modUserProfile','modUserProfile','',array(
                        'id','internalKey','blocked','blockeduntil','blockedafter','logincount','thislogin','failedlogincount'
                        ,'sessionid'
                        ),true),
                ],
                'sortby'=>['RealMessengerChatUser.id'=>'ASC'],
                'limit'=>10,
                //'groupby'=>'RealMessengerChat.id',
                'return' => 'data',
            );
            $this->pdoTools->setConfig($default_users, false);
            $users = $this->pdoTools->run();
            $user = $users[0];
            
            //get status online
            if($this->gtsNotify){
                $resp = $this->gtsNotify->getStatusOnline($user['id']);
                if($resp['success']){
                    $user['statuson'] = true;
                    $user['status'] = $resp['data']['status'];
                }
            }

            unset($users[0]);

            return $this->success('',['messages'=>implode("\r\n",$output),'user'=>$user,'users'=>$users]);    
        }
        return $this->error("error!");
    }

    public function get_chats($active_chat = false)
    {
        
        
        //$chat = $data['chat'];
        $user_id = $this->modx->user->id;
        $default = array(
            'class' => 'RealMessengerChat',
            'where' => [
                'RealMessengerChatUser.user_id'=>$user_id,
                'RealMessengerChatUser.closed'=>0,
                'RealMessengerChat.closed'=> 0,
            ],
            'leftJoin' => [
                'RealMessengerChatUser'=>[
                    'class'=>'RealMessengerChatUser',
                    'on'=>'RealMessengerChatUser.chat = RealMessengerChat.id',
                ],

            ],
            'select' => [
                'RealMessengerChat'=>'*',
                'RealMessengerChatUser'=>'RealMessengerChatUser.timestamp as user_timestamp',
            ],
            'sortby'=>['RealMessengerChatUser.timestamp'=>'DESC'],
            'limit'=>10,
            //'groupby'=>'RealMessengerChat.id',
            'return' => 'data',
        );
        
        $this->pdoTools->setConfig($default, false);
        $rows = $this->pdoTools->run();
        if(count($rows) > 0){
            //$hash = $data['hash'];
            if(empty($hash)) $hash = $this->config['hash'];
            if(isset($_SESSION['RealMessenger'][$hash]['ChatTpl'])){
                $ChatTpl = $_SESSION['RealMessenger'][$hash]['ChatTpl'];
            }else{
                $ChatTpl = 'tpl.RealMessenger.chat';
            }
            if(isset($_SESSION['RealMessenger'][$hash]['LastMessageTpl'])){
                $LastMessageTpl = $_SESSION['RealMessenger'][$hash]['LastMessageTpl'];
            }else{
                $LastMessageTpl = 'tpl.RealMessenger.last_message';
            }

            $output = [];
            foreach($rows as $row){
                
                //считаем новые сообщения
                $query = $this->modx->newQuery('RealMessengerMessage');
                $query->where(array(
                    array(
                        'chat:=' => $row['id'],
                        array(
                            'AND:createdon:>' => $row['user_timestamp'],
                            'OR:editedon:>' => $row['user_timestamp'],
                        ),
                    ),
                ));
                $row['messages_new_count'] = $this->modx->getCount('RealMessengerMessage',$query);
                //последнее сообщение
                $query = $this->modx->newQuery('RealMessengerMessage');
                $query->where(array(
                    'chat' => $row['id'],
                ));
                $query->sortby('createdon','DESC');
                if($last_message = $this->modx->getObject('RealMessengerMessage',$query)){
                    $row['last_message'] = $this->pdoTools->getChunk($LastMessageTpl,$last_message->toArray()); //$last_message->text;
                }
                //активный чат $active_chat
                if($active_chat){
                    if($active_chat == $row['id']){
                        $row['class'] = 'active';
                        $active_chat = false;
                    }
                }
                //собеседники
                $default_users = array(
                    'class' => 'RealMessengerChat',
                    'where' => [
                        'RealMessengerChatUser.user_id:!='=>$user_id,
                        'RealMessengerChat.id'=> $row['id'],
                    ],
                    'leftJoin' => [
                        'RealMessengerChatUser'=>[
                            'class'=>'RealMessengerChatUser',
                            'on'=>'RealMessengerChatUser.chat = RealMessengerChat.id',
                        ],
                        'modUser'=>[
                            'class'=>'modUser',
                            'on'=>'modUser.id = RealMessengerChatUser.user_id',
                        ],
                        'modUserProfile'=>[
                            'class'=>'modUserProfile',
                            'on'=>'modUserProfile.internalKey = RealMessengerChatUser.user_id',
                        ],
                    ],
                    'select' => [
                        //'RealMessengerChat'=>'*',
                        'modUser'=>$this->modx->getSelectColumns('modUser','modUser','',array('id','username')),
                        'modUserProfile'=>$this->modx->getSelectColumns('modUserProfile','modUserProfile','',array(
                            'id','internalKey','blocked','blockeduntil','blockedafter','logincount','thislogin','failedlogincount'
                            ,'sessionid'
                            ),true),
                    ],
                    'sortby'=>['RealMessengerChatUser.id'=>'ASC'],
                    'limit'=>10,
                    //'groupby'=>'RealMessengerChat.id',
                    'return' => 'data',
                );
                $this->pdoTools->setConfig($default_users, false);
                $row['users'] = $this->pdoTools->run();
                $row['user'] = $row['users'][0];
                
                //get status online
                if($this->gtsNotify){
                    $resp = $this->gtsNotify->getStatusOnline($row['user']['id']);
                    if($resp['success']){
                        $row['user']['statuson'] = true;
                        $row['user']['status'] = $resp['data']['status'];
                    }
                }

                unset($row['users'][0]);

                $output[] = $this->pdoTools->getChunk($ChatTpl,$row);
            }
            return $this->success('',array('chats'=>implode("\r\n",$output)));
        }else{
            return $this->error("error!");
        }
                
        return $this->error("error!");
    }

    public function search_contact()
    {
        //$hash = $data['hash'];
        if(empty($hash)) $hash = $this->config['hash'];
        if(isset($_SESSION['RealMessenger'][$hash]['SearchContactTpl'])){
            $SearchContactTpl = $_SESSION['RealMessenger'][$hash]['SearchContactTpl'];
        }else{
            $SearchContactTpl = 'tpl.RealMessenger.search.contact';
        }
        if(isset($_SESSION['RealMessenger'][$hash]['ContactGroups'])){
            $ContactGroups = $_SESSION['RealMessenger'][$hash]['ContactGroups'];
        }else{
            $ContactGroups = '2';
        }
        $select = [];
        $select['fields'] = 'id,fullname';
        $pdoUser = [];
        $pdoUser['groups'] = $ContactGroups;

        if(empty($select['fields'])) $pdoUser['select'] = 'id,fullname';
        $pdoUser = $this->pdoUsersConfig($pdoUser);
        

        $select['pdoTools'] = $pdoUser;
        if(empty($select['content'])) $select['content'] = '{$fullname}';
        $select['where'] = [
            'modUserProfile.fullname:LIKE'=> '%query%',
        ];

        $_SESSION['RealMessenger'][$hash]['search_contact'] =  $select;

        $output = $this->pdoTools->getChunk($SearchContactTpl, []);
        return $this->success('',array('search_contact'=>$output));     
        return $this->error("error!");
    }

    public function search_chat()
    {
        if(empty($hash)) $hash = $this->config['hash'];
        if(isset($_SESSION['RealMessenger'][$hash]['SearchContactTpl'])){
            $SearchContactTpl = $_SESSION['RealMessenger'][$hash]['SearchContactTpl'];
        }else{
            $SearchContactTpl = 'tpl.RealMessenger.search.contact';
        }
        return $this->pdoTools->getChunk($SearchContactTpl, ['search_goal'=>'chat','search_goal_label'=>'Поиск чатов']);    
    }

    public function autocomplect_search_chat($data)
    {
        
        //получаем чаты пользователя
        $user_id = $this->modx->user->id;
        $chats = [
            'class' => 'RealMessengerChatUser',
            'where' => [
                'RealMessengerChatUser.user_id'=>$user_id,
            ],
            'select' => [
                'RealMessengerChatUser'=>'RealMessengerChatUser.chat',
            ],
            'sortby'=>['RealMessengerChatUser.id'=>'ASC'],
            'limit'=>0,
            'return' => 'data',
        ];
        $this->pdoTools->setConfig($chats, false);
        $chats0 = $this->pdoTools->run();
        $chats_ids = [];
        foreach($chats0 as $chat){
            $chats_ids[] = $chat['chat'];
        }
        
        $default = [
            'class' => 'RealMessengerChat',
            'leftJoin'=>[
                'RealMessengerChatUser'=>[
                    'class'=>'RealMessengerChatUser',
                    'on'=>'RealMessengerChatUser.chat = RealMessengerChat.id',
                ],
                'modUser'=>[
                    'class'=>'modUser',
                    'on'=>'modUser.id = RealMessengerChatUser.user_id',
                ],
                'modUserProfile'=>[
                    'class'=>'modUserProfile',
                    'on'=>'modUserProfile.internalKey = RealMessengerChatUser.user_id',
                ],
            ],
            'where' => [
                'RealMessengerChat.id:IN'=>$chats_ids,
                'RealMessengerChatUser.user_id:!='=>$user_id,
            ],
            'groupby'=>'RealMessengerChat.id',
            'select' => [
                'RealMessengerChatUser'=>'RealMessengerChatUser.user_id as id',
                'modUserProfile'=>"GROUP_CONCAT(modUserProfile.fullname SEPARATOR ', ') as users_fullname",
            ],
            'sortby'=>['RealMessengerChatUser.id'=>'ASC'],
            'limit'=>0,
            'return' => 'data',
        ];

        $query = $data['query'];
        if($query){
            $default['where']['modUserProfile.fullname:LIKE'] = "%{$query}%";
        }
            
        
        $this->pdoTools->setConfig($default, false);
        $rows = $this->pdoTools->run();
        $output = [];
        foreach($rows as $row){
            $output[] = '<li><a href="#" data-id="'.$row['id'].'">'.$row['users_fullname'].'</a></li>';
        }
        return $this->success('',array('html'=>implode("\r\n",$output)));
    }

    public function autocomplect_search_contact($data)
    {
        $hash = $data['hash'];
        if(empty($hash)) $hash = $this->config['hash'];
        if(isset($_SESSION['RealMessenger'][$hash]['search_contact'])){
            $select = $_SESSION['RealMessenger'][$hash]['search_contact'];
        }else{
            return $this->error("select search_contact не найден!");
        }

        $select['pdoTools']['limit'] = 0;
        if(empty($select['where'])){
            $select['where'] = [];
        }
        $where = [];
        
            $query = $data['query'];
            if($query){
                foreach($select['where'] as $field=>$value){
                    $value = str_replace('query',$query,$value);
                    $where[$field] = $value;
                }
            }
            if(!empty($where)){
                if(empty($select['pdoTools']['where'])) $select['pdoTools']['where'] = [];
                if(is_string($select['pdoTools']['where'])) $select['pdoTools']['where'] = json_decode($select['pdoTools']['where'],1);
                $select['pdoTools']['where'] = array_merge($select['pdoTools']['where'],$where);
            }
        
        //$this->pdoTools->config = array_merge($this->config['pdoClear'],$select['pdoTools']);
        $select['pdoTools']['return'] = 'data';
        $this->pdoTools->setConfig($select['pdoTools'], false);
        $rows = $this->pdoTools->run();
        $output = [];
        foreach($rows as $row){
            $content = $this->pdoTools->getChunk('@INLINE '.$select['content'],$row);
            $output[] = '<li><a href="#" data-id="'.$row['id'].'">'.$content.'</a></li>';
        }
        return $this->success('',array('html'=>implode("\r\n",$output)));
    }
    /**
     * @return bool
     */
    public function loadServices()
    {
        $this->error = $this->modx->getService('error', 'error.modError', '', '');
        return true;
    }


    /**
     * Shorthand for the call of processor
     *
     * @access public
     *
     * @param string $action Path to processor
     * @param array $data Data to be transmitted to the processor
     *
     * @return mixed The result of the processor
     */
    public function runProcessor($action = '', $data = array())
    {
        if (empty($action)) {
            return false;
        }
        #$this->modx->error->reset();
        $processorsPath = !empty($this->config['processorsPath'])
            ? $this->config['processorsPath']
            : MODX_CORE_PATH . 'components/realmessenger/processors/';

        return $this->modx->runProcessor($action, $data, array(
            'processors_path' => $processorsPath,
        ));
    }


    /**
     * Method loads custom classes from specified directory
     *
     * @var string $dir Directory for load classes
     *
     * @return void
     */
    public function loadCustomClasses($dir)
    {
        $files = scandir($this->config['customPath'] . $dir);
        foreach ($files as $file) {
            if (preg_match('/.*?\.class\.php$/i', $file)) {
                include_once($this->config['customPath'] . $dir . '/' . $file);
            }
        }
    }


    /**
     * Добавление ошибок
     * @param string $message
     * @param array $data
     */
    public function addError($message, $data = array())
    {
        $message = $this->modx->lexicon($message, $data);
        $this->error->addError($message);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->modx->error->getErrors();
    }

    /**
     * Вернут true если были ошибки
     * @return boolean
     */
    public function hasError()
    {
        return $this->modx->error->hasError();
    }


    /**
     * Обработчик для событий
     * @param modSystemEvent $event
     * @param array $scriptProperties
     */
    public function loadHandlerEvent(modSystemEvent $event, $scriptProperties = array())
    {
        switch ($event->name) {
            case 'OnHandleRequest':
            case 'OnLoadWebDocument':
                break;
        }

    }
    public function error($message = '', $data = array())
    {
        if(is_array($message)) $message = $this->modx->lexicon($message['lexicon'], $message['data']);
        $response = array(
            'success' => false,
            'message' => $message,
            'data' => $data,
        );

        return $response;
    }
    
    public function success($message = '', $data = array())
    {
        if(is_array($message)) $message = $this->modx->lexicon($message['lexicon'], $message['data']);
        $response = array(
            'success' => true,
            'message' => $message,
            'data' => $data,
        );

        return $response;
    }

    public function makePlaceholders($config)
    {
        $placeholders = [];
        foreach($config as $k=>$v){
            if(is_string($v)){
                $placeholders['pl'][] = "[[+$k]]";
                $placeholders['vl'][] = $v;
            }
        }
        return $placeholders;
    }
    
    public function pdoUsersConfig($sp = array())
    {
        $class = 'modUser';
        $profile = 'modUserProfile';
        $member = 'modUserGroupMember';
        //$this->pdoTools->addTime('select sp'.print_r($sp,1));
        // Start building "Where" expression
        $where = array();
        if (empty($showInactive)) {
            $where[$class . '.active'] = 1;
        }
        if (empty($showBlocked)) {
            $where[$profile . '.blocked'] = 0;
        }

        // Add users profiles and groups
        $innerJoin = array(
            $profile => array('alias' => $profile, 'on' => "$class.id = $profile.internalKey"),
        );

        // Filter by users, groups and roles
        $tmp = array(
            'users' => array(
                'class' => $class,
                'name' => 'username',
                'join' => $class . '.id',
            ),
            'groups' => array(
                'class' => 'modUserGroup',
                'name' => 'name',
                'join' => $member . '.user_group',
            ),
            'roles' => array(
                'class' => 'modUserGroupRole',
                'name' => 'name',
                'join' => $member . '.role',
            ),
        );
        foreach ($tmp as $k => $p) {
            if (!empty($sp[$k])) {
                $$k = $sp[$k];
                $$k = array_map('trim', explode(',', $$k));
                ${$k . '_in'} = ${$k . '_out'} = $fetch_in = $fetch_out = array();
                foreach ($$k as $v) {
                    if (is_numeric($v)) {
                        if ($v[0] == '-') {
                            ${$k . '_out'}[] = abs($v);
                        } else {
                            ${$k . '_in'}[] = abs($v);
                        }
                    } else {
                        if ($v[0] == '-') {
                            $fetch_out[] = $v;
                        } else {
                            $fetch_in[] = $v;
                        }
                    }
                }

                if (!empty($fetch_in) || !empty($fetch_out)) {
                    $q = $this->modx->newQuery($p['class'], array($p['name'] . ':IN' => array_merge($fetch_in, $fetch_out)));
                    $q->select('id,' . $p['name']);
                    $tstart = microtime(true);
                    if ($q->prepare() && $q->stmt->execute()) {
                        $this->modx->queryTime += microtime(true) - $tstart;
                        $this->modx->executedQueries++;
                        while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                            if (in_array($row[$p['name']], $fetch_in)) {
                                ${$k . '_in'}[] = $row['id'];
                            } else {
                                ${$k . '_out'}[] = $row['id'];
                            }
                        }
                    }
                }

                if (!empty(${$k . '_in'})) {
                    $where[$p['join'] . ':IN'] = ${$k . '_in'};
                }
                if (!empty(${$k . '_out'})) {
                    $where[$p['join'] . ':NOT IN'] = ${$k . '_out'};
                }
            }
        }

        if (!empty($groups_in) || !empty($groups_out) || !empty($roles_in) || !empty($roles_out)) {
            $innerJoin[$member] = array('alias' => $member, 'on' => "$class.id = $member.member");
        }

        // Fields to select
        $select = array(
            $profile => implode(',', array_keys($this->modx->getFieldMeta($profile))),
            $class => implode(',', array_keys($this->modx->getFieldMeta($class))),
        );

        // Add custom parameters
        foreach (array('where', 'innerJoin', 'select') as $v) {
            if (!empty($sp[$v])) {
                $tmp = $sp[$v];
                if (!is_array($tmp)) {
                    $tmp = json_decode($tmp, true);
                }
                if (is_array($tmp)) {
                    $$v = array_merge($$v, $tmp);
                }
            }
            unset($sp[$v]);
        }
        //$this->pdoTools->addTime('Conditions prepared');

        $default = array(
            'class' => $class,
            'innerJoin' => json_encode($innerJoin),
            'where' => json_encode($where),
            'select' => json_encode($select),
            'groupby' => $class . '.id',
            'sortby' => $class . '.id',
            'sortdir' => 'ASC',
            'fastMode' => false,
            'return' => 'data',
            'disableConditions' => true,
        );

        if (!empty($users_in) && (empty($sp['sortby']) || $sp['sortby'] == $class . '.id')) {
            $sp['sortby'] = "find_in_set(`$class`.`id`,'" . implode(',', $users_in) . "')";
            $sp['sortdir'] = '';
        }

        // Merge all properties and run!
        //$this->pdoTools->addTime('Query parameters ready');
        return array_merge($default, $sp);
    }
    
    /**
     * Sanitize any text through Jevix snippet
     *
     * @param string $text Text for sanitization
     * @param string $setName Name of property set for get parameters from
     * @param boolean $replaceTags Replace MODX tags?
     *
     * @return string
     */
    public function Jevix($text = null, $setName = 'RealMessenger', $replaceTags = true) {
        if (empty($text)) {
            return ' ';
        }
        if (!$snippet = $this->modx->getObject('modSnippet', array('name' => 'Jevix'))) {
            return 'Could not load snippet Jevix';
        }
        // Loading parser if needed - it is for mgr context
        if (!is_object($this->modx->parser)) {
            $this->modx->getParser();
        }

        $params = array();
        if ($setName) {
            $params = $snippet->getPropertySet($setName);
        }

        $text = html_entity_decode($text, ENT_COMPAT, 'UTF-8');
        $params['input'] = str_replace(
            array('[', ']', '{', '}'),
            array('*(*(*(*(*(*', '*)*)*)*)*)*', '~(~(~(~(~(~', '~)~)~)~)~)~'),
            $text
        );

        $snippet->setCacheable(false);
        $filtered = $snippet->process($params);

        if ($replaceTags) {
            $filtered = str_replace(
                array('*(*(*(*(*(*', '*)*)*)*)*)*', '`', '~(~(~(~(~(~', '~)~)~)~)~)~'),
                array('&#91;', '&#93;', '&#96;', '&#123;', '&#125;'),
                $filtered
            );
        }
        else {
            $filtered = str_replace(
                array('*(*(*(*(*(*', '*)*)*)*)*)*', '~(~(~(~(~(~', '~)~)~)~)~)~'),
                array('[', ']', '{', '}'),
                $filtered
            );
        }

        return $filtered;
    }
}