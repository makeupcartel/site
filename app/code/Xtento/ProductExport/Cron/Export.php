<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-07-21T11:31:49+00:00
 * File:          app/code/Xtento/ProductExport/Cron/Export.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Cron;

use Magento\Framework\Exception\LocalizedException;

class Export extends \Xtento\ProductExport\Model\AbstractAutomaticExport
{
    /**
     * Run automatic export, dispatched by Magento cron scheduler
     *
     * @param $schedule
     */
    public function execute($schedule)
    {
        try {
            if (!$this->moduleHelper->isModuleEnabled() || !$this->moduleHelper->isModuleProperlyInstalled()) {
                $this->xtentoLogger->info('Cronjob executed, but module is disabled or not installed properly. Stopping.');
                return;
            }
            if (!$schedule) {
                $this->xtentoLogger->info('Cronjob executed, but no schedule is defined for cron. Stopping.');
                return;
            }
            $jobCode = $schedule->getJobCode();
            preg_match('/profile_(\d+)/', $jobCode, $jobMatch);
            if (!isset($jobMatch[1])) {
                throw new LocalizedException(__('No profile ID found in job_code.'));
            }
            $profileId = $jobMatch[1];
            $profile = $this->profileFactory->create()->load($profileId);
            if (!$profile->getId()) {
                // Remove existing cronjobs
                $this->cronHelper->removeCronjobsLike('productexport_profile_' . $profileId . '_%');
                throw new LocalizedException(__('Profile ID %1 does not seem to exist anymore.', $profileId));
            }
            if (!$profile->getEnabled()) {
                return; // Profile not enabled
            }
            if (!$profile->getCronjobEnabled()) {
                return; // Cronjob not enabled
            }
            $exportModel = $this->exportFactory->create()->setProfile($profile);
            $filters = $this->addProfileFilters($profile);
            $exportModel->cronExport($filters);
        } catch (\Exception $e) {
            $this->xtentoLogger->critical('Cronjob exception for job_code ' . $jobCode . ': ' . $e->getMessage());
        }
    }
}
