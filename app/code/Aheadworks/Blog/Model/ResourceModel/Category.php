<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel;

use Aheadworks\Blog\Api\Data\CategoryInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Category resource model
 * @package Aheadworks\Blog\Model\ResourceModel
 */
class Category extends AbstractDb
{
    /**#@+
     * Constants defined for tables
     */
    const BLOG_CATEGORY_TABLE = 'aw_blog_category';
    const BLOG_CATEGORY_STORE_TABLE = 'aw_blog_category_store';
    /**#@-*/

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::BLOG_CATEGORY_TABLE, 'id');
    }

    /**
     * {@inheritdoc}
     * @param CategoryInterface $object
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isObjectNew()) {
            $object->unsetData(CategoryInterface::PATH);
        }
        return parent::_beforeSave($object);
    }

    /**
     * {@inheritdoc}
     * @param CategoryInterface $object
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (empty($object->getPath())) {
            $this->getConnection()->update(
                $this->getTable(self::BLOG_CATEGORY_TABLE),
                [CategoryInterface::PATH => $object->getId()],
                [CategoryInterface::ID . ' = ?' => $object->getId()]
            );
        }
        return parent::_afterSave($object);
    }

    /**
     * Load category by url key
     *
     * @param \Aheadworks\Blog\Model\Category $category
     * @param string $urlKey
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByUrlKey(\Aheadworks\Blog\Model\Category $category, $urlKey)
    {
        $connection = $this->getConnection();
        $bind = ['url_key' => $urlKey];
        $select = $connection->select()
            ->from($this->getMainTable(), $this->getIdFieldName())
            ->where('url_key = :url_key');

        $categoryId = $connection->fetchOne($select, $bind);
        if ($categoryId) {
            $this->load($category, $categoryId);
        } else {
            $category->setData([]);
        }

        return $this;
    }
}
