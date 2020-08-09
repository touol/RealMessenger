<?php

return [
    'RealMessenger' => [
        'file' => 'realmessenger',
        'description' => 'RealMessenger snippet to list items',
        'properties' => [
            'ContactGroups' => [
                'type' => 'textfield',
                'value' => '2',
            ],
            'ContactGroupsPageIds' => [
                'type' => 'textfield',
                'value' => '',
            ],
            'OuterTpl' => [
                'type' => 'textfield',
                'value' => 'tpl.RealMessenger.outer',
            ],
            'ChatsTpl' => [
                'type' => 'textfield',
                'value' => 'tpl.RealMessenger.chats',
            ],
            'ChatTpl' => [
                'type' => 'textfield',
                'value' => 'tpl.RealMessenger.chat',
            ],
            'MessagesTpl' => [
                'type' => 'textfield',
                'value' => 'tpl.RealMessenger.messages',
            ],
            'MessageTpl' => [
                'type' => 'textfield',
                'value' => 'tpl.RealMessenger.message',
            ],
            'FormTpl' => [
                'type' => 'textfield',
                'value' => 'tpl.RealMessenger.form',
            ],
            'EmailTpl' => [
                'type' => 'textfield',
                'value' => 'tpl.RealMessenger.email',
            ],
            'SearchContactTpl' => [
                'type' => 'textfield',
                'value' => 'tpl.RealMessenger.search.contact',
            ],
            /*'sortby' => [
                'type' => 'textfield',
                'value' => 'name',
            ],
            'sortdir' => [
                'type' => 'list',
                'options' => [
                    ['text' => 'ASC', 'value' => 'ASC'],
                    ['text' => 'DESC', 'value' => 'DESC'],
                ],
                'value' => 'ASC',
            ],
            'limit' => [
                'type' => 'numberfield',
                'value' => 10,
            ],
            'outputSeparator' => [
                'type' => 'textfield',
                'value' => "\n",
            ],
            'toPlaceholder' => [
                'type' => 'combo-boolean',
                'value' => false,
            ],*/
        ],
    ],
];