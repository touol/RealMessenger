RealMessenger.panel.Home = function (config) {
    config = config || {}
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        layout: 'anchor',

        hideMode: 'offsets',
        items: [{
            html: '<h2>' + _('realmessenger') + '</h2>',
            cls: '',
            style: {margin: '15px 0'}
        }, {
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            border: true,
            hideMode: 'offsets',
            stateful: true,
            stateId: 'realmessenger-panel-home',
            stateEvents: ['tabchange'],
            getState: function () {return {activeTab: this.items.indexOf(this.getActiveTab())}},
            items: [{
                title: _('realmessenger_items'),
                layout: 'anchor',
                items: [{
                    html: _('realmessenger_intro_msg'),
                    cls: 'panel-desc',
                }, {
                    xtype: 'realmessenger-grid-items',
                    cls: 'main-wrapper',
                }]
            }]
        }]
    })
    RealMessenger.panel.Home.superclass.constructor.call(this, config)
}
Ext.extend(RealMessenger.panel.Home, MODx.Panel)
Ext.reg('realmessenger-panel-home', RealMessenger.panel.Home)

Ext.onReady(function () {
    if (RealMessenger.config.help_buttons.length > 0) {
        RealMessenger.buttons.help = function (config) {
            config = config || {}
            for (var i = 0; i < RealMessenger.config.help_buttons.length; i++) {
                if (!RealMessenger.config.help_buttons.hasOwnProperty(i)) {
                    continue
                }
                RealMessenger.config.help_buttons[i]['handler'] = this.loadPaneURl
            }
            Ext.applyIf(config, {
                buttons: RealMessenger.config.help_buttons
            })
            RealMessenger.buttons.help.superclass.constructor.call(this, config)
        }
        Ext.extend(RealMessenger.buttons.help, MODx.toolbar.ActionButtons, {
            loadPaneURl: function (b) {
                var url = b.url;
                var text = b.text;
                if (!url || !url.length) { return false }
                if (url.substring(0, 4) !== 'http') {
                    url = MODx.config.base_help_url + url
                }
                MODx.helpWindow = new Ext.Window({
                    title: text
                    , width: 850
                    , height: 350
                    , resizable: true
                    , maximizable: true
                    , modal: false
                    , layout: 'fit'
                    , bodyStyle: 'padding: 0;'
                    , items: [{
                        xtype: 'container',
                        layout: {
                            type: 'vbox',
                            align: 'stretch'
                        },
                        width: '100%',
                        height: '100%',
                        items: [{
                            autoEl: {
                                tag: 'iframe',
                                src: url,
                                width: '100%',
                                height: '100%',
                                frameBorder: 0
                            }
                        }]
                    }]
                    //,html: '<iframe src="' + url + '" width="100%" height="100%" frameborder="0"></iframe>'
                })
                MODx.helpWindow.show(b)
                return true
            }
        })

        Ext.reg('realmessenger-buttons-help', RealMessenger.buttons.help)
        MODx.add('realmessenger-buttons-help')
    }
})
