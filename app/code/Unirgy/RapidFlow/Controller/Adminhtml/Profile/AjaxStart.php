<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;

use Zend\Json\Json;

class AjaxStart extends AbstractProfile
{
    public function execute()
    {
        try {
            $profile = $this->_getProfile();
            switch ($profile->getRunStatus()) {
                case 'pending':
                    $profile->start()->save()->run();
                    $result = ['success' => true];
                    break;
                case 'running':
                    $result = ['warning' => __('The profile is already running')];
                    break;
                default:
                    $result = ['error' => __('Invalid profile run status')];
            }
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage()];
        }

        $this->getResponse()->representJson(Json::encode($result));
    }
}
