<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2017-07-11T13:03:08+00:00
 * File:          app/code/Xtento/ProductExport/Console/Command/ExportCommand.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Console\Command;

use Magento\Framework\App\State as AppState;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{
    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var \Xtento\ProductExport\Model\ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var \Xtento\ProductExport\Model\ExportFactory
     */
    protected $exportFactory;

    /**
     * ExportCommand constructor.
     *
     * @param \Xtento\ProductExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\ProductExport\Model\ExportFactory $exportFactory
     */
    public function __construct(
        AppState $appState,
        \Xtento\ProductExport\Model\ProfileFactory $profileFactory,
        \Xtento\ProductExport\Model\ExportFactory $exportFactory
    ) {
        $this->appState = $appState;
        $this->profileFactory = $profileFactory;
        $this->exportFactory = $exportFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('xtento:productexport:export')
            ->setDescription('Export XTENTO product export profile.')
            ->setDefinition(
                [
                    new InputArgument(
                        'profile',
                        InputArgument::REQUIRED,
                        'Profile IDs to export (multiple IDs: comma-separated)'
                    ),
                    new InputArgument(
                        'from',
                        InputArgument::OPTIONAL,
                        'Export filters: entity_id greater/equals than from value'
                    ),
                    new InputArgument(
                        'to',
                        InputArgument::OPTIONAL,
                        'Export filters: entity_id lower/equals than to value'
                    )
                ]
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode('adminhtml');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // intentionally left empty
        }
        echo(sprintf("[Debug] App Area: %s\n", $this->appState->getAreaCode())); // Required to avoid "area code not set" error

        $profileIds = explode(",", $input->getArgument('profile'));
        if (empty($profileIds)) {
            $output->writeln("<error>Profile IDs to export missing.</error>");
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        foreach ($profileIds as $profileId) {
            $profileId = intval($profileId);
            if ($profileId < 1) {
                $output->writeln("<error>Invalid profile ID: %s</error>", $profileId);
                continue;
            }

            try {
                $profile = $this->profileFactory->create()->load($profileId);
                if (!$profile->getId()) {
                    $output->writeln(sprintf("<error>Profile ID %d does not exist.</error>", $profileId));
                    continue;
                }
                if (!$profile->getEnabled()) {
                    $output->writeln(sprintf("<error>Profile ID %d is disabled.</error>", $profileId));
                    continue;
                }

                $output->writeln(sprintf("<info>Exporting profile ID %d.</info>", $profileId));
                $exportModel = $this->exportFactory->create()->setProfile($profile);
                // Prepare filters
                $filters = [];
                if ($input->getArgument('from') > 0) {
                    array_push($filters, ['entity_id' => [['from' => intval($input->getArgument('from'))]]]);
                }
                if ($input->getArgument('to') > 0) {
                    if (empty($filters)) {
                        array_push($filters, ['entity_id' => [['to' => intval($input->getArgument('to'))]]]);
                    } else {
                        $filters[0]['entity_id'][0]['to'] = intval($input->getArgument('to'));
                    }
                }
                // Export
                $exportModel->cronExport($filters);
                $output->writeln(sprintf('<info>Export for profile ID %d completed. Check "Execution Log" for detailed results.</info>', $profileId));
            } catch (\Exception $exception) {
                $output->writeln(sprintf("<error>Exception for profile ID %d: %s</error>", $profileId, $exception->getMessage()));
                continue;
            }
        }
        $output->writeln("<info>Finished command.</info>");
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
