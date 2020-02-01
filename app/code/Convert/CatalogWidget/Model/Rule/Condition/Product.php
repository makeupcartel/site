<?php

namespace Convert\CatalogWidget\Model\Rule\Condition;

/**
 * Class Product
 *
 * @package Convert\CatalogWidget\Model\Rule\Condition
 */
class Product extends \Magento\CatalogWidget\Model\Rule\Condition\Product
{
    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return $this|\Magento\CatalogWidget\Model\Rule\Condition\Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
  protected function addNotGlobalAttribute(
      \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute,
      \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
  ) {
      $storeId =  $this->storeManager->getStore()->getId();
      $values = $collection->getAllAttributeValues($attribute);
      $validEntities = [];
      if ($values) {
          foreach ($values as $entityId => $storeValues) {
              if (isset($storeValues[$storeId])) {
                  if ($this->validateAttribute($storeValues[$storeId])) {
                      $validEntities[] = $entityId;
                  }
              } else {
                  if (isset($storeValues[\Magento\Store\Model\Store::DEFAULT_STORE_ID])) {
                    if ($this->validateAttribute($storeValues[\Magento\Store\Model\Store::DEFAULT_STORE_ID])) {
                      $validEntities[] = $entityId;
                    }
                  }
              }
          }
      }
      $this->setOperator('()');
      $this->unsetData('value_parsed');
      if ($validEntities) {
          $this->setData('value', implode(',', $validEntities));
      } else {
          $this->unsetData('value');
      }

      return $this;
  }
}