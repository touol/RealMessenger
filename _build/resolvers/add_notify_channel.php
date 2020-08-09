<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            if ($modx instanceof modX) {
                if($gtsNotify = $modx->getService('gtsNotify', 'gtsNotify', MODX_CORE_PATH . 'components/gtsnotify/model/', [])){
                    if(!$channel = $modx->getObject('gtsNotifyChannel', array('name' => 'RealMessenger'))) {
                        if($channel = $modx->newObject('gtsNotifyChannel', [
                            'name' => 'RealMessenger',
                            'description' => 'channel for RealMessenger',
                            'icon' => 'glyphicon glyphicon-envelope',
                            'icon_empty' => 'glyphicon glyphicon-envelope',
                            'tpl' => 'tpl.RealMessenger.notify',
                            'email_send' => 1,
                            'email_tpl' => 'tpl.RealMessenger.email',
                            'email_sleep' => 300,
                            ])) {
                            if($channel->save()){
                                
                            }
                        }
                    }
                }
            }
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            if ($modx instanceof modX) {
                //$modx->removeExtensionPackage('realmessenger');
            }
            break;
    }
}
return true;