<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-04-17T13:01:31+00:00
 * File:          app/code/Xtento/ProductExport/Observer/AbstractEventObserver.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Observer;

use Xtento\ProductExport\Model\Export;

class AbstractEventObserver extends \Xtento\ProductExport\Model\AbstractAutomaticExport
{
    protected $events = [];
    public static $exportedIds = [];

    // Magento default events
    const EVENT_CATALOG_PRODUCT_SAVE_AFTER = 1;
    const EVENT_CATALOG_CATEGORY_SAVE_AFTER = 2;

    // Third party events
    // None at this time.

    /**
     * Get export events
     * 
     * @param bool|false $entity
     * @param bool|false $allEvents
     * @return array
     */
    public function getEvents($entity = false, $allEvents = false)
    {
        $events = [];

        // Events where product information can be exported
        if ($allEvents || $entity == Export::ENTITY_PRODUCT) {
            $events[Export::ENTITY_PRODUCT][self::EVENT_CATALOG_PRODUCT_SAVE_AFTER] = [
                'event' => 'catalog_product_save_after',
                'label' => __('After product gets saved (Event: catalog_product_save_after)'),
                'method' => 'getProduct()',
                'force_collection_item' => true
            ];
        }
        // Events where category information can be exported
        if ($allEvents || $entity == Export::ENTITY_CATEGORY) {
            $events[Export::ENTITY_CATEGORY][self::EVENT_CATALOG_CATEGORY_SAVE_AFTER] = [
                'event' => 'catalog_category_save_after',
                'label' => __('After category gets saved (Event: catalog_category_save_after)'),
                'method' => 'getCategory()',
                'force_collection_item' => true
            ];
        }

        // Third party events
        // None at this time

        return $events;
    }

    /*
     *  Third party events
     */
    // None at this time

    /*
     * Code handling events
     */
    protected function handleEvent(\Magento\Framework\Event\Observer $observer, $eventId = 0, $entity)
    {
        try {
            if (!$this->moduleHelper->isModuleEnabled() || !$this->moduleHelper->isModuleProperlyInstalled()) {
                return;
            }
            $event = $observer->getEvent();

            // Load profiles which are listening for this event
            $profileCollection = $this->profileCollectionFactory->create()
                ->addFieldToFilter('enabled', 1) // Profile enabled
                ->addFieldToFilter('entity', $entity)
                ->addFieldToFilter('event_observers', ['like' => '%' . $eventId . '%']); // Event enabled "pre-check"
            foreach ($profileCollection as $profile) {
                $profileId = $profile->getId();
                $eventObservers = explode(",", $profile->getEventObservers());
                if (!in_array($eventId, $eventObservers)) {
                    continue; // Not enabled for this event
                }
                if (!isset(self::$exportedIds[$profileId])) {
                    self::$exportedIds[$profileId] = [];
                    // Note: $exportedIds checking whether item has been exported seems to be broken. getId() for events in M2 return "null", unlike M1.
                }
                $exportObject = $this->getExportObject($entity, $event, $eventId);
                if ($exportObject) {
                    if (!in_array($exportObject->getId(), self::$exportedIds[$profileId])) {
                        $exportModel = $this->exportFactory->create()->setProfile($profile);
                        if (isset($this->events[$entity][$eventId]['force_collection_item']) && $this->events[$entity][$eventId]['force_collection_item'] === true) {
                            $filters = $this->addProfileFilters($profile);
                            if ($exportModel->eventExport($filters, $exportObject)) {
                                // Has been exported in this execution.. do not export again in the same execution.
                                if ($exportObject->getId()) {
                                    array_push(self::$exportedIds[$profileId], $exportObject->getId());
                                }
                                $this->_registry->registry('productexport_log')->setExportEvent(
                                    $this->events[$entity][$eventId]['event']
                                )->save();
                            }
                        } else {
                            if ($exportObject->getId()) {
                                $filters = [['entity_id' => $exportObject->getId()]];
                                $filters = array_merge($filters, $this->addProfileFilters($profile));
                                if ($exportModel->eventExport($filters)) {
                                    // Has been exported in this execution.. do not export again in the same execution.
                                    array_push(self::$exportedIds[$profileId], $exportObject->getId());
                                    $this->_registry->registry('productexport_log')->setExportEvent(
                                        $this->events[$entity][$eventId]['event']
                                    )->save();
                                }
                            }
                        }
                    }
                } else {
                    $this->xtentoLogger->warning('Event handler for event '.$eventId.': Could not find export object.');
                }
            }
        } catch (\Exception $e) {
            #echo $e->getTraceAsString(); die();
            $this->xtentoLogger->warning('Event handler exception for event '.$eventId.': '.$e->getMessage());
            return;
        }
    }

    protected function getExportObject($entity, $event, $eventId)
    {
        if (empty($this->events)) {
            $this->events = $this->getEvents(false, true);
        }
        if (isset($this->events[$entity][$eventId]) && isset($this->events[$entity][$eventId]['method'])) {
            $eventMethods = explode("->", str_replace('()', '', $this->events[$entity][$eventId]['method']));
            if (count($eventMethods) == 1) {
                return $event->{$eventMethods[0]}();
            } else if (count($eventMethods) == 2) {
                return $event->{$eventMethods[0]}()->{$eventMethods[1]}();
            }
        }
        return false;
    }
}
