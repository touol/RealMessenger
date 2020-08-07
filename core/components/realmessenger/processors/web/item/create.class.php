<?php

class RealMessengerOfficeItemCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'RealMessengerItem';
    public $classKey = 'RealMessengerItem';
    public $languageTopics = ['realmessenger'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('realmessenger_item_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('realmessenger_item_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'RealMessengerOfficeItemCreateProcessor';