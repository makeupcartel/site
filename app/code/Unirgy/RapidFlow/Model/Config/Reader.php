<?php

/**
 * Created by pp
 * @project magento2
 */

namespace Unirgy\RapidFlow\Model\Config;

use Magento\Framework\App\Config\Element;
use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Config\DomFactory;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Exception\LocalizedException;

class Reader
{
    protected $_fileResolver;

    protected $_converter;

    protected $domFactory;

    protected $_fileName;

    /**
     * @param FileResolverInterface $fileResolver
     * @param string $elementClass
     * @param string $fileName
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        $elementClass = 'Magento\Framework\App\Config\Element',
        $fileName = 'urapidflow.xml'
    ) {
        $this->_fileResolver = $fileResolver;
        $this->_fileName = $fileName;
        $this->_elementClass = 'Magento\Framework\App\Config\Element';
    }

    /**
     * Read configuration scope
     *
     * @return Element
     *
     * @throws LocalizedException
     */
    public function read()
    {
        $fileList = [];
        $directories = $this->_fileResolver->get($this->_fileName, "global");
        foreach ($directories as $key => $directory) {
            $fileList[$key] = $directory;
        }

        if (!count($fileList)) {
            return [];
        }

        /** @var Element $configElement */
        $configElement = null;
        foreach ($fileList as $file) {
            try {
                if (!$configElement) {
                    $configElement = simplexml_load_string($file, $this->_elementClass);
                } else {
                    $configElement->extend(simplexml_load_string($file, $this->_elementClass), true);
                }
            } catch (\Exception $e) {
                throw new LocalizedException(
                    __("Invalid XML in file %1:\n%2", [$file, $e->getMessage()])
                );
            }
        }

//        $output = [];
//        if ($configElement) {
//            $output = $this->_converter->convert($configElement->getDom());
//            $configElement->getDom()->saveXML();
//        }
//        return $output;
        return $configElement;
    }
}
