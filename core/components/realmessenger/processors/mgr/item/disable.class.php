<?php
include_once dirname(__FILE__) . '/update.class.php';
class RealMessengerItemDisableProcessor extends RealMessengerItemUpdateProcessor
{
    public function beforeSet()
    {
        $this->setProperty('active', false);
        return true;
    }
}
return 'RealMessengerItemDisableProcessor';
