<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Import\Validator;

use Magento\Framework\Validator\AbstractValidator;

abstract class AbstractImportValidator extends AbstractValidator implements RowValidatorInterface
{
    protected $context;

    public function init($context)
    {
        $this->context = $context;
        return $this;
    }
}
