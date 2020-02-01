<?php
/**
 * @author  Hong Tran <hong@convertdigital.com.au>
 * @package makeup-cartel
 * @copyright  Copyright (c) 2019, Convertdigital (https://www.convertdigital.com.au/)
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @date    28/11/2019
 */
namespace Convert\Catalog\Block;
class Navigation extends \Magento\Catalog\Block\Navigation
{
    public function getCategoryById($catId){
        return $this->_categoryInstance->load($catId);
    }
}