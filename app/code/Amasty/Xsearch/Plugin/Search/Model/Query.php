<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Plugin\Search\Model;

use Amasty\Xsearch\Controller\Redirect\Index;

class Query
{
    /**
     * @var \Amasty\Xsearch\Model\UserSearchFactory
     */
    private $userSearch;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Search\Model\Query
     */
    private $query;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(
        \Amasty\Xsearch\Model\UserSearchFactory $userSearch,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Search\Model\Query $query,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->userSearch = $userSearch;
        $this->customerSession = $customerSession;
        $this->query = $query;
        $this->request = $request;
    }

    /**
     * @param \Magento\Search\Model\Query $subject
     * @param $proceed
     * @param $numResults
     * @return \Magento\Search\Model\Query
     */
    public function aroundSaveNumResults($subject, $proceed, $numResults)
    {
        if ($this->request->getParam(Index::AMSEARCH_404_REDIRECT) !== null) {
            return $subject;
        }

        return $proceed($numResults);
    }

    /**
     * @param \Magento\Search\Model\Query $subject
     * @param $proceed
     * @return \Magento\Search\Model\Query
     */
    public function aroundSaveIncrementalPopularity($subject, $proceed)
    {
        if ($this->request->getParam(Index::AMSEARCH_404_REDIRECT) !== null) {
            return $subject;
        }

        return $proceed();
    }

    /**
     * @param $subject
     * @return \Magento\Search\Model\Query
     */
    public function afterSaveNumResults($subject)
    {
        $customerId = $this->customerSession->getCustomerId() ?: $this->customerSession->getSessionId();
        $query = $this->query->loadByQueryText($subject->getQueryText());
        if ($query->getQueryId()) {
            $this->userSearch->create()->setUserKey($customerId)
                ->setQueryId($query->getQueryId())
                ->setCreatedAt(date('Y-m-d H:i:s'))
                ->save();
        }

        return $subject;
    }
}
