<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2017-11-28T11:37:18+00:00
 * File:          app/code/Xtento/ProductExport/Helper/Xsl.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Xsl extends \Magento\Framework\App\Helper\AbstractHelper
{
    // IMPORTANT: Remember to add your custom function into allowed functions in etc/xtento/productexport_settings.xml!

    // Static functions which can be called in the XSL Template, example:
    // <xsl:value-of select="php:functionString('Xtento\ProductExport\Helper\Xsl::mb_sprintf', '%015.0f', grand_total)"/>

    /**
     * Get value from store config ("scope config")
     *
     * @param $path
     * @param string $scopeType (Other example: \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
     * @param null $scopeCode
     *
     * @return mixed
     */
    public static function getStoreConfig($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        // Needs to use the object manager as this is a static function (which is required for XSL)
        /** @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */
        $scopeConfig = ObjectManager::getInstance()->create('\Magento\Framework\App\Config\ScopeConfigInterface');
        return $scopeConfig->getValue($path, $scopeType, $scopeCode);
    }

    // Resize image
    // Sample: <xsl:value-of select="php:functionString('Xtento\ProductExport\Helper\Xsl::resizeImage', entity_id, store_id, image_raw, 100, 100)"/>
    public static function resizeImage($productId, $storeId = null, $imageFile, $width, $height = null)
    {
        $productRepository = ObjectManager::getInstance()->create('\Magento\Catalog\Api\ProductRepositoryInterface');
        try {
            $product = $productRepository->getById($productId, false, $storeId);
            if ($product->getId()) {
                return ObjectManager::getInstance()->get('\Magento\Catalog\Helper\Image')->init($product, 'product_page_image_small')->setImageFile($imageFile)->resize($width, $height)->getUrl();
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return "";
        }
        return "";
    }

    // Get product price for customer group
    public static function getProductPriceForCustomerGroup($productId, $customerGroupId, $storeId = null)
    {
        $groupPrice = 0;
        // Needs to use the object manager as this is a static function (which is required for XSL)
        $productRepository = ObjectManager::getInstance()->create('\Magento\Catalog\Api\ProductRepositoryInterface');
        try {
            $product = $productRepository->getById($productId, false, $storeId);
            if ($product->getId()) {
                $product->setCustomerGroupId($customerGroupId);
                $groupPrice = $product->getPriceModel()->getFinalPrice(1, $product);
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return -1;
        }
        return (float)$groupPrice;
    }

    public static function currencyByStore($value, $store = null, $format = true)
    {
        $pricingHelper = ObjectManager::getInstance()->create('\Magento\Framework\Pricing\Helper\Data');
        return $pricingHelper->currencyByStore($value, $store, $format, false);
    }

    /**
     * @param $value
     * @param $fromCurrency
     * @param null $toCurrency
     * @return mixed
     *
     * If this function returns a blank page/errors out, the currency rate doesn't exist in Magento probably.
     */
    public static function currencyConvert($value, $fromCurrency, $toCurrency = null)
    {
        $pricingHelper = ObjectManager::getInstance()->create('\Magento\Directory\Helper\Data');
        return $pricingHelper->currencyConvert($value, $fromCurrency, $toCurrency);
    }

    /**
     * sprintf with multibyte support
     *
     * @param $format
     *
     * @return string
     */
    public static function mb_sprintf($format)
    {
        $argv = func_get_args();
        array_shift($argv);
        return self::mb_vsprintf($format, $argv);
    }

    /**
     * Works with all encodings in format and arguments.
     * Supported: Sign, padding, alignment, width and precision.
     * Not supported: Argument swapping.
     *
     * @param $format
     * @param $argv
     * @param string $encoding
     *
     * @return string
     */
    public static function mb_vsprintf($format, $argv, $encoding = "UTF-8")
    {
        if (isset($argv[0]) && (is_numeric($argv[0]) || is_float($argv[0]))) {
            return vsprintf($format, $argv);
        }

        if (is_null($encoding))
            $encoding = mb_internal_encoding();

        // Use UTF-8 in the format so we can use the u flag in preg_split
        $format = mb_convert_encoding($format, 'UTF-8', $encoding);

        $newformat = ""; // build a new format in UTF-8
        $newargv = []; // unhandled args in unchanged encoding

        while ($format !== "") {

            // Split the format in two parts: $pre and $post by the first %-directive
            // We get also the matched groups
            list ($pre, $sign, $filler, $align, $size, $precision, $type, $post) =
                preg_split("!\%(\+?)('.|[0 ]|)(-?)([1-9][0-9]*|)(\.[1-9][0-9]*|)([%a-zA-Z])!u",
                    $format, 2, PREG_SPLIT_DELIM_CAPTURE);

            $newformat .= mb_convert_encoding($pre, $encoding, 'UTF-8');

            if ($type == '') {
                // didn't match. do nothing. this is the last iteration.
            } elseif ($type == '%') {
                // an escaped %
                $newformat .= '%%';
            } elseif ($type == 's') {
                $arg = array_shift($argv);
                $arg = mb_convert_encoding($arg, 'UTF-8', $encoding);
                $padding_pre = '';
                $padding_post = '';

                // truncate $arg
                if ($precision !== '') {
                    $precision = intval(substr($precision, 1));
                    if ($precision > 0 && mb_strlen($arg, $encoding) > $precision)
                        $arg = mb_substr($arg, 0, $precision, $encoding);
                }

                // define padding
                if ($size > 0) {
                    $arglen = mb_strlen($arg, $encoding);
                    if ($arglen < $size) {
                        if ($filler === '')
                            $filler = ' ';
                        if ($align == '-')
                            $padding_post = str_repeat($filler, $size - $arglen);
                        else
                            $padding_pre = str_repeat($filler, $size - $arglen);
                    }
                }

                // escape % and pass it forward
                $newformat .= $padding_pre . str_replace('%', '%%', $arg) . $padding_post;
            } else {
                // another type, pass forward
                $newformat .= "%$sign$filler$align$size$precision$type";
                $newargv[] = array_shift($argv);
            }
            $format = strval($post);
        }
        // Convert new format back from UTF-8 to the original encoding
        $newformat = mb_convert_encoding($newformat, $encoding, 'UTF-8');
        return vsprintf($newformat, $newargv);
    }
}