<?php

namespace Convert\CategoryThumbnail\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;

/**
 * Class UpgradeData
 *
 * @package Convert\CategoryThumbnail\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
	private $eavSetupFactory;

    /**
     * UpgradeData constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     */
	public function __construct(EavSetupFactory $eavSetupFactory)
	{
		$this->eavSetupFactory = $eavSetupFactory;
	}

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
	public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$setup->startSetup();
		if ($context->getVersion() && version_compare($context->getVersion(), '1.0.3')) {
			$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
			$eavSetup->addAttribute(
				\Magento\Catalog\Model\Category::ENTITY,
				'category_mobile_thumbnail',
				[
					'type' => 'varchar',
					'label' => 'Category Mobile Image',
					'input' => 'image',
					'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
					'required' => false,
					'sort_order' => 100,
					'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
					'group' => 'General Information',
				]
			);
		}
		$setup->endSetup();
	}
}