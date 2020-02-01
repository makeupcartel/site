<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Controller\Adminhtml\RelevanceRule;

use Magento\Framework\Controller\ResultFactory;

class Index extends AbstractRelevance
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $this->initPage($resultPage);
    }
}
