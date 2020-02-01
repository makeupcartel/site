<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_ReferralSystem
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\ReferralSystem\Model\ResourceModel\DiscountDenomination;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
	 
class Collection extends AbstractCollection{
    
    protected $_idFieldName = 'id';
   
	protected function _construct()
	{
	    $this->_init(
	            'Ced\ReferralSystem\Model\DiscountDenomination',
	            'Ced\ReferralSystem\Model\ResourceModel\DiscountDenomination'
	    );
	}
}