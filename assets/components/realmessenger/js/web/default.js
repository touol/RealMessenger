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
            remove: RealMessengerConfig.callbacksObjectTemplate(),
        },
        Autocomplect: {
            load: RealMessengerConfig.callbacksObjectTemplate(),
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
        
        //noinspection JSUnresolvedFunction
        /*RealMessenger.$doc
            .on('submit', RealMessenger.form, function (e) {
                e.preventDefault();
                
            });*/
            
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
            remove: RealMessengerConfig.callbacksObjectTemplate(),
        },
        
        initialize: function () {
            RealMessenger.$doc
                .on('click', 'body', function (e) {
                    $(this).find('.RealMessenger-Chat-menu').hide();
                });
            //realmessenger-autocomplect-all
            /*RealMessenger.$doc
                .on('click', '.RealMessenger-Chat-btn', function (e) {
                    e.preventDefault();
                    $Chat = $(this).closest('.RealMessenger-Chat');
                    $menu = $Chat.find('.RealMessenger-Chat-menu');
                    if($menu.is(':visible')){
                        $menu.hide();
                        return;
                    }
                    //RealMessenger.sendData.$GtsApp = $table;
                    RealMessenger.sendData.$Chat = $Chat;
                    RealMessenger.sendData.data = {
                        RealMessenger_action: 'load_Chat_notify',
                        name: $Chat.data('name'),
                    };
                    var callbacks = RealMessenger.Chat.callbacks;
            
                    callbacks.load.response.success = function (response) {
                        $menu = RealMessenger.sendData.$Chat.find('.RealMessenger-Chat-menu');
                        $menu.html(response.data.html).show();
                    };
                    RealMessenger.send(RealMessenger.sendData.data, RealMessenger.Chat.callbacks.load, RealMessenger.Callbacks.Chat.load);
                });
            RealMessenger.$doc
                .on('click', '.RealMessenger-Chat-notify-remove', function (e) {
                    e.preventDefault();
                    $Chat = $(this).closest('.RealMessenger-Chat');
                    $notify = $(this).closest('.RealMessenger-Chat-notify');
                    RealMessenger.sendData.$Chat = $Chat;
                    RealMessenger.sendData.data = {
                        RealMessenger_action: 'remove_Chat_notify',
                        name: $Chat.data('name'),
                        notify_id: $notify.data('id'),
                    };
                    var callbacks = RealMessenger.Chat.callbacks;
            
                    callbacks.remove.response.success = function (response) {
                        $notify = RealMessenger.sendData.$Chat.find('.RealMessenger-Chat-notify');
                        $notify.remove();
                        $badge = RealMessenger.sendData.$Chat.find('.RealMessenger-badge-notify');
                        $badge.text(response.data.count);
                        if(response.data.count == 0){
                            $badge.hide();
                        }else{
                            $badge.show();
                        }
                    };
                    RealMessenger.send(RealMessenger.sendData.data, RealMessenger.Chat.callbacks.remove, RealMessenger.Callbacks.Chat.remove);
                });*/
            
            document.addEventListener("RealMessengerprovider", function(event) { 
                console.log('notify',event.detail);
                /*for(var key in event.detail.Chats) {
                    $badge = $('.RealMessenger-Chat[data-name="' + key + '"]').find('.RealMessenger-badge-notify');
                    $badge.text(event.detail.Chats[key].count);
                    if(event.detail.Chats[key].count == 0){
                        $badge.hide();
                    }else{
                        $badge.show();
                    }
                }*/
            });
        },
    };
    RealMessenger.Autocomplect = {
        callbacks: {
            load: RealMessengerConfig.callbacksObjectTemplate(),
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
                    RealMessenger.sendData.$autocomplect = $autocomplect;
                    RealMessenger.sendData.data = {
                        hash: hash,
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
                    /*$autocomplect = $(this).closest('.realmessenger-autocomplect');
                    $autocomplect.find('.realmessenger-autocomplect-id').val($(this).data('id'));
                    $autocomplect.find('.realmessenger-autocomplect-hidden-id').val($(this).data('id')).trigger('change');
                    $autocomplect.find('.realmessenger-autocomplect-content').val($(this).text());
                    $autocomplect.find('.realmessenger-autocomplect-menu').hide();*/
                });
            RealMessenger.$doc
                .on('keyup', '.realmessenger-autocomplect-content', function (e) {
                    e.preventDefault();
                    $autocomplect = $(this).closest('.realmessenger-autocomplect');

                    hash = $(this).closest('#realmesseger').data('hash');
                    RealMessenger.sendData.$autocomplect = $autocomplect;
                    RealMessenger.sendData.data = {
                        action: 'autocomplect_search_contact',
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
            /*RealMessenger.$doc
                .on('change', '.realmessenger-autocomplect-content', function (e) {
                    e.preventDefault();
                    $autocomplect = $(this).closest('.realmessenger-autocomplect');
                    if($(this).val() == ""){
                        $autocomplect.find('.realmessenger-autocomplect-id').val(0);
                        $autocomplect.find('.realmessenger-autocomplect-hidden-id').val(0).trigger('change');
                    }
                });*/
        },
    };
    $(document).ready(function ($) {
        RealMessenger.initialize();
    });
})(window, document, jQuery, RealMessengerConfig);