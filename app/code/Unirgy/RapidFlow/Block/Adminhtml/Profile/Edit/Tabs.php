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

namespace Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tabs as WidgetTabs;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Unirgy\RapidFlow\Model\Config;

class Tabs extends WidgetTabs
{

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var Config
     */
    protected $_rapidFlowConfig;


    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        Registry $registry,
        LayoutFactory $layoutFactory,
        Config $rapidFlowConfig,
        array $data = []
    )
    {
        $this->_registry = $registry;
        $this->_rapidFlowConfig = $rapidFlowConfig;

        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->setId('profile_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Profile Information'));
    }

    protected function _beforeToHtml()
    {
        $profile = $this->_registry->registry('profile_data');

        if (in_array($profile->getRunStatus(), ['pending', 'running', 'paused'])) {
            $this->addTab('status_section', [
                'label' => __('Profile Status'),
                'title' => __('Profile Status'),
                'content' => $this->getLayout()
                    ->createBlock('Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit\Tab\Status')
                    ->setProfile($profile)
                    ->toHtml(),
            ]);
            return parent::_beforeToHtml();
        }

        $this->addTab('main_section', [
            'label' => __('Profile Information'),
            'title' => __('Profile Information'),
            'content' => $this->getLayout()
                ->createBlock('Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit\Tab\Main')
                ->setProfile($profile)
                ->toHtml(),
        ]);

        $jsonTab = [
            'label' => __('Profile Configuration as JSON'),
            'title' => __('Profile Configuration as JSON'),
            'content' => $this->getLayout()
                ->createBlock('Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit\Tab\Json')
                ->setProfile($profile)
                ->toHtml(),
        ];

        if (!$profile->getId()) {
            $this->addTab('json_section', $jsonTab);
            return parent::_beforeToHtml();
        }

        if (in_array($profile->getRunStatus(), ['stopped', 'finished'])) {
            $this->addTab('status_section', [
                'label' => __('Profile Status'),
                'title' => __('Profile Status'),
                'content' => $this->getLayout()
                    ->createBlock('Unirgy\RapidFlow\Block\Adminhtml\Profile\Status')
                    ->setProfile($profile)
                    ->toHtml(),
            ]);
        }
        /*
                $this->addTab('schedule_section', array(
                    'label'     => __('Schedule Options'),
                    'title'     => __('Schedule Options'),
                    'content'   => $this->getLayout()->createBlock('Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit\Tab\Schedule')
                        ->setProfile($profile)
                        ->toHtml(),
                ));
        */
        $tabs = $this->_rapidFlowConfig
            ->getProfileTabs($profile->getProfileType(), $profile->getDataType());

        if ($tabs) {
            foreach ($tabs as $key => $tab) {
                $this->addTab($key . '_section', [
                    'label' => __((string)$tab->title),
                    'class' => 'admin__scope-old',
                    'title' => __((string)$tab->title),
                    'content' => $this->getLayout()->createBlock((string)$tab->block, '', ['data' => ['profile' => $profile]])
                        ->setProfile($profile)
                        ->toHtml(),
                ]);
            }
        }

        $this->addTab('json_section', $jsonTab);

        return parent::_beforeToHtml();
    }
}
