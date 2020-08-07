<?php

class RealMessengerItemGetProcessor extends modObjectGetProcessor
{
    public $objectType = 'RealMessengerItem';
    public $classKey = 'RealMessengerItem';
    public $languageTopics = ['realmessenger:manager'];
    //public $permission = 'view';


    /**
     * We doing special check of permission
     * because of our objects is not an instances of modAccessibleObject
     *
     * @return mixed
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        return parent::process();
    }

}

return 'RealMessengerItemGetProcessor';