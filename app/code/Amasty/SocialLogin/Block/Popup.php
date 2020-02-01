<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Block;

class Popup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Amasty\SocialLogin\Model\ConfigData
     */
    private $configData;

    /**
     * @var \Amasty\SocialLogin\Model\Source\ButtonPosition
     */
    private $buttonPosition;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\SocialLogin\Model\ConfigData $configData,
        \Magento\Customer\Model\Session $customerSession,
        \Amasty\SocialLogin\Model\Source\ButtonPosition $buttonPosition,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configData = $configData;
        $this->customerSession = $customerSession;
        $this->buttonPosition = $buttonPosition;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @return bool
     */
    public function isSocialsEnabled()
    {
        return $this->configData->getConfigValue('general/enabled')
            && !$this->customerSession->isLoggedIn();
    }

    /**
     * @return bool
     */
    public function isPopupEnabled()
    {
        return $this->configData->getConfigValue('general/popup_enabled');
    }

    /**
     * @return string
     */
    public function getPositionTitle()
    {
       return $this->configData->getPositionTitle();
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        return $this->jsonEncoder->encode([
            'logout_url'    => $this->getUrl('amsociallogin/logout/index'),
            'header_update' => $this->getUrl('amsociallogin/header/update')
        ]);
    }
}
