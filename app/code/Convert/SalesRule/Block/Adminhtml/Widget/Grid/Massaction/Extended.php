<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Convert\SalesRule\Block\Adminhtml\Widget\Grid\Massaction;

/**
 * Grid widget massaction block
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @method \Magento\Quote\Model\Quote setHideFormElement(boolean $value) Hide Form element to prevent IE errors
 * @method boolean getHideFormElement()
 * @author      Magento Core Team <core@magentocommerce.com>
 * @TODO MAGETWO-31510: Remove deprecated class
 * @since 100.0.2
 */
class Extended extends \Magento\Backend\Block\Widget\Grid\Massaction\Extended
{
    /**
     * @return string
     */
    public function getGridIdsJson()
    {
        if (!$this->getUseSelectAll()) {
            return '';
        }

        /** @var \Magento\Framework\Data\Collection $allIdsCollection */
        $allIdsCollection = clone $this->getParentBlock()->getCollection();

        if ($this->getMassactionIdField()) {
            $massActionIdField = $this->getMassactionIdField();
        } else {
            $massActionIdField = $this->getParentBlock()->getMassactionIdField();
        }
        $collection = clone $allIdsCollection->getSelect();
        $collection->reset(\Magento\Framework\DB\Select::ORDER);
        $collection->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $collection->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $collection->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->columns($massActionIdField, 'main_table');
        $gridIds = $allIdsCollection->getConnection()->fetchCol($collection);

        if (!empty($gridIds)) {
            return join(",", $gridIds);
        }
        return '';
    }
}
