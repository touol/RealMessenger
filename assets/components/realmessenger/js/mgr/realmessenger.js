var RealMessenger = function (config) {
    config = config || {};
    RealMessenger.superclass.constructor.call(this, config);
};
Ext.extend(RealMessenger, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}, buttons: {}
});
Ext.reg('realmessenger', RealMessenger);

RealMessenger = new RealMessenger();