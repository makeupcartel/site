<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block\Adminhtml\System\Config\Field;

use Magento\Backend\Block\Template\Context;
use Aheadworks\Blog\Model\Serialize\SerializeInterface;
use Aheadworks\Blog\Model\Serialize\Factory as SerializeFactory;

/**
 * Fieldset renderer for Wordpress import
 */
class WordpressImport extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * 'Import file' input ID
     */
    const IMPORT_FILE_INPUT_ID = 'import_file';

    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Blog::system/config/wordpress_import.phtml';

    /**
     * @var SerializeInterface
     */
    private $serializer;

    /**
     * @param Context $context
     * @param array $data
     * @param SerializeFactory $serializeFactory
     */
    public function __construct(
        Context $context,
        SerializeFactory $serializeFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->serializer = $serializeFactory->create();
    }

    /**
     * @inheritDoc
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Retrieve script options encoded to json
     *
     * @return string
     */
    public function getScriptOptions()
    {
        $params = [
            'wpImportUrl' => $this->getUrl(
                'aw_blog_admin/post/wordpressImport/',
                [
                    '_current' => true,
                    '_secure' => $this->templateContext->getRequest()->isSecure()
                ]
            ),
            'importInputSelector' => '#' . self::IMPORT_FILE_INPUT_ID
        ];

        return $this->serializer->serialize($params);
    }
}
