<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Adminhtml\Analytics\Wanted;

use Magento\Backend\Block\Template;
use Amasty\Xsearch\Model\ResourceModel\UserSearch\Collection;

class QueryList extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Xsearch::analytics/wanted.phtml';

    /**
     * @var \Amasty\Xsearch\Model\QueryInfo
     */
    private $queryInfo;

    public function __construct(
        Template\Context $context,
        \Amasty\Xsearch\Model\QueryInfo $queryInfo,
        array $data = []
    ) {
        $this->queryInfo = $queryInfo;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getWantedQueries()
    {
        return $this->queryInfo->getMostWantedQueries(Collection::LIMIT_LAST_DATA);
    }

    /**
     * @return string
     */
    public function getMoreUrl()
    {
        return $this->getUrl('amsearch/wanted/index');
    }
}
