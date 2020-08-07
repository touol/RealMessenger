<?php
include_once dirname(__FILE__) . '/update.class.php';
class RealMessengerItemEnableProcessor extends RealMessengerItemUpdateProcessor
{
    public function beforeSet()
    {
        $this->setProperty('active', true);
        return true;
    }
}
return 'RealMessengerItemEnableProcessor';