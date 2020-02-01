<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Convert\Sales\Model\Service;

use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Model\Product\Type;

/**
 * Class InvoiceService
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceService extends \Magento\Sales\Model\Service\InvoiceService
{
    /**
     * Serializer interface instance.
     *
     * @var Json
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $repository
     * @param \Magento\Sales\Api\InvoiceCommentRepositoryInterface $commentRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Sales\Model\Order\InvoiceNotifier $notifier
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Convert\Order $orderConverter
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Sales\Api\InvoiceRepositoryInterface $repository,
        \Magento\Sales\Api\InvoiceCommentRepositoryInterface $commentRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Sales\Model\Order\InvoiceNotifier $notifier,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Convert\Order $orderConverter,
        Json $serializer = null
    ) {
        parent::__construct(
            $repository,
            $commentRepository,
            $criteriaBuilder,
            $filterBuilder,
            $notifier,
            $orderRepository,
            $orderConverter,
            $serializer
        );
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Creates an invoice based on the order and quantities provided
     *
     * @param Order $order
     * @param array $qtys
     * @return \Magento\Sales\Model\Order\Invoice
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareInvoice(Order $order, array $qtys = [])
    {
        $isQtysEmpty = empty($qtys);
        $invoice = $this->orderConverter->toInvoice($order);
        $totalQty = 0;
        $qtys = $this->prepareItemsQty($order, $qtys);
        foreach ($order->getAllItems() as $orderItem) {
            $itemId = $orderItem->getId() ? : $orderItem->getQuoteItemId();
            if (!$this->_canInvoiceItem($orderItem, $qtys)) {
                continue;
            }
            $item = $this->orderConverter->itemToInvoiceItem($orderItem);
            if (isset($qtys[$itemId])) {
                $qty = (double) $qtys[$itemId];
            } elseif ($orderItem->isDummy()) {
                $qty = $orderItem->getQtyOrdered() ? $orderItem->getQtyOrdered() : 1;
            } elseif ($isQtysEmpty) {
                $qty = $orderItem->getQtyToInvoice();
            } else {
                $qty = 0;
            }
            $totalQty += $qty;
            $this->setInvoiceItemQuantity($item, $qty);
            $invoice->addItem($item);
        }
        $invoice->setTotalQty($totalQty);
        $invoice->collectTotals();
        $order->getInvoiceCollection()->addItem($invoice);
        return $invoice;
    }

    /**
     * Prepare qty to invoice for parent and child products if theirs qty is not specified in initial request.
     *
     * @param Order $order
     * @param array $qtys
     * @return array
     */
    private function prepareItemsQty(Order $order, array $qtys = [])
    {
        foreach ($order->getAllItems() as $orderItem) {
            // add to fix quantity issue
            $itemId = $orderItem->getId() ? : $orderItem->getQuoteItemId();
            if (empty($qtys[$itemId])) {
                if ($orderItem->getProductType() == Type::TYPE_BUNDLE && !$orderItem->isShipSeparately()) {
                    $qtys[$itemId] = $orderItem->getQtyOrdered() - $orderItem->getQtyInvoiced();
                } else {
                    $parentItem = $orderItem->getParentItem();
                    $parentItemId = $parentItem ? $parentItem->getId() : null;
                    if ($parentItemId && isset($qtys[$parentItemId])) {
                        $qtys[$orderItem->getId()] = $qtys[$parentItemId];
                    }
                    continue;
                }
            }

            $this->prepareItemQty($orderItem, $qtys);
        }
        return $qtys;
    }

    /**
     * Prepare qty_invoiced for order item
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @param array $qtys
     */
    private function prepareItemQty(\Magento\Sales\Api\Data\OrderItemInterface $orderItem, &$qtys)
    {
        $this->prepareBundleQty($orderItem, $qtys);
        // add to fix quantity issue
        $itemId = $orderItem->getId()?:$orderItem->getQuoteItemId();
        if ($orderItem->isDummy()) {
            if ($orderItem->getHasChildren()) {
                foreach ($orderItem->getChildrenItems() as $child) {
                    // add to fix quantity issue
                    $childItemId = $child->getId() ? : $child->getQuoteItemId();
                    if (!isset($qtys[$childItemId])) {
                        $qtys[$childItemId] = $child->getQtyToInvoice();
                    }
                    $parentId = $orderItem->getParentItemId();
                    if ($parentId && array_key_exists($parentId, $qtys)) {
                        $qtys[$orderItem->getId()] = $qtys[$parentId];
                    } else {
                        continue;
                    }
                }
            } elseif ($orderItem->getParentItem()) {
                $parent = $orderItem->getParentItem();
                $parentId = $parent->getId();
                if (!isset($qtys[$parentId])) {
                    $qtys[$parentId] = $parent->getQtyToInvoice();
                }
            }
        }
    }

    /**
     * Prepare qty to invoice for bundle products
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @param array $qtys
     */
    private function prepareBundleQty(\Magento\Sales\Api\Data\OrderItemInterface $orderItem, &$qtys)
    {
        // add to fix quantity issue
        $itemId = $orderItem->getId() ? : $orderItem->getQuoteItemId();
        if ($orderItem->getProductType() == Type::TYPE_BUNDLE && !$orderItem->isShipSeparately()) {
            foreach ($orderItem->getChildrenItems() as $childItem) {
                $bundleSelectionAttributes = $childItem->getProductOptionByCode('bundle_selection_attributes');
                if (is_string($bundleSelectionAttributes)) {
                    $bundleSelectionAttributes = $this->serializer->unserialize($bundleSelectionAttributes);
                }
                // add to fix quantity issue
                $childItemId = $childItem->getId() ? : $childItem->getQuoteItemId();
                $qtys[$childItemId] = $qtys[$itemId] * $bundleSelectionAttributes['qty'];
            }
        }
    }

    /**
     * Check if order item can be invoiced.
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @param array $qtys
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _canInvoiceItem(\Magento\Sales\Api\Data\OrderItemInterface $item, array $qtys = [])
    {
        if ($item->getLockedDoInvoice()) {
            return false;
        }
        if ($item->isDummy()) {
            if ($item->getHasChildren()) {
                foreach ($item->getChildrenItems() as $child) {
                    // add to fix quantity issue
                    $childItemId = $child->getId() ? : $child->getQuoteItemId();
                    if (empty($qtys)) {
                        if ($child->getQtyToInvoice() > 0) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$childItemId]) && $qtys[$childItemId] > 0) {
                            return true;
                        }
                    }
                }
                return false;
            } elseif ($item->getParentItem()) {
                $parent = $item->getParentItem();
                if (empty($qtys)) {
                    return $parent->getQtyToInvoice() > 0;
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $item->getQtyToInvoice() > 0;
        }
    }
}
