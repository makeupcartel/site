<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-09-09T09:53:31+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Entity/Collection/Item.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Entity\Collection;

class Item extends \Magento\Framework\DataObject
{
    public $collectionItem;
    public $collectionSize;
    public $currItemNo;

    public function __construct($collectionItem, $entityType, $currItemNo, $collectionCount)
    {
        parent::__construct();
        $this->collectionItem = $collectionItem;
        $this->collectionSize = $collectionCount;
        $this->currItemNo = $currItemNo;
        if ($entityType == \Xtento\ProductExport\Model\Export::ENTITY_PRODUCT) {
            $this->setProduct($collectionItem);
        }
        if ($entityType == \Xtento\ProductExport\Model\Export::ENTITY_REVIEW) {
            $this->setReview($collectionItem);
        }
        if ($entityType == \Xtento\ProductExport\Model\Export::ENTITY_CATEGORY) {
            $this->setCategory($collectionItem);
        }
    }

    public function getObject()
    {
        return $this->collectionItem;
    }
}