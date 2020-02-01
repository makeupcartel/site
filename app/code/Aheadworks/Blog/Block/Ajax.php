<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block;

use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Blog\Model\Serialize\SerializeInterface;
use Aheadworks\Blog\Model\Serialize\Factory as SerializeFactory;

/**
 * Class Ajax
 *
 * @package Aheadworks\Blog\Block
 */
class Ajax extends \Magento\Framework\View\Element\Template
{
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
     * Retrieve script options encoded to json
     *
     * @return string
     */
    public function getScriptOptions()
    {
        $params = [
            'url' => $this->getUrl(
                'aw_blog/block/render/',
                [
                    '_current' => true,
                    '_secure' => $this->templateContext->getRequest()->isSecure()
                ]
            )
        ];
        return $this->serializer->serialize($params);
    }
}
