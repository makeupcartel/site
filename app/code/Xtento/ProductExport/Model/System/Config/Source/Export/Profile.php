<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-04-20T14:02:54+00:00
 * File:          app/code/Xtento/ProductExport/Model/System/Config/Source/Export/Profile.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\System\Config\Source\Export;

class Profile
{
    /**
     * @var \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory
     */
    protected $profileCollectionFactory;

    /**
     * @param \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     */
    public function __construct(
        \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
    ) {
        $this->profileCollectionFactory = $profileCollectionFactory;
    }

    /**
     * @param bool $all
     * @param bool $entity
     *
     * @return array
     */
    public function toOptionArray($all = false, $entity = false)
    {
        $profileCollection = $this->profileCollectionFactory->create();
        if (!$all) {
            $profileCollection->addFieldToFilter('enabled', 1);
            $profileCollection->addFieldToFilter('manual_export_enabled', 1);
        }
        if ($entity) {
            $profileCollection->addFieldToFilter('entity', $entity);
        }
        $profileCollection->getSelect()->order('entity ASC');
        $returnArray = [];
        foreach ($profileCollection as $profile) {
            $returnArray[] = [
                'profile' => $profile,
                'value' => $profile->getId(),
                'label' => $profile->getName(),
                'entity' => $profile->getEntity(),
            ];
        }
        if (empty($returnArray)) {
            $returnArray[] = [
                'profile' => new \Magento\Framework\DataObject(),
                'value' => '',
                'label' => __(
                    'No profiles available. Add and enable export profiles for the %1 entity first.',
                    $entity
                ),
                'entity' => '',
            ];
        }
        return $returnArray;
    }
}
