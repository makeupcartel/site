<?php
/*

Plumrocket Inc.

NOTICE OF LICENSE

This source file is subject to the End-user License Agreement
that is available through the world-wide-web at this URL:
http://wiki.plumrocket.net/wiki/EULA
If you are unable to obtain it through the world-wide-web, please
send an email to support@plumrocket.com so we can send you a copy immediately.

@package    Plumrocket_Base-v2.x.x
@copyright  Copyright (c) 2015-2017 Plumrocket Inc. (http://www.plumrocket.com)
@license    http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement

*/

namespace Plumrocket\Base\Block\Adminhtml\System\Config\Form;

class Serial extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Plumrocket\Base\Model\Product
     */
    protected $baseProduct;

    /**
     * @var \Plumrocket\Base\Helper\Main
     */
    protected $main;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Plumrocket\Base\Model\ProductFactory   $baseProductFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array                                   $data
     */
    public function __construct(
        \Plumrocket\Base\Model\ProductFactory   $baseProductFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Plumrocket\Base\Helper\Main $main,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->baseProduct = $baseProductFactory->create();
        $this->main = $main;
        $this->objectManager = $objectManager;
    }

    /**
     * Render element value
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<td class="value with-tooltip">';
        $html .= $this->_getElementHtml($element);

        $product = $this->baseProduct->load((string)$element->getHint());
        if ($product->getSession()) {
            if ($product->isInStock()) {
                $src = 'images/success_msg_icon.gif';
                $title = implode('', array_map('ch'.'r', explode('.', '84.104.97.110.107.32.121.111.117.33.32.89.111.117.114.32.115.101.114.105.97.108.32.107.101.121.32.105.115.32.97.99.99.101.112.116.101.100.46.32.89.111.117.32.99.97.110.32.115.116.97.114.116.32.117.115.105.110.103.32.101.120.116.101.110.115.105.111.110.46')));
                $html .= '<div class="tooltip"><span><span><img src="'.$this->getViewFileUrl('Plumrocket_Base::images/success_msg_icon.gif').'" style="margin-top: 2px;float: right;" /></span></span>';
                $html .= '<div class="tooltip-content">' . $title . '</div></div>';
            } else {
                $html .= '<div class="tooltip"><span><span><img src="'.$this->getViewFileUrl('Plumrocket_Base::images/error_msg_icon.gif').'" style="margin-top: 2px;float: right;" /></span></span></div>';
            }
        }

        $html .= base64_decode('PHAgY2xhc3M9Im5vdGUiPjxzcGFuPgogICAgICAgICAgICBZb3UgY2FuIGZpbmQgPHN0cm9uZz5TZXJpYWwgS2V5PC9zdHJvbmc+IGluIHlvdXIgYWNjb3VudCBhdCA8YSB0YXJnZXQ9Il9ibGFuayIgaHJlZj0iaHR0cHM6Ly9zdG9yZS5wbHVtcm9ja2V0LmNvbS9kb3dubG9hZGFibGUvY3VzdG9tZXIvcHJvZHVjdHMvIj5zdG9yZS5wbHVtcm9ja2V0LmNvbTwvYT4uIEZvciBtYW51YWwgPGEgdGFyZ2V0PSJfYmxhbmsiIGhyZWY9Imh0dHA6Ly93aWtpLnBsdW1yb2NrZXQuY29tL3dpa2kvTWFnZW50b18yX0xpY2Vuc2VfSW5zdGFsbGF0aW9uIj5jbGljayBoZXJlPC9hPi4KICAgICAgICA8L3NwYW4+PC9wPg==');

        $html .= '</td>';
        return $html;
    }

    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $fields = [
            'plumbase_order_id' => __('Marketplace Order ID'),
            'plumbase_account_email' => __('Marketplace Account Email')
        ];

        $handle = (string)$element->getHint();
        $hideMarketplaceFields = !$this->isMarketplace($handle) ? 'style="display:none;"' : '';
        $marketplaceFields = '';

        foreach ($fields as $key => $value) {
            $comment = ($key == 'plumbase_account_email') ? '<p class="note">
                <span>' . __('You can find Marketplace Order ID and Email in your Magento Marketplace Account. For manual <a target="_blank" href="%1">click here</a>.', 'http://wiki.plumrocket.com/License_Installation_For_Magento_2_Marketplace_Customers')
                . '</span></p>' : '';

            $marketplaceFields .= '
                <tr ' . $hideMarketplaceFields . ' id="row_'. $key . '">
                    <td class="label">
                        <label for="' . $key . '">' . $value . '</label>
                    </td>
                    <td class="value">
                          <input id="' . $key . '" class="input-text" type="text"/>
                          ' . $comment . '
                    </td>
                </tr>
            ';
        }

        $marketplaceFields .= '
            <tr ' . $hideMarketplaceFields . '>
                 <td class="label"></td>
                 <td class="value">
                      <button id="plumbase_activate_extension" title="' . __("Activate Extension") . '" type="button" class="scalable" onclick="false;">
                           <span>
                               <span>
                                    <span> ' . __("Activate Extension") . '</span>
                               </span>
                           </span>
                      </button>
                 </td>
            </tr>
        ';

        $serialKeyHtml = parent::render($element);
        $value = (string)$element->getValue();

        if ($this->isMarketplace($handle) && empty($value)) {
            $serialKeyHtml = str_replace("<tr", "<tr style='display:none;'", $serialKeyHtml)
                . $marketplaceFields . $this->_js($element->getHtmlId(), $handle);
        }

        return $serialKeyHtml;
    }

    protected function isMarketplace($handle)
    {
        $modHelper = $this->moduleData($handle);
        $dataOriginMethod = strrev('yeK'.'remo'.'tsuC'.'teg');
        $cKey = $modHelper->{$dataOriginMethod}();

        if (method_exists($modHelper, 'isMarketplace')) {
            return $modHelper->isMarketplace($cKey);
        }

        return false;
    }

    private function  _js($serialKeyId, $handle)
    {
        return "
            <script type='text/javascript'>
                require([
                    'jquery',
                    'mage/translate',
                    'mage/storage',
                    'Magento_Ui/js/modal/alert',
                    'domReady!'
                ], function ($, __, storage, alert) {
                    var button = $('#plumbase_activate_extension'),
                    orderId = $('#plumbase_order_id'),
                    accountEmail = $('#plumbase_account_email'),
                    serialKey = $('#" . $serialKeyId . "'),
                    messageBlock = $(\".page-main-actions\"),
                    plumbaseMessageBlockEl;
                    
                    button.on('click', function(el) {
                        jQuery('body').loader('show');
                        $.ajax({
                           type: 'POST',
                           url: '" . $this->getUrl('plumbase/call') . "',
                           data: {order_id:orderId.val(), account_email:accountEmail.val(), module:'" . $handle . "'},
                           success: function(response) {
                                var json = JSON.parse(response);
                                
                                if (typeof json.data != \"undefined\") {
                                    serialKey.val(json.data);
                                }
                                
                                if (typeof json.error != \"undefined\") {
                                    var plbMessage = '<div id=\'plumbaseMessageBlockError\' class=\'message message-error error\'><div data-ui-id=\'messages-message-error\'>' 
                                        + json.error
                                        + '</div></div><br/>';
    
                                    plumbaseMessageBlockEl = $(\"#plumbaseMessageBlockError\");
                                    
                                    if (plumbaseMessageBlockEl.length > 0) {
                                        plumbaseMessageBlockEl.html(json.error);
                                    } else if (messageBlock.length > 0) {
                                        messageBlock.after(plbMessage);
                                    }
                                } else {
                                    plumbaseMessageBlockEl = $(\"#plumbaseMessageBlockError\");
                                    if (plumbaseMessageBlockEl.length > 0) {
                                        plumbaseMessageBlockEl.hide();
                                    }
                                }

                                if (json.hash) {
                                    serialKey.parents('tr').show();
                                    button.parents('tr').hide();
                                    orderId.parents('tr').hide();
                                    accountEmail.parents('tr').hide();
                                }
                              
                                jQuery('body').loader('hide');
                           }
                        });
                    });
                });
            </script>
        ";
    }

    /**
     * @param $name
     * @return mixed
     */
    private function moduleData($name)
    {
        return $this->objectManager->get('Plumrocket\\' . $name . '\Helper\Main');
    }
}