<?php

/**
 * The home manager controller for RealMessenger.
 *
 */
class RealMessengerHomeManagerController extends modExtraManagerController
{
    /** @var RealMessenger $RealMessenger */
    public $RealMessenger;


    /**
     *
     */
    public function initialize()
    {
        $this->RealMessenger = $this->modx->getService('RealMessenger', 'RealMessenger', MODX_CORE_PATH . 'components/realmessenger/model/');
        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['realmessenger:manager', 'realmessenger:default'];
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('realmessenger');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->RealMessenger->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->RealMessenger->config['jsUrl'] . 'mgr/realmessenger.js');
        $this->addJavascript($this->RealMessenger->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->RealMessenger->config['jsUrl'] . 'mgr/misc/combo.js');
        $this->addJavascript($this->RealMessenger->config['jsUrl'] . 'mgr/misc/default.grid.js');
        $this->addJavascript($this->RealMessenger->config['jsUrl'] . 'mgr/misc/default.window.js');
        $this->addJavascript($this->RealMessenger->config['jsUrl'] . 'mgr/widgets/items/grid.js');
        $this->addJavascript($this->RealMessenger->config['jsUrl'] . 'mgr/widgets/items/windows.js');
        $this->addJavascript($this->RealMessenger->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->RealMessenger->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addJavascript(MODX_MANAGER_URL . 'assets/modext/util/datetime.js');

        $this->RealMessenger->config['date_format'] = $this->modx->getOption('realmessenger_date_format', null, '%d.%m.%y <span class="gray">%H:%M</span>');
        $this->RealMessenger->config['help_buttons'] = ($buttons = $this->getButtons()) ? $buttons : '';

        $this->addHtml('<script type="text/javascript">
        RealMessenger.config = ' . json_encode($this->RealMessenger->config) . ';
        RealMessenger.config.connector_url = "' . $this->RealMessenger->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "realmessenger-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .=  '<div id="realmessenger-panel-home-div"></div>';
        return '';
    }

    /**
     * @return string
     */
    public function getButtons()
    {
        $buttons = null;
        $name = 'RealMessenger';
        $path = "Extras/{$name}/_build/build.php";
        if (file_exists(MODX_BASE_PATH . $path)) {
            $site_url = $this->modx->getOption('site_url').$path;
            $buttons[] = [
                'url' => $site_url,
                'text' => $this->modx->lexicon('realmessenger_button_install'),
            ];
            $buttons[] = [
                'url' => $site_url.'?download=1&encryption_disabled=1',
                'text' => $this->modx->lexicon('realmessenger_button_download'),
            ];
            $buttons[] = [
                'url' => $site_url.'?download=1',
                'text' => $this->modx->lexicon('realmessenger_button_download_encryption'),
            ];
        }
        return $buttons;
    }
}