<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Adminhtml\Analytics\Chart;

use Magento\Backend\Block\Template;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Phrase;
use Amasty\Xsearch\Model\ResourceModel\UserSearch\CollectionFactory;
use Amasty\Xsearch\Model\ResourceModel\UserSearch\Collection;

class Query extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Xsearch::analytics/chart/query.phtml';

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var null|array
     */
    private $totals;

    /**
     * @var \Amasty\Xsearch\Model\ResourceModel\UserSearch\CollectionFactory
     */
    private $queryCollection;

    /**
     * @var \Amasty\Xsearch\Model\QueryInfo
     */
    private $queryInfo;

    public function __construct(
        EncoderInterface $jsonEncoder,
        Template\Context $context,
        CollectionFactory $userSearchCollection,
        \Amasty\Xsearch\Model\QueryInfo $queryInfo,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonEncoder = $jsonEncoder;
        $this->queryCollection = $userSearchCollection;
        $this->queryInfo = $queryInfo;
    }

    /**
     * @return Phrase
     */
    public function getTitle()
    {
        return __('Search Volume');
    }

    /**
     * @return string
     */
    public function getAnalyticsData()
    {
        return $this->jsonEncoder->encode($this->queryInfo->getAnalyticsData(Collection::GROUP_BY_MOUNTH));
    }

    /**
     * @param string $field
     * @return int
     */
    public function getTotal($field)
    {
        if (!$this->totals) {
            $uniqueUsers = $this->queryCollection->create()->getTotalRowUniqueUsers();
            $productClickUsers = $this->queryCollection->create()->getTotalRowProductClick();
            $percentClick = $uniqueUsers ? round(($productClickUsers / $uniqueUsers) * 100, 2) : 0;
            $this->totals = [
                'popularity' => $this->queryCollection->create()->getTotalRowPopularity(),
                'unique_query' => $this->queryCollection->create()->getTotalRowUniqueQuery(),
                'unique_user' => $uniqueUsers,
                'product_click' => $percentClick,
            ];
        }

        return isset($this->totals[$field]) ? $this->totals[$field] : 0;
    }
}
