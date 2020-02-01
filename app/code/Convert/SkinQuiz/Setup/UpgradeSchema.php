<?php
namespace Convert\SkinQuiz\Setup;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
	/**
     * Eav setup factory
     * @var EavSetupFactory
     */
	private $eavSetupFactory;
	
	/**
     * Init
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(\Magento\Eav\Setup\EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
	}
	

	public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
	{
		$setup->startSetup();
		$conn=$setup->getConnection();
		$tableName=$setup->getTable('quiz_customer');
		if($conn->isTableExists($tableName) != true && $context->getVersion() && version_compare($context->getVersion(), '2.1.2'))
		{
			$table=$conn->newTable($tableName)
			->addColumn(
				'id',
				Table::TYPE_INTEGER,
				null,
				['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true]
			)
			->addColumn(
				'email',
				Table::TYPE_TEXT,
				255,
				['nullable'=>false,'default'=>'']
			)
			->addColumn(
				'guestquiz',
				Table::TYPE_TEXT,
				'2M',
				['nullable'=>false,'default'=>'']
			)
			->setOption('charset','utf8');
			$conn->createTable($table);				
		}

		if (version_compare($context->getVersion(), '2.1.3', '<')) {
            $this->addSkinQuizExcludeAttribute($setup);
		}
		
		if (version_compare($context->getVersion(), '2.1.4', '<')) {
            $this->changeSkinQuizExcludeToText($setup);
        }

		$setup->endSetup();
	}

	private function addSkinQuizExcludeAttribute(SchemaSetupInterface $installer) 
    {
		$eavSetup = $this->eavSetupFactory->create();
		
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'skin_quiz_exclude',
            [
                'group' => 'Skin Quiz',
                'type' => 'varchar',
                'label' => 'Skin Quiz Exclude',
                'input' => 'multiselect',
				'source' => 'Convert\SkinQuiz\Model\Attribute\Source\SkinExclude',
				'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'required' => false,
                'sort_order' => 50,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'visible' => true,
                'is_html_allowed_on_front' => false,
                'visible_on_front' => false
            ]
        );
	}
	
	private function changeSkinQuizExcludeToText(SchemaSetupInterface $installer) 
    {
		$eavSetup = $this->eavSetupFactory->create();
		
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'skin_quiz_exclude',
            [
                'backend_type' => 'text'
            ]
        );
    }
}