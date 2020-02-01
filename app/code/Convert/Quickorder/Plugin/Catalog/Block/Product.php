<?php

namespace Convert\Quickorder\Plugin\Catalog\Block;

class Product
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_request = $request;
    }

    public function aroundGetDetailsRenderer(\Magento\Catalog\Block\Product\AbstractProduct $subject, callable $proceed, $type = null)
    {
        if ($type === null) {
            $type = 'default';
        }
        $rendererList = $this->getDetailsRendererList($subject);
        if ($rendererList) {
            return $rendererList->getRenderer($type, 'default');
        }
        return null;
    }

    /**
     * Return the list of details
     *
     * @return \Magento\Framework\View\Element\RendererList
     */
    protected function getDetailsRendererList($subject)
    {
        $route      = $this->_request->getRouteName();
        $action     = $this->_request->getActionName();
        if ($route == 'cvquickorder' && $action == 'index') {
            $result = $subject->getDetailsRendererListName() ? $subject->getLayout()->getBlock(
                $subject->getDetailsRendererListName()
            ) : $subject->getChildBlock(
                'quickorder.toprenderers'
            );
        } else {
            $result = $subject->getDetailsRendererListName() ? $subject->getLayout()->getBlock(
                $subject->getDetailsRendererListName()
            ) : $subject->getChildBlock(
                'details.renderers'
            );
        }
        return $result;
    }
}
