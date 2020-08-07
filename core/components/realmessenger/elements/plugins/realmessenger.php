<?php
/** @var modX $modx */
/* @var array $scriptProperties */
switch ($modx->event->name) {
    case 'OnHandleRequest':
        /* @var RealMessenger $RealMessenger*/
        $RealMessenger = $modx->getService('realmessenger', 'RealMessenger', $modx->getOption('realmessenger_core_path', $scriptProperties, $modx->getOption('core_path') . 'components/realmessenger/') . 'model/');
        if ($RealMessenger instanceof RealMessenger) {
            $RealMessenger->loadHandlerEvent($modx->event, $scriptProperties);
        }
        break;
}
return '';