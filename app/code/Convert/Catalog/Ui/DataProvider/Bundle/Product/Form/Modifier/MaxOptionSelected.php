<?php

namespace Convert\Catalog\Ui\DataProvider\Bundle\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Modal;

/**
 * Class MaxOptionSelected
 *
 * @package Convert\Catalog\Ui\DataProvider\Bundle\Product\Form\Modifier
 */
class MaxOptionSelected extends AbstractModifier
{
    const CODE_BUNDLE_DATA = 'bundle-items';
    const CODE_BUNDLE_OPTIONS = 'bundle_options';

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modifyMeta(array $meta)
    {
        $path = $this->arrayManager->findPath(static::CODE_BUNDLE_DATA, $meta, null, 'children');

        $meta = $this->arrayManager->merge(
            $path,
            $meta,
            [
                'children' => [
                    self::CODE_BUNDLE_OPTIONS => $this->getBundleOptions()
                ]
            ]
        );

        //TODO: Remove this workaround after MAGETWO-49902 is fixed
        $bundleItemsGroup = $this->arrayManager->get($path, $meta);
        $meta = $this->arrayManager->set($path, $meta, $bundleItemsGroup);

        return $meta;
    }
    
    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }
    
    /**
     * Get Bundle Options structure
     *
     * @return array
     */
    protected function getBundleOptions()
    {
        return [
            'children' => [
                'record' => [
                    'children' => [
                        'product_bundle_container' => [
                            'children' => [
                                'option_info' => $this->getOptionInfo()
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
    /**
     * Get option info
     *
     * @return array
     */
    protected function getOptionInfo()
    {
        $result = [
            'children' => [
                'max_option' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => Form\Element\DataType\Text::NAME,
                                'formElement' => Form\Element\Input::NAME,
                                'componentType' => Form\Field::NAME,
                                'dataScope' => 'max_option',
                                'label' => __('Maximum number options selected'),
                                'validation' => ['validate-number' => '0'],
                                'sortOrder' => 25
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $result;
    }
}