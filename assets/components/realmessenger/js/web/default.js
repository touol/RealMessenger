(function (window, document, $, RealMessengerConfig) {
    var RealMessenger = RealMessenger || {};
    RealMessengerConfig.callbacksObjectTemplate = function () {
        return {
            // return false to prevent send data
            before: [],
            response: {
                success: [],
                error: []
            },
            ajax: {
                done: [],
                fail: [],
                always: []
            }
        }
    };
    RealMessenger.Callbacks = RealMessengerConfig.Callbacks = {
        Chat: {
            load: RealMessengerConfig.callbacksObjectTemplate(),
            close: RealMessengerConfig.callbacksObjectTemplate(),
            remove: RealMessengerConfig.callbacksObjectTemplate(),
            save_message: RealMessengerConfig.callbacksObjectTemplate(),
            send_read_messages: RealMessengerConfig.callbacksObjectTemplate(),
        },
        Autocomplect: {
            load: RealMessengerConfig.callbacksObjectTemplate(),
            find_or_new_chat: RealMessengerConfig.callbacksObjectTemplate(),
            //remove: RealMessengerConfig.callbacksObjectTemplate(),
        },
    };
    
    RealMessenger.Callbacks.add = function (path, name, func) {
        if (typeof func != 'function') {
            return false;
        }
        path = path.split('.');
        var obj = RealMessenger.Callbacks;
        for (var i = 0; i < path.length; i++) {
            if (obj[path[i]] == undefined) {
                return false;
            }
            obj = obj[path[i]];
        }
        if (typeof obj != 'object') {
            obj = [obj];
        }
        if (name != undefined) {
            obj[name] = func;
        }
        else {
            obj.push(func);
        }
        return true;
    };
    RealMessenger.Callbacks.remove = function (path, name) {
        path = path.split('.');
        var obj = RealMessenger.Callbacks;
        for (var i = 0; i < path.length; i++) {
            if (obj[path[i]] == undefined) {
                return false;
            }
            obj = obj[path[i]];
        }
        if (obj[name] != undefined) {
            delete obj[name];
            return true;
        }
        return false;
    };
    RealMessenger.setup = function () {
        // selectors & $objects
        this.actionName = 'RealMessenger';
        //this.action = ':submit[name=' + this.actionName + ']';
        //this.form = '.gts-form';
        this.$doc = $(document);

        this.sendData = {
            action: this.actionName,
            data: null
        };

        this.timeout = 300;
    };
    RealMessenger.initialize = function () {
        RealMessenger.setup();
        var d = $('#realmessenger-messages');
        d.scrollTop(d.prop("scrollHeight"));
            
        RealMessenger.Chat.initialize();
        RealMessenger.Autocomplect.initialize();
    };
    RealMessenger.send = function (data, callbacks, userCallbacks) {
        var runCallback = function (callback, bind) {
            if (typeof callback == 'function') {
                return callback.apply(bind, Array.prototype.slice.call(arguments, 2));
            }
            else if (typeof callback == 'object') {
                for (var i in callback) {
                    if (callback.hasOwnProperty(i)) {
                        var response = callback[i].apply(bind, Array.prototype.slice.call(arguments, 2));
                        if (response === false) {
                            return false;
                        }
                    }
                }
            }
            return true;
        };
        // set context
        if ($.isArray(data)) {
            data.push({
                name: 'ctx',
                value: RealMessengerConfig.ctx
            });
        }
        
        else if ($.isPlainObject(data)) {
            data.ctx = RealMessengerConfig.ctx;
        }
        else if (typeof data == 'string') {
            data += '&ctx=' + RealMessengerConfig.ctx;
        }

        // set action url
        var url =  RealMessengerConfig.actionUrl;
        var method = 'post';
        // callback before
        if (runCallback(callbacks.before) === false || runCallback(userCallbacks.before) === false) {
            return;
        }
        // send
        var xhr = function (callbacks, userCallbacks) {
            return $[method](url, data, function (response) {
                if (response.success) {
                    if (response.message) {
                        //RealMessenger.Message.success(response.message);
                    }
                    runCallback(callbacks.response.success, RealMessenger, response);
                    runCallback(userCallbacks.response.success, RealMessenger, response);
                }
                else {
                    //RealMessenger.Message.error(response.message);
                    runCallback(callbacks.response.error, RealMessenger, response);
                    runCallback(userCallbacks.response.error, RealMessenger, response);
                }
            }, 'json').done(function () {
                runCallback(callbacks.ajax.done, RealMessenger, xhr);
                runCallback(userCallbacks.ajax.done, RealMessenger, xhr);
            }).fail(function () {
                runCallback(callbacks.ajax.fail, RealMessenger, xhr);
                runCallback(userCallbacks.ajax.fail, RealMessenger, xhr);
            }).always(function (response) {
                
                runCallback(callbacks.ajax.always, RealMessenger, xhr);
                runCallback(userCallbacks.ajax.always, RealMessenger, xhr);
            });
        }(callbacks, userCallbacks);
    };
    RealMessenger.Chat = {
        
        callbacks: {
            load: RealMessengerConfig.callbacksObjectTemplate(),
            close: RealMessengerConfig.callbacksObjectTemplate(),
            remove: RealMessengerConfig.callbacksObjectTemplate(),
            save_message: RealMessengerConfig.callbacksObjectTemplate(),
            send_read_messages: RealMessengerConfig.callbacksObjectTemplate(),
        },
        
        initialize: function () {
            RealMessenger.$doc
                .on('click', '.realmessenger-chat-body', function (e) {
                    
                    e.preventDefault();
                    $chat = $(this).closest('.realmessenger-chat');
                    chat = $chat.data('id');
                    hash = $(this).closest('#realmesseger').data('hash');
                    
                    RealMessenger.sendData.$chat = $chat;
                    
                    RealMessenger.sendData.data = {
                        hash: hash,
                        action: 'get_chat_messages',
                        chat: chat,
                    };
                    var callbacks = RealMessenger.Chat.callbacks;
            
                    callbacks.load.response.success = function (response) {
                        $chat = RealMessenger.sendData.$chat;
                        $('#realmessenger-message-form input[name=chat]').val($chat.data('id'));
                        $('#realmessenger-message-form-wrapper').show();
                        $(".realmessenger-chat").removeClass('active');
                        $chat.addClass('active');
                        $chat.find('.messages-new-count').hide();
                        $clone = $chat.clone();
                        
                        $('.realmessenger-chat[data-id=' + $chat.data('id') + ']').remove();
                        $(".realmessenger-chats").prepend($clone);
                        
                        $('#realmessenger-messages').html(response.data.messages);
                        var d = $('#realmessenger-messages');
                        d.scrollTop(d.prop("scrollHeight"));
                        if($('.realmessenger-message').length > 0){
                            $('.realmessenger-messages-empty').hide();
                        }else{
                            $('.realmessenger-messages-empty').show();
                        }
                        
                    };
                    RealMessenger.send(RealMessenger.sendData.data, RealMessenger.Chat.callbacks.load, RealMessenger.Callbacks.Chat.load);
                
                });
            RealMessenger.$doc
                .on('click', '.realmessenger-chat-close', function (e) {
                    
                    e.preventDefault();
                    $chat = $(this).closest('.realmessenger-chat');
                    chat = $chat.data('id');
                    hash = $(this).closest('#realmesseger').data('hash');
                    
                    RealMessenger.sendData.$chat = $chat;
                    
                    RealMessenger.sendData.data = {
                        hash: hash,
                        action: 'close_chat',
                        chat: chat,
                    };
                    var callbacks = RealMessenger.Chat.callbacks;
            
                    callbacks.close.response.success = function (response) {
                        $chat = RealMessenger.sendData.$chat;
                        $chat.remove();
                        $('#realmessenger-messages').html('');
                    };
                    RealMessenger.send(RealMessenger.sendData.data, RealMessenger.Chat.callbacks.close, RealMessenger.Callbacks.Chat.close);
                
                });
            RealMessenger.$doc
                .on('submit', 'form#realmessenger-message-form', function (e) {
                    
                    e.preventDefault();
                    hash = $(this).closest('#realmesseger').data('hash');
                    
                    $form = $(this);
                    file_ids = [];
                    $('.dz-preview').each(function( index ) {
                        file_ids.push($(this).data('userfiles-id'));
                        $(this).remove();
                    });
                    $form.find('[name=file_ids]').val(file_ids.join());
                    RealMessenger.sendData.$form = $form;
                    var formData = $form.serializeArray();

                    if($(this).find('textarea').val() == '' && file_ids.join() == '') return;
                    $form.find('textarea').val('');

                    RealMessenger.sendData.data = {
                        hash: hash,
                        action: 'save_message',
                        data: formData,
                    };
                    var callbacks = RealMessenger.Chat.callbacks;
            
                    callbacks.save_message.response.success = function (response) {
                        $form = RealMessenger.sendData.$form;
                        $form.find('textarea').val('');
                        $messages = $(response.data.messages);
                        $('#realmessenger-messages').append($messages);
                        var d = $('#realmessenger-messages');
                        d.scrollTop(d.prop("scrollHeight"));
                        $('.realmessenger-messages-empty').hide();
                    };
                    RealMessenger.send(RealMessenger.sendData.data, RealMessenger.Chat.callbacks.save_message, RealMessenger.Callbacks.Chat.save_message);
                
                });
            RealMessenger.$doc
                .on('keyup', 'form#realmessenger-message-form textarea', function (e) {
                    
                    //e.preventDefault();
                    $el_chat = $('.realmessenger-chat.active');
                    if($el_chat.find('.messages-new-count').text() == 0) return;

                    hash = $(this).closest('#realmesseger').data('hash');
                    RealMessenger.sendData.$el_chat = $el_chat;

                    RealMessenger.sendData.data = {
                        hash: hash,
                        action: 'send_read_messages',
                        chat: $el_chat.data('id'),
                    };
                    var callbacks = RealMessenger.Chat.callbacks;
            
                    callbacks.send_read_messages.response.success = function (response) {
                        $el_chat = RealMessenger.sendData.$el_chat;
                    };
                    RealMessenger.send(RealMessenger.sendData.data, RealMessenger.Chat.callbacks.send_read_messages, RealMessenger.Callbacks.Chat.send_read_messages);
                
                });
            document.addEventListener("gtsnotifyprovider", function(event) { 
                //console.log('notify',event.detail);
                for(var key in event.detail.channels) {
                    if(key == 'RealMessenger'){
                        user_data = event.detail.channels[key].data.user_data;
                        for(var chat in user_data) {
                            $el_chat = $('.realmessenger-chat[data-id="' + chat + '"]');
                            $badge = $el_chat.find('.messages-new-count');
                            $badge.text(user_data[chat].chat_count);
                            if(user_data[chat].chat_count == 0){
                                $badge.hide();
                            }else{
                                $badge.show();
                                if($el_chat.hasClass("active")){
                                    $messages = $(event.detail.data.messages);
                                    $messages.removeClass('ownmessage');
                                    $('#realmessenger-messages').append($messages);
                                    var d = $('#realmessenger-messages');
                                    d.scrollTop(d.prop("scrollHeight"));
                                }
                            }
                        }
                    }
                }
            });
        },
    };
    RealMessenger.Autocomplect = {
        callbacks: {
            load: RealMessengerConfig.callbacksObjectTemplate(),
            find_or_new_chat: RealMessengerConfig.callbacksObjectTemplate(),
        },
        
        initialize: function () {
            RealMessenger.$doc
                .on('click', 'body', function (e) {
                    $(this).find('.realmessenger-autocomplect-menu').hide();
                });
            //realmessenger-autocomplect-all
            RealMessenger.$doc
                .on('click', '.realmessenger-autocomplect-all', function (e) {
                    e.preventDefault();
                    $autocomplect = $(this).closest('.realmessenger-autocomplect');
                    $menu = $autocomplect.find('.realmessenger-autocomplect-menu');
                    if($menu.is(':visible')){
                        $menu.hide();
                        return;
                    }
                    hash = $(this).closest('#realmesseger').data('hash');
                    search_goal = $autocomplect.data('search_goal');
                    RealMessenger.sendData.$autocomplect = $autocomplect;
                    RealMessenger.sendData.data = {
                        hash: hash,
                        search_goal: search_goal,
                        action: 'autocomplect_search_contact',
                        query: '',
                    };
                    var callbacks = RealMessenger.Autocomplect.callbacks;
            
                    callbacks.load.response.success = function (response) {
                        $menu = RealMessenger.sendData.$autocomplect.find('.realmessenger-autocomplect-menu');
                        $menu.html(response.data.html).show();
                    };
                    RealMessenger.send(RealMessenger.sendData.data, RealMessenger.Autocomplect.callbacks.load, RealMessenger.Callbacks.Autocomplect.load);
                });
            RealMessenger.$doc
                .on('click', '.realmessenger-autocomplect-menu li a', function (e) {
                    e.preventDefault();
                    //add chat
                    new_chat_user_id = $(this).data('id');
                    hash = $(this).closest('#realmesseger').data('hash');

                    $autocomplect = $(this).closest('.realmessenger-autocomplect');
                    RealMessenger.sendData.$autocomplect = $autocomplect;
                    RealMessenger.sendData.data = {
                        hash: hash,
                        action: 'find_or_new_chat',
                        new_chat_user_id: new_chat_user_id,
                    };
                    var callbacks = RealMessenger.Autocomplect.callbacks;
            
                    callbacks.find_or_new_chat.response.success = function (response) {
                        $menu = RealMessenger.sendData.$autocomplect.find('.realmessenger-autocomplect-menu');
                        $menu.hide();

                        $(".realmessenger-chat").removeClass('active');
                        $('.realmessenger-chat[data-id=' + response.data.active_chat + ']').remove();
                        $(".realmessenger-chats").prepend(response.data.chat);
                        $('#realmessenger-messages-wrapper').replaceWith(response.data.messages);
                        $('#realmessenger-message-form input[name=chat]').val(response.data.active_chat);
                        $('#realmessenger-message-form-wrapper').show();
                        $('.realmessenger-chats-empty').hide();
                    };
                    RealMessenger.send(RealMessenger.sendData.data, RealMessenger.Autocomplect.callbacks.find_or_new_chat, RealMessenger.Callbacks.Autocomplect.find_or_new_chat);
                
                });
            RealMessenger.$doc
                .on('keyup', '.realmessenger-autocomplect-content', function (e) {
                    e.preventDefault();
                    $autocomplect = $(this).closest('.realmessenger-autocomplect');

                    hash = $(this).closest('#realmesseger').data('hash');
                    search_goal = $autocomplect.data('search_goal');
                    RealMessenger.sendData.$autocomplect = $autocomplect;
                    RealMessenger.sendData.data = {
                        action: 'autocomplect_search_contact',
                        search_goal: search_goal,
                        hash: hash,
                        query: $(this).val(),
                    };
                    var callbacks = RealMessenger.Autocomplect.callbacks;
            
                    callbacks.load.response.success = function (response) {
                        $menu = RealMessenger.sendData.$autocomplect.find('.realmessenger-autocomplect-menu');
                        $menu.html(response.data.html).show();
                    };
                    RealMessenger.send(RealMessenger.sendData.data, RealMessenger.Autocomplect.callbacks.load, RealMessenger.Callbacks.Autocomplect.load);
                });
        },
    };
    $(document).ready(function ($) {
        RealMessenger.initialize();
    });
})(window, document, jQuery, RealMessengerConfig);