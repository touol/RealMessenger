RealMessenger.grid.Items = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'realmessenger-grid-items';
    }

    Ext.applyIf(config, {
        baseParams: {
            action: 'mgr/item/getlist',
            sort: 'id',
            dir: 'DESC'
        },
        stateful: true,
        stateId: config.id,
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: 0,
            getRowClass: function (rec) {
                return !rec.data.active
                  ? 'realmessenger-grid-row-disabled'
                  : '';
            }
        },
        paging: true,
        remoteSort: true,
        autoHeight: true,
    });
    RealMessenger.grid.Items.superclass.constructor.call(this, config);
};
Ext.extend(RealMessenger.grid.Items, RealMessenger.grid.Default, {

    getFields: function () {
        return [
            'id', 'name', 'description', 'active', 'actions'
        ];
    },

    getColumns: function () {
        return [
            {header: _('realmessenger_item_id'), dataIndex: 'id', width: 20, sortable: true},
            {header: _('realmessenger_item_name'), dataIndex: 'name', sortable: true, width: 200},
            {header: _('realmessenger_item_description'), dataIndex: 'description', sortable: false, width: 250},
            {header: _('realmessenger_item_createdon'), dataIndex: 'createdon', width: 75, renderer: RealMessenger.utils.formatDate},
            {header: _('realmessenger_item_updatedon'), dataIndex: 'updatedon', width: 75, renderer: RealMessenger.utils.formatDate},
            {header: _('realmessenger_item_active'), dataIndex: 'active', width: 75, renderer: RealMessenger.utils.renderBoolean},
            {
                header: _('realmessenger_grid_actions'),
                dataIndex: 'actions',
                id: 'actions',
                width: 50,
                renderer: RealMessenger.utils.renderActions
            }
        ];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('realmessenger_item_create'),
            handler: this.createItem,
            scope: this
        },{
            xtype: 'realmessenger-combo-filter-active',
            name: 'active',
            width: 210,
            custm: true,
            clear: true,
            addall: true,
            value: '',
            listeners: {
                select: {
                    fn: this._filterByCombo,
                    scope: this
                },
                afterrender: {
                    fn: this._filterByCombo,
                    scope: this
                }
            }
        },{
            xtype: 'realmessenger-combo-filter-resource',
            name: 'resource',
            width: 210,
            custm: true,
            clear: true,
            addall: true,
            value: '',
            listeners: {
                select: {
                    fn: this._filterByCombo,
                    scope: this
                },
                afterrender: {
                    fn: this._filterByCombo,
                    scope: this
                }
            }
        },
            '->', this.getSearchField()];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateItem(grid, e, row);
            },
        };
    },

    createItem: function (btn, e) {
        var w = MODx.load({
            xtype: 'realmessenger-item-window-create',
            id: Ext.id(),
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.reset();
        w.setValues({active: true});
        w.show(e.target);
    },

    updateItem: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        else if (!this.menu.record) {
            return false;
        }
        var id = this.menu.record.id;

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/item/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'realmessenger-item-window-update',
                            id: Ext.id(),
                            record: r,
                            listeners: {
                                success: {
                                    fn: function () {
                                        this.refresh();
                                    }, scope: this
                                }
                            }
                        });
                        w.reset();
                        w.setValues(r.object);
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },

    removeItem: function () {
        this.action('remove')
    },
    disableItem: function () {
        this.action('disable')
    },
    enableItem: function () {
        this.action('enable')
    },
});
Ext.reg('realmessenger-grid-items', RealMessenger.grid.Items);