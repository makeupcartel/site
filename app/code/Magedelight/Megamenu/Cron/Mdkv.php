<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Megamenu
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Megamenu\Cron;

class Mdkv
{
    protected $helper;

    public function __construct(
        \Magedelight\Megamenu\Helper\Mddata $helper
    ) {
        $this->helper = $helper;
    }

    public function execute()
    {
        $mappedDomains = $this->helper->getAllowedDomainsCollection();
    }
}
