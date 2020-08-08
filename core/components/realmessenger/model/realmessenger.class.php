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
            case 'save_chat_message': 
                return $this->get_new_chat_message($data);
                break;
            case 'autocomplect_search_contact': 
                return $this->autocomplect_search_contact($data);
                break;
            default:
                return $this->error("Метод $action в классе $class не найден!");
        }
    }
    
    public function get_chat_messages($data)
    {
        //$chat = $data['chat'];
        $user_id = $this->modx->user->id;
        if($chat = $this->modx->getObject('RealMessengerChat',['id'=>(int)$data['chat'],'closed'=>0])
            and $ChatUser = $this->modx->getObject('RealMessengerChatUser',['chat'=>(int)$data['chat'],'user_id'=>$user_id])
            ){
            $default = array(
                'class' => 'RealMessengerMessage',
                'where' => [
                    //'gtsNotifyNotifyPurpose.active'=>1,
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
            if(count($rows) > 0){
                if(isset($_SESSION['RealMessenger'][$data['hash']]['MessageTpl'])){
                    $MessageTpl = $_SESSION['RealMessenger'][$data['hash']]['MessageTpl'];
                }else{
                    $MessageTpl = 'tpl.RealMessenger.message';
                }
                $output = [];
                foreach($rows as $row){
                    if($gtsNotify) {
                        $gtsNotify->remove_channel_notify($row['notify_id'],'RealMessenger');
                    }
                    $output[] = $this->pdoTools->getChunk($MessageTpl,$row);
                }
                return $this->success('',array('messages'=>implode("\r\n",$output)));
            }else{
                return $this->error("error!");
            }
                
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
                //'gtsNotifyNotifyPurpose.active'=>1,
                'RealMessengerChatUser.user_id'=>$user_id,
                'RealMessengerChat.closed'=> 0,
            ],
            'leftJoin' => [
                'RealMessengerChatUser'=>[
                    'class'=>'RealMessengerChatUser',
                    'on'=>'RealMessengerChatUser.chat = RealMessengerChat.id',
                ],
                /*'modUser'=>[
                    'class'=>'modUser',
                    'on'=>'modUser.id = RealMessengerChatUser.user_id',
                ],
                'modUserProfile'=>[
                    'class'=>'modUserProfile',
                    'on'=>'modUserProfile.internalKey = RealMessengerChatUser.user_id',
                ],*/

            ],
            'select' => [
                'RealMessengerChat'=>'*',
                /*'modUser'=>$this->modx->getSelectColumns('modUser','modUser','',array('username')),
                'modUserProfile'=>$this->modx->getSelectColumns('modUserProfile','modUserProfile','',array(
                    'id','internalKey','blocked','blockeduntil','blockedafter','logincount','thislogin','failedlogincount'
                    ,'sessionid'
                    ),true),*/
            ],
            'sortby'=>['RealMessengerChatUser.timestamp'=>'DESC'],
            'limit'=>10,
            //'groupby'=>'RealMessengerChat.id',
            'return' => 'data',
        );
        
        $this->pdoTools->setConfig($default, false);
        $rows = $this->pdoTools->run();
        if(count($rows) > 0){
            if(isset($_SESSION['RealMessenger'][$data['hash']]['ChatTpl'])){
                $ChatTpl = $_SESSION['RealMessenger'][$data['hash']]['ChatTpl'];
            }else{
                $ChatTpl = 'tpl.RealMessenger.chat';
            }
            $output = [];
            foreach($rows as $row){
                if($gtsNotify) {
                    $gtsNotify->remove_channel_notify($row['notify_id'],'RealMessenger');
                }
                
                //считаем новые сообщения
                $query = $xpdo->newQuery('RealMessengerMessage');
                $query->where(array(
                    array(
                        'chat:=' => $row['id'],
                        array(
                            'AND:createdon:>=' => $row['timestamp'],
                            'OR:createdon:>=' => $row['timestamp'],
                        ),
                    ),
                ));
                $row['messages_new_count'] = $this->modx->getCount('RealMessengerMessage',$query);
                
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
                $this->pdoTools->setConfig($default, false);
                $row['users'] = $this->pdoTools->run();
                $row['user'] = $row['users'][0];
                unset($row['users'][0]);

                $output[] = $this->pdoTools->getChunk($ChatTpl,$row);
            }
            return $this->success('',array('chats'=>implode("\r\n",$output)));
        }else{
            return $this->error("error!");
        }
                
        return $this->error("error!");
    }

    public function search_contact($ContactGroups = false)
    {
        if(isset($_SESSION['RealMessenger'][$data['hash']]['SearchContactTpl'])){
            $SearchContactTpl = $_SESSION['RealMessenger'][$data['hash']]['SearchContactTpl'];
        }else{
            $SearchContactTpl = 'tpl.RealMessenger.search.contact';
        }
        $select = [];
        $select['fields'] = 'id,fullname';
        $pdoUser = [];
        $pdoUser['groups'] = $ContactGroups;

        if(empty($select['fields'])) $pdoUser['select'] = 'id,fullname';
        $pdoUser = $this->pdoUsersConfig($pdoUser);
        

        $select['pdoTools'] = $pdoUser;
        if(empty($select['content'])) $select['content'] = '{$fullname}';
        

        $_SESSION['RealMessenger'][$data['hash']]['search_contact'] =  $select;

        $output = $this->pdoTools->getChunk($SearchContactTpl, $select);
        return $this->success('',array('search_contact'=>$output));     
        return $this->error("error!");
    }
    
    public function autocomplect_search_contact($data)
    {
        $hash = $data['hash'];
        if(isset($_SESSION['RealMessenger'][$data['hash']]['search_contact'])){
            $select = $_SESSION['RealMessenger'][$data['hash']]['search_contact'];
        }else{
            return $this->error("select $select_name не найден!");
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
}