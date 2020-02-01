<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2018 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_Core
 */

namespace EH\Core\Model;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\Renderer\RendererInterface;

class HtmlMessageRenderer implements RendererInterface
{
    const CODE = 'html_renderer';

    const MESSAGE_IDENTIFIER = 'html_message';

    /**
     * @param MessageInterface $message
     * @param array $initializationData
     * @return string
     */
    public function render(MessageInterface $message, array $initializationData)
    {
        return $message->getData()['html'];
    }
}