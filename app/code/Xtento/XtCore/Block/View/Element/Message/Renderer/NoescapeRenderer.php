<?php

/**
 * Product:       Xtento_XtCore
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2017-08-16T08:52:13+00:00
 * File:          app/code/Xtento/XtCore/Block/View/Element/Message/Renderer/NoescapeRenderer.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\XtCore\Block\View\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;

class NoescapeRenderer implements \Magento\Framework\View\Element\Message\Renderer\RendererInterface
{
    /**
     * complex_renderer
     */
    const CODE = 'noescape_renderer';

    /**
     * Renders complex message, no escaping
     *
     * @param MessageInterface $message
     * @param array $initializationData
     * @return string
     */
    public function render(MessageInterface $message, array $initializationData)
    {
        return $message->getText();
    }
}
