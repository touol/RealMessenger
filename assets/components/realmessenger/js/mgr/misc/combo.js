RealMessenger.combo.Search = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        xtype: 'twintrigger',
        ctCls: 'x-field-search',
        allowBlank: true,
        msgTarget: 'under',
        emptyText: _('search'),
        name: 'query',
        triggerAction: 'all',
        clearBtnCls: 'x-field-search-clear',
        searchBtnCls: 'x-field-search-go',
        onTrigger1Click: this._triggerSearch,
        onTrigger2Click: this._triggerClear,
    });
    RealMessenger.combo.Search.superclass.constructor.call(this, config);
    this.on('render', function () {
        this.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
            this._triggerSearch();
        }, this);
    });
    this.addEvents('clear', 'search');
};
Ext.extend(RealMessenger.combo.Search, Ext.form.TwinTriggerField, {

    initComponent: function () {
        Ext.form.TwinTriggerField.superclass.initComponent.call(this);
        this.triggerConfig = {
            tag: 'span',
            cls: 'x-field-search-btns',
            cn: [
                {tag: 'div', cls: 'x-form-trigger ' + this.searchBtnCls},
                {tag: 'div', cls: 'x-form-trigger ' + this.clearBtnCls}
            ]
        };
    },

    _triggerSearch: function () {
        this.fireEvent('search', this);
    },

    _triggerClear: function () {
        this.fireEvent('clear', this);
    },

});
Ext.reg('realmessenger-combo-search', RealMessenger.combo.Search);
Ext.reg('realmessenger-field-search', RealMessenger.combo.Search);

/**
 * Filter Active
 * @param config
 * @constructor
 */
RealMessenger.combo.Active = function(config) {
    config = config || {};

    if (config.custm) {
        config.triggerConfig = [{
            tag: 'div',
            cls: 'x-field-search-btns',
            style: String.format('width: {0}px;', config.clear ? 62 : 31),
            cn: [{
                tag: 'div',
                cls: 'x-form-trigger x-field-realmessenger-active-go'
            }]
        }];
        if (config.clear) {
            config.triggerConfig[0].cn.push({
                tag: 'div',
                cls: 'x-form-trigger x-field-realmessenger-active-clear'
            });
        }

        config.initTrigger = function() {
            var ts = this.trigger.select('.x-form-trigger', true);
            this.wrap.setStyle('overflow', 'hidden');
            var triggerField = this;
            ts.each(function(t, all, index) {
                t.hide = function() {
                    var w = triggerField.wrap.getWidth();
                    this.dom.style.display = 'none';
                    triggerField.el.setWidth(w - triggerField.trigger.getWidth());
                };
                t.show = function() {
                    var w = triggerField.wrap.getWidth();
                    this.dom.style.display = '';
                    triggerField.el.setWidth(w - triggerField.trigger.getWidth());
                };
                var triggerIndex = 'Trigger' + (index + 1);

                if (this['hide' + triggerIndex]) {
                    t.dom.style.display = 'none';
                }
                t.on('click', this['on' + triggerIndex + 'Click'], this, {
                    preventDefault: true
                });
                t.addClassOnOver('x-form-trigger-over');
                t.addClassOnClick('x-form-trigger-click');
            }, this);
            this.triggers = ts.elements;
        };
    }
    Ext.applyIf(config, {
        name: config.name || 'active',
        hiddenName: config.name || 'active',
        displayField: 'name',
        valueField: 'value',
        editable: true,
        fields: ['name', 'value'],
        pageSize: 10,
        emptyText: _('realmessenger_combo_select'),
        hideMode: 'offsets',
        url: RealMessenger.config.connector_url,
        baseParams: {
            action: 'mgr/misc/active/getlist',
            combo: true,
            addall: config.addall || 0
        },
        tpl: new Ext.XTemplate(
            '<tpl for="."><div class="x-combo-list-item">',
            '<small>({value})</small> <b>{name}</b></span>',
            '</div></tpl>', {
                compiled: true
            }),
        cls: 'input-combo-realmessenger-active',
        clearValue: function() {
            if (this.hiddenField) {
                this.hiddenField.value = '';
            }
            this.setRawValue('');
            this.lastSelectionText = '';
            this.applyEmptyText();
            this.value = '';
            this.fireEvent('select', this, null, 0);
        },

        getTrigger: function(index) {
            return this.triggers[index];
        },

        onTrigger1Click: function() {
            this.onTriggerClick();
        },

        onTrigger2Click: function() {
            this.clearValue();
        }
    });
    RealMessenger.combo.Active.superclass.constructor.call(this, config);

};
Ext.extend(RealMessenger.combo.Active, MODx.combo.ComboBox);
Ext.reg('realmessenger-combo-filter-active', RealMessenger.combo.Active);

