<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-05-10T19:18:39+00:00
 * File:          app/code/Xtento/ProductExport/Model/Destination/Email.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Destination;

class Email extends AbstractClass
{
    public function testConnection()
    {
        $this->initConnection();
        if (!$this->getDestination()->getBackupDestination()) {
            $this->getDestination()->setLastResult($this->getTestResult()->getSuccess())->setLastResultMessage($this->getTestResult()->getMessage())->save();
        }
        return $this->getTestResult();
    }

    public function initConnection()
    {
        $this->setDestination($this->destinationFactory->create()->load($this->getDestination()->getId()));
        $testResult = new \Magento\Framework\DataObject();
        $this->setTestResult($testResult);
        $this->getTestResult()->setSuccess(true)->setMessage(__('Ready to send emails.'));
        return true;
    }

    public function saveFiles($fileArray)
    {
        if (empty($fileArray)) {
            return [];
        }
        // Init connection
        $this->initConnection();
        $savedFiles = [];


        /** @var \Magento\Framework\Mail\Message $message */
        $mail = $this->objectManager->create('Magento\Framework\Mail\MessageInterface');

        $mail->setFrom($this->getDestination()->getEmailSender(), $this->getDestination()->getEmailSender());
        foreach (explode(",", $this->getDestination()->getEmailRecipient()) as $email) {
            $mail->addTo($email, '=?utf-8?B?' . base64_encode($email) . '?=');
        }

        $bodyFiles = [];
        foreach ($fileArray as $filename => $data) {
            $savedFiles[] = $filename;
            if (stripos($filename, '.pdf') === false) {
                $bodyFiles[] = $data;
            }
        }

        #$mail->setSubject($this->_replaceVariables($this->getDestination()->getEmailSubject(), $firstFileContent));
        $mail->setSubject('=?utf-8?B?' . base64_encode($this->replaceVariables($this->getDestination()->getEmailSubject(), implode("\n\n", $bodyFiles))) . '?=');

        $utilsHelper = $this->objectManager->create('\Xtento\XtCore\Helper\Utils');
        if (version_compare($utilsHelper->getMagentoVersion(), '2.2.8', '>=')) {
            // >= M2.3: ZF1 removed in M2.3
            $text = new \Zend\Mime\Part(strip_tags($this->replaceVariables($this->getDestination()->getEmailBody(), implode("\n\n", $bodyFiles))));
            $text->type = \Zend\Mime\Mime::TYPE_TEXT;
            $text->charset = 'utf-8';

            $html = new \Zend\Mime\Part(nl2br($this->replaceVariables($this->getDestination()->getEmailBody(), implode("\n\n", $bodyFiles))));
            $html->type = \Zend\Mime\Mime::TYPE_HTML;
            $html->charset = 'utf-8';

            $content  = new \Zend\Mime\Message();
            $content->setParts([$text, $html]);
            $contentPart = new \Zend\Mime\Part($content->generateMessage());
            $contentPart->type = 'multipart/alternative;' . "\r\n" . ' boundary="' . $content->getMime()->boundary() . '"';

            $messageParts = [$contentPart];
            foreach ($fileArray as $filename => $data) {
                if ($this->getDestination()->getEmailAttachFiles()) {
                    $attachment = new \Zend\Mime\Part($data);
                    $attachment->filename = $filename;
                    $attachment->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
                    $attachment->encoding = \Zend\Mime\Mime::ENCODING_BASE64;
                    array_push($messageParts, $attachment);
                }
            }

            $mimeMessage = new \Zend\Mime\Message();
            $mimeMessage->setParts($messageParts);
            $mail->setBody($mimeMessage);
        } else {
            // <=M2.2.7
            foreach ($fileArray as $filename => $data) {
                if ($this->getDestination()->getEmailAttachFiles()) {
                    $attachment = $mail->createAttachment($data);
                    $attachment->filename = $filename;
                }
            }

            $mail->setMessageType(\Magento\Framework\Mail\Message::TYPE_TEXT)
                ->setBody(strip_tags($this->replaceVariables($this->getDestination()->getEmailBody(), implode("\n\n", $bodyFiles))));
            $mail->setMessageType(\Magento\Framework\Mail\Message::TYPE_HTML)
                ->setBody(nl2br($this->replaceVariables($this->getDestination()->getEmailBody(), implode("\n\n", $bodyFiles))));
        }

        try {
            $this->objectManager->create('\Magento\Framework\Mail\TransportInterfaceFactory')->create(['message' => clone $mail])->sendMessage();
        } catch (\Exception $e) {
            $this->getTestResult()->setSuccess(false)->setMessage(__('Error while sending email: %1', $e->getMessage()));
            return false;
        }

        return $savedFiles;
    }

    protected function replaceVariables($string, $content)
    {
        $additionalVariables = $this->_registry->registry('xtento_productexport_export_variables');
        $additionalVariables = is_array($additionalVariables) ? $additionalVariables : [];

        $replaceableVariables = [
            '%d%' => date('d', $this->dateTime->gmtTimestamp()),
            '%m%' => date('m', $this->dateTime->gmtTimestamp()),
            '%y%' => date('y', $this->dateTime->gmtTimestamp()),
            '%Y%' => date('Y', $this->dateTime->gmtTimestamp()),
            '%h%' => date('H', $this->dateTime->gmtTimestamp()),
            '%i%' => date('i', $this->dateTime->gmtTimestamp()),
            '%s%' => date('s', $this->dateTime->gmtTimestamp()),
            '%exportid%' => ($this->_registry->registry('productexport_log')) ? $this->_registry->registry('productexport_log')->getId() : 0,
            '%recordcount%' => ($this->_registry->registry('productexport_log')) ? $this->_registry->registry('productexport_log')->getRecordsExported() : 0,
            '%content%' => $content,
        ];
        $string = str_replace(array_keys($replaceableVariables), array_values($replaceableVariables), $string);
        return $string;
    }
}