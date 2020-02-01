<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Backend\Model\Url;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\LayoutFactory;
use Unirgy\RapidFlow\Model\Profile;

class Upload extends Content
{
    /**
     * @var Url
     */
    protected $_backendModelUrl;

    /**
     * @var Profile
     */
    protected $_profile;

    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Config $mediaConfig,
        LayoutFactory $frameworkViewLayoutFactory,
        Url $backendModelUrl,
        array $data = []
    ) {
        $this->_backendModelUrl = $backendModelUrl;

        parent::__construct($context, $jsonEncoder, $mediaConfig, $data);
        $this->setTemplate('Unirgy_RapidFlow::urapidflow/upload.phtml');
        if(isset($data['profile'])){
            $this->_profile = $data['profile'];
        }
    }

    protected function _prepareLayout()
    {
        /** @var \Magento\Backend\Block\Media\Uploader $uploader */
        $uploader = $this->getLayout()->createBlock('Magento\Backend\Block\Media\Uploader');
        $this->setChild('uploader', $uploader);
        $uploader->setTemplate('Unirgy_RapidFlow::urapidflow/upload/uploader.phtml');
        $uploader->getConfig()
            ->setUrl($this->_backendModelUrl->addSessionParam()->getUrl('*/*/upload', $this->_params()))
            ->setFileField('file')
            ->setFilters([
                             'csv' => [
                                 'label' => __('CSV and Tab Separated files (.csv, .txt)'),
                                 'files' => ['*.csv', '*.txt']
                             ],
                             'all' => [
                                 'label' => __('All Files'),
                                 'files' => ['*.*']
                             ]
                         ]);

        return Widget::_prepareLayout();
    }

    /**
     * @return array
     */
    protected function _params()
    {
        /** @var Profile $profile */
        $profile = $this->_profile;
        return $profile && $profile instanceof Profile ? ['id' => $profile->getId()] : [];
    }
}
