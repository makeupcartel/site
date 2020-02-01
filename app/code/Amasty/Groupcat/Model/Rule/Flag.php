<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

/**
 * Flag stores status about availability not applied Groupcat rules
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Amasty\Groupcat\Model\Rule;

class Flag extends \Magento\Framework\Flag
{
    /**
     * Flag code
     *
     * @var string
     */
    protected $_flagCode = 'groupcat_rules_dirty';
}
