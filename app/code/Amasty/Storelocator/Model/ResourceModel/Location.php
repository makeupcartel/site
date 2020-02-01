<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\ResourceModel;

use Magento\Framework\App\Filesystem\DirectoryList;

class Location extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * Location constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Amasty\Base\Model\Serializer                     $serializer
     * @param null                                              $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Amasty\Base\Model\Serializer $serializer,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->serializer = $serializer;
    }

    public function _construct()
    {
        $this->_init('amasty_amlocator_location', 'id');
    }

    /**
     * Perform actions before object save
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (is_array($object->getSchedule())) {
            $object->setSchedule($this->serializer->serialize($object->getSchedule()));
        }
        //convert State to ID
        $state = $object->getStateId();
        if (is_numeric($state)) {
            $ObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $object->setState(
                $ObjectManager->get('Magento\Directory\Model\Region')->load($object->getStateId())
                    ->getName()
            );
        }
    }
    
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        //remove image
        $ObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $path = $ObjectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            'amasty/amlocator/'
        );

        $ObjectManager->get('Magento\Framework\Filesystem\Io\File')->rm($path . $object->getStoreImg());
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $data = $object->getData();
        if (isset($data['store_attribute']) && !empty($data['store_attribute'])) {
            $insertData = [];
            $storeId = (int)$object->getId();

            foreach ($data['store_attribute'] as $attributeId => $values) {
                $value = $values;
                if (is_array($values)) {
                    $value = implode(',', $values);
                }
                $insertData[] = [
                    'attribute_id' => $attributeId,
                    'store_id' => $storeId,
                    'value' => $value
                ];
            }
            
            $table = $this->getTable('amasty_amlocator_store_attribute');

            if (count($insertData) > 0) {
                $this->getConnection()->insertOnDuplicate($table, $insertData, ['value']);
            }
        }
        $this->_isPkAutoIncrement = true;
    }

    public function setAttributesData(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $connection = $this->getConnection();

            $select = $connection->select()
                ->from(
                    ['sa' => $this->getTable('amasty_amlocator_store_attribute')]
                )
                ->joinLeft(
                    ['attr' => $this->getTable('amasty_amlocator_attribute')],
                    '(sa.attribute_id = attr.attribute_id)'
                )
                ->joinLeft(
                    ['attr_option' => $this->getTable('amasty_amlocator_attribute_option')],
                    '(sa.attribute_id = attr_option.attribute_id)',
                    [
                        'options_serialized' => 'attr_option.options_serialized',
                        'value_id' => 'attr_option.value_id'
                    ]
                )
                ->where(
                    'store_id = ?',
                    (int)$object->getId()
                )
                ->where(
                    'value <> ""'
                )
            ;

            $attributes = $connection->fetchAll($select);

            $result = [];

            if (!empty($attributes)) {
                foreach ($attributes as $key => $attribute) {
                    if (!array_key_exists($attribute['attribute_id'], $result)) {
                        $result[$attribute['attribute_id']] = $attribute;
                        $labels = $this->serializer->unserialize($attribute['label_serialized']);
                        $result[$attribute['attribute_id']]['labels'] = $labels;
                    }
                    if (isset($attribute['options_serialized']) && $attribute['options_serialized']) {
                        $values = explode(',', $attribute['value']);
                        if (in_array($attribute['value_id'], $values)) {
                            $options = $this->serializer->unserialize($attribute['options_serialized']);
                            $result[$attribute['attribute_id']]['option_title'][] = $options;
                        }
                    }
                    if ($attribute['frontend_input'] == 'boolean') {
                        if ((int)$attribute['value'] == 1) {
                            $result[$attribute['attribute_id']]['boolean_title'] = __('Yes');
                        } else {
                            $result[$attribute['attribute_id']]['boolean_title'] = __('No');
                        }
                    }
                }
            }

            $attributes = array_values($result);

            $object->setData('attributes', $attributes);
        }

        return $object;
    }

    /**
     * Set _isPkAutoIncrement for saving new location
     */
    public function setResourceFlags()
    {
        $this->_isPkAutoIncrement = false;
    }
}