/**
 * Filter Resource
 * @param config
 * @constructor
 */
RealMessenger.combo.Resource = function(config) {
    config = config || {};

    if (config.custm) {
        config.triggerConfig = [{
            tag: 'div',
            cls: 'x-field-search-btns',
            style: String.format('width: {0}px;', config.clear?62:31),
            cn: [{
                tag: 'div',
                cls: 'x-form-trigger x-field-realmessenger-resource-go'
            }]
        }];
        if (config.clear) {
            config.triggerConfig[0].cn.push({
                tag: 'div',
                cls: 'x-form-trigger x-field-realmessenger-resource-clear'
            });
        }

        config.initTrigger = function() {
            var ts = this.trigger.select('.x-form-trigger', true);
            this.wrap.setStyle('overflow', 'hidden');
            var triggerField = this;
            ts.each(function(t, all, index) {
                t.hide = function() {
                    var w = triggerField.wrap.getWidth();
                    this.dom.style.display = 'none';
                    triggerField.el.setWidth(w - triggerField.trigger.getWidth());
                };
                t.show = function() {
                    var w = triggerField.wrap.getWidth();
                    this.dom.style.display = '';
                    triggerField.el.setWidth(w - triggerField.trigger.getWidth());
                };
                var triggerIndex = 'Trigger' + (index + 1);

                if (this['hide' + triggerIndex]) {
                    t.dom.style.display = 'none';
                }
                t.on('click', this['on' + triggerIndex + 'Click'], this, {
                    preventDefault: true
                });
                t.addClassOnOver('x-form-trigger-over');
                t.addClassOnClick('x-form-trigger-click');
            }, this);
            this.triggers = ts.elements;
        };
    }
    Ext.applyIf(config, {
        name: config.name || 'resource',
        hiddenName: config.name || 'resource',
        displayField: 'pagetitle',
        valueField: 'id',
        editable: true,
        fields: ['pagetitle', 'id'],
        pageSize: 10,
        emptyText: _('realmessenger_combo_select'),
        hideMode: 'offsets',
        url: RealMessenger.config.connector_url,
        baseParams: {
            action: 'mgr/misc/resource/getlist',
            client_status:1,
            combo: true
        },
        tpl: new Ext.XTemplate(
            '<tpl for="."><div class="x-combo-list-item">',
            '<small>({id})</small> <b>{pagetitle}</b>',
            '</div></tpl>',
            {
                compiled: true
            }),
        cls: 'input-combo-realmessenger-resource',
        clearValue: function() {
            if (this.hiddenField) {
                this.hiddenField.value = '';
            }
            this.setRawValue('');
            this.lastSelectionText = '';
            this.applyEmptyText();
            this.value = '';
            this.fireEvent('select', this, null, 0);
        },

        getTrigger: function(index) {
            return this.triggers[index];
        },

        onTrigger1Click: function() {
            this.onTriggerClick();
        },

        onTrigger2Click: function() {
            this.clearValue();
        }
    });
    RealMessenger.combo.Resource.superclass.constructor.call(this, config);

};
Ext.extend(RealMessenger.combo.Resource, MODx.combo.ComboBox);
Ext.reg('realmessenger-combo-filter-resource', RealMessenger.combo.Resource);

