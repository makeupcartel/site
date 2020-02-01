<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Plugin\CatalogSearch\Block;

class Result
{
    /**
     * @var \Amasty\Xsearch\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    private $response;

    /**
     * CatalogSearch\Block\Result constructor.
     * @param \Amasty\Xsearch\Helper\Data $helper
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\App\Response\Http $response
     */
    public function __construct(
        \Amasty\Xsearch\Helper\Data $helper,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\Response\Http $response
    ) {
        $this->helper = $helper;
        $this->redirect = $redirect;
        $this->response = $response;
    }

    /**
     * @param $subject
     * @param int $result
     * @return int
     */
    public function afterGetResultCount($subject, $result)
    {
        if ($this->helper->isSingleProductRedirect()
            && !$subject->getRequest()->getParam('shopbyAjax')
            && $result == 1
        ) {
            $redirectUrl = $subject->getListBlock()->getLoadedProductCollection()->getFirstItem()->getProductUrl();
            $this->redirect->redirect($this->response, $redirectUrl);
        }

        return $result;
    }
}
