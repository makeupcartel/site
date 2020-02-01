<?php
/**
 * Created by PhpStorm.
 * User: pp
 * Date: 9/15/17
 * Time: 01:19
 */

namespace Unirgy\SimpleUp\Console\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class BaseCommand extends Command
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_config;

    /**
     * @var \Unirgy\SimpleUp\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Unirgy\SimpleUp\Helper\Data $helper
    )
    {
        $this->_config = $config;
        parent::__construct();
        $this->helper = $helper;
    }

    /**
     * @return \Unirgy\SimpleUp\Helper\Data
     */
    protected function getSimpleUpHelper()
    {
        return $this->helper;
    }

    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected function getConfig()
    {
        return $this->_config;
    }

    /**
     * @param OutputInterface $output
     * @throws \InvalidArgumentException
     */
    protected function validateInstallation(OutputInterface $output)
    {
        if ($this->_config->getValue('usimpleup/general/check_ioncube') && !extension_loaded('ionCube Loader') && !function_exists('sg_get_const')) {
            $output->writeln('<error>ionCube or SourceGuardian loader is not installed, commercial extensions might not work.</error>');
        }
        if (!extension_loaded('zip')) {
            $output->writeln('<error>Zip PHP extension is not installed, will not be able to unpack downloaded extensions</error>');
        }
        if ($this->_config->getValue('usimpleup/ftp/active') && !extension_loaded('ftp')) {
            $output->writeln('<error>FTP PHP extension is not installed, will not be able to install extensions using FTP</error>');
        }
    }
}