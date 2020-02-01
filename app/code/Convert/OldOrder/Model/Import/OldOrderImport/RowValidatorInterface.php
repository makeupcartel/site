<?php
namespace Convert\OldOrder\Model\Import\OldOrderImport;
interface RowValidatorInterface extends \Magento\Framework\Validator\ValidatorInterface
{
       const ERROR_INVALID_TITLE= 'InvalidValueTITLE';
       const ERROR_MESSAGE_IS_EMPTY = 'EmptyOldOrder';
    /**
     * Initialize validator
     *
     * @return $this
     */
    public function init($context);
}