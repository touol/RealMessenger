<?php
$xpdo_meta_map['RealMessengerRewiev']= array (
  'package' => 'realmessenger',
  'version' => '1.1',
  'table' => 'realmessenger_rewievs',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'leed_id' => 0,
    'question' => '',
    'question_autor' => '',
    'question_date' => NULL,
    'answer' => '',
    'answer_autor' => '',
    'answer_date' => NULL,
    'active' => 1,
  ),
  'fieldMeta' => 
  array (
    'leed_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'question' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'question_autor' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '100',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'question_date' => 
    array (
      'dbtype' => 'date',
      'phptype' => 'date',
      'null' => true,
      'title' => 'Дата',
    ),
    'answer' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'answer_autor' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '100',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'answer_date' => 
    array (
      'dbtype' => 'date',
      'phptype' => 'date',
      'null' => true,
      'title' => 'Дата',
    ),
    'active' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => true,
      'default' => 1,
    ),
  ),
  'indexes' => 
  array (
    'leed_id' => 
    array (
      'alias' => 'leed_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'leed_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'active' => 
    array (
      'alias' => 'active',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'active' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
);
