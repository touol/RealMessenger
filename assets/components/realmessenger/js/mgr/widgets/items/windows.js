RealMessenger.window.CreateItem = function (config) {
    config = config || {}
    config.url = RealMessenger.config.connector_url

    Ext.applyIf(config, {
        title: _('realmessenger_item_create'),
        width: 600,
        cls: 'realmessenger_windows',
        baseParams: {
            action: 'mgr/item/create',
            resource_id: config.resource_id
        }
    })
    RealMessenger.window.CreateItem.superclass.constructor.call(this, config)

    this.on('success', function (data) {
        if (data.a.result.object) {
            // Авто запуск при создании новой подписик
            if (data.a.result.object.mode) {
                if (data.a.result.object.mode === 'new') {
                    var grid = Ext.getCmp('realmessenger-grid-items')
                    grid.updateItem(grid, '', {data: data.a.result.object})
                }
            }
        }
    }, this)
}
Ext.extend(RealMessenger.window.CreateItem, RealMessenger.window.Default, {

    getFields: function (config) {
        return [
            {xtype: 'hidden', name: 'id', id: config.id + '-id'},
            {
                xtype: 'textfield',
                fieldLabel: _('realmessenger_item_name'),
                name: 'name',
                id: config.id + '-name',
                anchor: '99%',
                allowBlank: false,
            }, {
                xtype: 'textarea',
                fieldLabel: _('realmessenger_item_description'),
                name: 'description',
                id: config.id + '-description',
                height: 150,
                anchor: '99%'
            },  {
                xtype: 'realmessenger-combo-filter-resource',
                fieldLabel: _('realmessenger_item_resource_id'),
                name: 'resource_id',
                id: config.id + '-resource_id',
                height: 150,
                anchor: '99%'
            }, {
                xtype: 'xcheckbox',
                boxLabel: _('realmessenger_item_active'),
                name: 'active',
                id: config.id + '-active',
                checked: true,
            }
        ]


    }
})
Ext.reg('realmessenger-item-window-create', RealMessenger.window.CreateItem)

RealMessenger.window.UpdateItem = function (config) {
    config = config || {}

    Ext.applyIf(config, {
        title: _('realmessenger_item_update'),
        baseParams: {
            action: 'mgr/item/update',
            resource_id: config.resource_id
        },
    })
    RealMessenger.window.UpdateItem.superclass.constructor.call(this, config)
}
Ext.extend(RealMessenger.window.UpdateItem, RealMessenger.window.CreateItem)
Ext.reg('realmessenger-item-window-update', RealMessenger.window.UpdateItem)