<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 26.02.13
 * Time: 22:42
 *
 */

namespace Unirgy\SimpleUp\Model\ResourceModel;

use \Magento\Framework\Module\Setup as ModuleSetup;


class Setup
    extends ModuleSetup
{
    public function reinstall()
    {
        return;
        // todo figure out if reinstall option is needed in M2, if so, find how to do it!
        $configVer = (string)$this->_moduleConfig->version;

        $this->_installResourceDb($configVer);
    }
}
