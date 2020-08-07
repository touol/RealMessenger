<?php
class RealMessengerItemRemoveProcessor extends modObjectRemoveProcessor
{
    public $objectType = 'RealMessengerItem';
    public $classKey = 'RealMessengerItem';
    public $languageTopics = ['realmessenger:manager'];
    #public $permission = 'remove';

    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        if (!$this->modx->hasPermission($this->permission)) {
            return $this->modx->lexicon('access_denied');
        }
        return parent::initialize();
    }
}

return 'RealMessengerItemRemoveProcessor';