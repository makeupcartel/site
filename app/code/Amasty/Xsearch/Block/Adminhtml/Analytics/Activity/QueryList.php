<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Adminhtml\Analytics\Activity;

use Magento\Backend\Block\Template;
use Amasty\Xsearch\Model\ResourceModel\UserSearch\Collection;

class QueryList extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Xsearch::analytics/activity.phtml';

    /**
     * @var \Amasty\Xsearch\Model\QueryInfo
     */
    private $queryInfo;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

    public function __construct(
        Template\Context $context,
        \Amasty\Xsearch\Model\QueryInfo $queryInfo,
        \Magento\Framework\DataObjectFactory $objectFactory,
        array $data = []
    ) {
        $this->queryInfo = $queryInfo;
        $this->objectFactory = $objectFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getLastSearches()
    {
        $data = array_slice(
            $this->queryInfo->getAnalyticsData(Collection::GROUP_BY_DAY),
            0,
            Collection::LIMIT_LAST_DATA
        );

        $result = [];
        foreach ($data as $item) {
            $result[] = $this->objectFactory->create(['data' => $item]);
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getMoreUrl()
    {
        return $this->getUrl('amsearch/activity/index');
    }
}
