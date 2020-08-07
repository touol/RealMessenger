<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/RealMessenger/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/realmessenger')) {
            $cache->deleteTree(
                $dev . 'assets/components/realmessenger/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/realmessenger/', $dev . 'assets/components/realmessenger');
        }
        if (!is_link($dev . 'core/components/realmessenger')) {
            $cache->deleteTree(
                $dev . 'core/components/realmessenger/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/realmessenger/', $dev . 'core/components/realmessenger');
        }
    }
}

return true;