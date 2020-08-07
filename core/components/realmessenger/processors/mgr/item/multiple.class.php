<?php

class RealMessengerMultipleProcessor extends modProcessor
{


    /**
     * @return array|string
     */
    public function process()
    {
        if (!$method = $this->getProperty('method', false)) {
            return $this->failure();
        }
        $ids = json_decode($this->getProperty('ids'), true);
        if (empty($ids)) {
            return $this->success();
        }

        /** @var RealMessenger $RealMessenger */
        $RealMessenger = $this->modx->getService('RealMessenger');
        foreach ($ids as $id) {
            /** @var modProcessorResponse $response */
            $response = $RealMessenger->runProcessor('mgr/item/' . $method, array('id' => $id), array(
                'processors_path' => MODX_CORE_PATH . 'components/realmessenger/processors/mgr/'
            ));
            if ($response->isError()) {
                return $response->getResponse();
            }
        }

        return $this->success();
    }


}

return 'RealMessengerMultipleProcessor';