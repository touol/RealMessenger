RealMessenger.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'realmessenger-panel-home',
            renderTo: 'realmessenger-panel-home-div'
        }]
    });
    RealMessenger.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(RealMessenger.page.Home, MODx.Component);
Ext.reg('realmessenger-page-home', RealMessenger.page.Home);