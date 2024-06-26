<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Plugin\Quote\Model\QuoteRepository;

use Amasty\Promo\Model\Storage;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteRepository\SaveHandler;

/**
 * Set flag for avid quote double saves
 */
class SaveHandlerPlugin
{
    /**
     * @var Storage
     */
    private $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param SaveHandler $subject
     * @param CartInterface $quote
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        SaveHandler $subject,
        CartInterface $quote
    ) {
        $this->storage->restrictQuoteSaving();
    }

    /**
     * @param SaveHandler $subject
     * @param CartInterface $result
     *
     * @return CartInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(SaveHandler $subject, $result)
    {
        $this->storage->allowQuoteSaving();

        return $result;
    }
}
