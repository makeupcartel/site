<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Upgrade Data script
 *
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * UpgradeData constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->runUpgrade101($setup);
        }

        if (version_compare($context->getVersion(), '1.2.4', '<')) {
            $this->runUpgrade124($setup);
        }

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->runUpgrade200($setup);
        }

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->runUpgrade201($setup);
        }

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->runUpgrade210($setup);
        }

        if (version_compare($context->getVersion(), '2.2.1', '<')) {
            $this->runUpgrade221($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function runUpgrade101(ModuleDataSetupInterface $setup)
    {
        $whitelistEntries = [
            $this->getWhitelistEntryAsArray(0, 'Rest API', '/rest'),
            $this->getWhitelistEntryAsArray(0, 'Customer Account Login', '/customer/account/login'),
            $this->getWhitelistEntryAsArray(0, 'Customer Account Logout', '/customer/account/logout'),
            $this->getWhitelistEntryAsArray(0, 'Customer Account Logout Success', '/customer/account/logoutSuccess'),
            $this->getWhitelistEntryAsArray(0, 'Customer Account Create', '/customer/account/create'),
            $this->getWhitelistEntryAsArray(0, 'Customer Account Create Password', '/customer/account/createPassword'),
            $this->getWhitelistEntryAsArray(0, 'Customer Account Forgot Password', '/customer/account/forgotpassword'),
            $this->getWhitelistEntryAsArray(
                0,
                'Customer Account Forgot Password Post',
                '/customer/account/forgotpasswordpost'
            ),
            $this->getWhitelistEntryAsArray(0, 'Customer Section Load', '/customer/section/load'),
            $this->getWhitelistEntryAsArray(0, 'Contact Us', '/contact', true),
            $this->getWhitelistEntryAsArray(0, 'Help', '/help', true)
        ];

        $setup->getConnection()->insertMultiple(
            $setup->getTable('bitexpert_forcelogin_whitelist'),
            $whitelistEntries
        );
    }

    /**
     * @param int $storeId
     * @param string $label
     * @param string $urlRule
     * @param boolean $editable
     * @param string $strategy
     * @return array
     */
    private function getWhitelistEntryAsArray(
        $storeId,
        $label,
        $urlRule,
        $editable = false,
        $strategy = 'default'
    ) {
        return [
            'store_id' => $storeId,
            'label' => $label,
            'url_rule' => $urlRule,
            'editable' => $editable,
            'strategy' => $strategy
        ];
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function runUpgrade124(ModuleDataSetupInterface $setup)
    {
        $bind = [
            'editable' => true
        ];

        $urlRules = [
            '/rest',
            '/customer/account/login'
        ];

        // All routes can be editable except for rest and login page
        $where = [
            'url_rule NOT IN(?)' => $urlRules
        ];

        $setup->getConnection()->update(
            $setup->getTable('bitexpert_forcelogin_whitelist'),
            $bind,
            $where
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function runUpgrade200(ModuleDataSetupInterface $setup)
    {
        $whitelistEntries = [
            $this->getWhitelistEntryAsArray(0, 'Sitemap.xml', '/sitemap.xml', true),
            $this->getWhitelistEntryAsArray(0, 'Robots.txt', '/robots.txt', true)
        ];

        $setup->getConnection()->insertMultiple(
            $setup->getTable('bitexpert_forcelogin_whitelist'),
            $whitelistEntries
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function runUpgrade201(ModuleDataSetupInterface $setup)
    {
        $whitelistEntries = [
            $this->getWhitelistEntryAsArray(0, 'Customer Account Dashboard', '/customer/account')
        ];

        $setup->getConnection()->insertMultiple(
            $setup->getTable('bitexpert_forcelogin_whitelist'),
            $whitelistEntries
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function runUpgrade210(ModuleDataSetupInterface $setup)
    {
        $setup->getConnection()->update(
            $setup->getTable('bitexpert_forcelogin_whitelist'),
            [
                'strategy' => 'regex-all'
            ]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function runUpgrade221(ModuleDataSetupInterface $setup)
    {
        $setup->getConnection()->update(
            $setup->getTable('bitexpert_forcelogin_whitelist'),
            [
                'editable' => true
            ]
        );
    }
}
