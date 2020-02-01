<?php
namespace Convert\SkinQuiz\Controller\Quiz;

use \Magento\Store\Model\StoreManagerInterface;

class Addalltocart extends \Magento\Framework\App\Action\Action
{
	protected $formKey;   
	protected $cart;
	protected $product;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Data\Form\FormKey $formKey,
		\Magento\Checkout\Model\Cart $cart,
		\Magento\Catalog\Model\Product $product
	)
	{
		$this->formKey = $formKey;
		$this->cart = $cart;
		$this->product = $product; 
		parent::__construct($context);
	}

	public function execute()
	{
		$arrayproductjson = $this->getRequest()->getParam('arrayproduct');
		$arrayproductid = json_decode($arrayproductjson, TRUE);
		$this->cart->addProductsByIds($arrayproductid);
		$this->cart->save();
		die(json_encode($arrayproductid)) ;
	}
}
