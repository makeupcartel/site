<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-04-17T12:46:52+00:00
 * File:          app/code/Xtento/ProductExport/Model/ResourceModel/History/Collection.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\ResourceModel\History;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->request = $request;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    protected function _construct()
    {
        $this->_init('Xtento\ProductExport\Model\History', 'Xtento\ProductExport\Model\ResourceModel\History');
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        if ($this->request->getControllerName() == 'history' ||
            ($this->request->getControllerName() == 'profile' && $this->request->getActionName() == 'history')
        ) {
            $this->getSelect()->joinLeft(
                ['profile' => $this->getTable('xtento_productexport_profile')],
                'main_table.profile_id = profile.profile_id',
                ['concat(profile.name," (ID: ", profile.profile_id,")") as profile', 'profile.entity', 'profile.name']
            );

            if ($this->request->getParam('id', false)) {
                $this->addFieldToFilter('main_table.profile_id', intval($this->request->getParam('id')));
            }
        }

        return $this;
    }
}