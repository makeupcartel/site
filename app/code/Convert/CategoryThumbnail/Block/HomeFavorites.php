<?php

namespace Convert\CategoryThumbnail\Block;

class HomeFavorites extends \Magento\Framework\View\Element\Template
{
    protected $_categoryFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context
    )
    {
        $this->_categoryFactory = $categoryFactory;
        $this->_registry = $registry;
        parent::__construct($context);
    }

    public function getCategoryFavorites($cat_id)
    {
        $category = $this->_categoryFactory->create()->load($cat_id);
        return $category;
    }

    public function getCurrentCategory()
    {
        $category = $this->_registry->registry('current_category');
        return $category;
    }
}