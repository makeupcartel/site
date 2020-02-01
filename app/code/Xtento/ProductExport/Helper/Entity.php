<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-09-09T12:16:06+00:00
 * File:          app/code/Xtento/ProductExport/Helper/Entity.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Helper;

use Magento\Framework\Exception\LocalizedException;
use Xtento\ProductExport\Model\Export;

class Entity extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);
        $this->objectManager = $objectManager;
    }

    public function getPluralEntityName($entity)
    {
        if ($entity == Export::ENTITY_PRODUCT) {
            return __("products");
        }
        if ($entity == Export::ENTITY_REVIEW) {
            return __("product reviews");
        }
        if ($entity == Export::ENTITY_CATEGORY) {
            return __("categories");
        }
        return $entity;
    }

    public function getEntityName($entity)
    {
        if ($entity == Export::ENTITY_PRODUCT) {
            return __("Product");
        }
        if ($entity == Export::ENTITY_PRODUCT) {
            return __("Product Review");
        }
        if ($entity == Export::ENTITY_CATEGORY) {
            return __("Category");
        }
        return ucwords($entity);
    }

    public function getExportEntity($entity)
    {
        if ($entity == Export::ENTITY_PRODUCT) {
            return '\Magento\Catalog\Model\Product';
        }
        if ($entity == Export::ENTITY_REVIEW) {
            return '\Magento\Review\Model\Review';
        }
        if ($entity == Export::ENTITY_CATEGORY) {
            return '\Magento\Catalog\Model\Category';
        }
        throw new LocalizedException(__('Could not find export entity "%1"', $entity));
    }

    public function getLastEntityId($entity)
    {
        $collection = $this->objectManager->create($this->getExportEntity($entity))->getCollection();
        if ($entity == \Xtento\ProductExport\Model\Export::ENTITY_CATEGORY || $entity == \Xtento\ProductExport\Model\Export::ENTITY_PRODUCT) {
            $collection->addFieldToSelect('entity_id');
            $collection->getSelect()->limit(1)->order('entity_id DESC');
        }
        if ($entity == \Xtento\ProductExport\Model\Export::ENTITY_REVIEW) {
            $collection->getSelect()->limit(1)->order('main_table.review_id DESC');
        }
        $object = $collection->getFirstItem();
        return $object->getId();
    }
}