/**
 * Filed Resource
 * @param config
 * @constructor
 */
RealMessenger.combo.Resource = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'resource',
        hiddenName: 'resource',
        displayField: 'pagetitle',
        valueField: 'id',
        editable: true,
        fields: ['id', 'pagetitle'],
        pageSize: 20,
        emptyText: _('realmessenger_combo_select'),
        hideMode: 'offsets',
        url: RealMessenger.config['connector_url'],
        baseParams: {
            action: 'mgr/system/element/resource/getlist',
            combo: true
        }
    });
    RealMessenger.combo.Resource.superclass.constructor.call(this, config);
};
Ext.extend(RealMessenger.combo.Resource, MODx.combo.ComboBox);
Ext.reg('realmessenger-combo-resource', RealMessenger.combo.Resource);

/**
 * Filed Context
 * @param config
 * @constructor
 */
RealMessenger.combo.Context = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'context',
        hiddenName: 'context',
        displayField: 'name',
        valueField: 'key',
        editable: true,
        fields: ['key', 'name'],
        pageSize: 20,
        emptyText: _('realmessenger_combo_select'),
        hideMode: 'offsets',
        url: RealMessenger.config['connector_url'],
        baseParams: {
            action: 'mgr/system/element/context/getlist',
            combo: true
        }
    });
    RealMessenger.combo.Context.superclass.constructor.call(this, config);
};
Ext.extend(RealMessenger.combo.Context, MODx.combo.ComboBox);
Ext.reg('realmessenger-combo-context', RealMessenger.combo.Context);

/**
 * Filed Chunk
 * @param config
 * @constructor
 */
RealMessenger.combo.Chunk = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'chunk',
        hiddenName: config.name || 'chunk',
        displayField: 'name',
        valueField: 'id',
        editable: true,
        fields: ['id', 'name'],
        pageSize: 20,
        emptyText: _('realmessenger_combo_select'),
        hideMode: 'offsets',
        url: RealMessenger.config['connector_url'],
        baseParams: {
            action: 'mgr/system/element/chunk/getlist',
            mode: 'chunks'
        }
    });
    RealMessenger.combo.Chunk.superclass.constructor.call(this, config);
};
Ext.extend(RealMessenger.combo.Chunk, MODx.combo.ComboBox);
Ext.reg('realmessenger-combo-chunk', RealMessenger.combo.Chunk);

/**
 * Filed User
 * @param config
 * @constructor
 */
RealMessenger.combo.User = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'user',
        fieldLabel: config.name || 'createdby',
        hiddenName: config.name || 'createdby',
        displayField: 'fullname',
        valueField: 'id',
        anchor: '99%',
        fields: ['username', 'id', 'fullname'],
        pageSize: 20,
        typeAhead: false,
        editable: true,
        allowBlank: false,
        url: RealMessenger.config['connector_url'],
        baseParams: {
            action: 'mgr/system/user/getlist',
            combo: true,
        },
        tpl: new Ext.XTemplate('\
            <tpl for=".">\
                <div class="x-combo-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{username}</b>\
                        <tpl if="fullname && fullname != username"> - {fullname}</tpl>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    RealMessenger.combo.User.superclass.constructor.call(this, config);
};
Ext.extend(RealMessenger.combo.User, MODx.combo.ComboBox);
Ext.reg('realmessenger-combo-user', RealMessenger.combo.User);


RealMessenger.combo.DateTime = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        timePosition: 'right',
        allowBlank: true,
        hiddenFormat: 'Y-m-d H:i:s',
        dateFormat: MODx.config['manager_date_format'],
        timeFormat: MODx.config['manager_time_format'],
        dateWidth: 120,
        timeWidth: 120
    });
    RealMessenger.combo.DateTime.superclass.constructor.call(this, config);
};
Ext.extend(RealMessenger.combo.DateTime, Ext.ux.form.DateTime);
Ext.reg('realmessenger-xdatetime', RealMessenger.combo.DateTime);