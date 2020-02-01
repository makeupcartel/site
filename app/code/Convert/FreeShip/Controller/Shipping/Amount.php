<?php

namespace Convert\FreeShip\Controller\Shipping;

/**
 * Class Amount
 *
 * @package Convert\FreeShip\Controller\Shipping
 */
class Amount extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Convert\FreeShip\Helper\Data
     */
	protected $helperData;

    /**
     * Amount constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Convert\FreeShip\Helper\Data $helperData
     */
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Convert\FreeShip\Helper\Data $helperData

	)
	{
		$this->helperData = $helperData;
		return parent::__construct($context);
	}

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
	public function execute()
	{
	}
}