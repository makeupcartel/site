<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Validator;

use BitExpert\ForceCustomerLogin\Model\WhitelistEntry as WhitelistEntryModel;

/**
 * Class WhitelistEntry
 *
 * @package BitExpert\ForceCustomerLogin\Validator
 */
class WhitelistEntry
{
    /**
     * @param WhitelistEntryModel $whitelistEntry
     * @return bool
     * @throw \InvalidArgumentException If validation fails
     */
    public function validate(
        WhitelistEntryModel $whitelistEntry
    ) {
        if (0 === \strlen(\trim($whitelistEntry->getLabel()))) {
            throw new \InvalidArgumentException('Label value is too short.');
        }
        if (255 < \strlen(\trim($whitelistEntry->getLabel()))) {
            throw new \InvalidArgumentException('Label value is too long.');
        }

        if (0 === \strlen(\trim($whitelistEntry->getUrlRule()))) {
            throw new \InvalidArgumentException('Url Rule value is too short.');
        }
        if (255 < \strlen(\trim($whitelistEntry->getUrlRule()))) {
            throw new \InvalidArgumentException('Url Rule value is too long.');
        }

        if (0 === \strlen(\trim($whitelistEntry->getStrategy()))) {
            throw new \InvalidArgumentException('Strategy value is too short.');
        }
        if (255 < \strlen(\trim($whitelistEntry->getStrategy()))) {
            throw new \InvalidArgumentException('Strategy value is too long.');
        }

        if (!\is_bool($whitelistEntry->getEditable())) {
            throw new \InvalidArgumentException('Editable is no boolean value.');
        }

        return true;
    }
}
