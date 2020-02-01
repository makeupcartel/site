<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;



class Test extends AbstractProfile
{
    public function execute()
    {
        try {
            $profile = $this->_getProfile();
            try {
                $profile->stop();
            } catch (\Exception $e) {
            };
            $profile->start()->save()->run();
        } catch (\Exception $e) {
            var_dump($e);
        }
        exit;
    }
}
