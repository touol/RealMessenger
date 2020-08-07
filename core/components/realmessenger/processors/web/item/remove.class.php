<?php

class RealMessengerOfficeItemRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'RealMessengerItem';
    public $classKey = 'RealMessengerItem';
    public $languageTopics = ['realmessenger'];
    //public $permission = 'remove';


    /**
     * @return array|string
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        $ids = $this->modx->fromJSON($this->getProperty('ids'));
        if (empty($ids)) {
            return $this->failure($this->modx->lexicon('realmessenger_item_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var RealMessengerItem $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('realmessenger_item_err_nf'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'RealMessengerOfficeItemRemoveProcessor';