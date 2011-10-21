<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * Helper class for managing customizations to Zurmo. If you want to do customizations, extend this class and in
     * perInstance.php define:
     * $instanceConfig['custom']['class'] = 'path.to.your.custom.management.component.MyCustomMeasurement';
     * Then in your new component, you can override any of the methods that act as hooks.
     */
    class CustomManagement extends CApplicationComponent
    {
        /**
         * Called right before the auto build is initialized in the installation process.
         * @see InstallUtil::runInstallation
         * @param MessageLogger $messageLogger
         */
        public function runBeforeInstallationAutoBuildDatabase(MessageLogger $messageLogger)
        {
        }

        /**
         * Called right after the default data is loaded in the installation process.
         * @see InstallUtil::runInstallation
         * @param MessageLogger $messageLogger
         */
        public function runAfterInstallationDefaultDataLoad(MessageLogger $messageLogger)
        {
        }

        /**
         * Called as a begin request behavior.  This is only called during non-installation behavior. This can be used
         * as a convenience for developers to check and load any missing metadata customizations as they develop.
         */
        public function resolveIsCustomDataLoaded()
        {
        }

        /**
         * Called from ImportCommand.  Override and add calls to any import routines you would like to run.
         * @see ImportCommand
         * @param MessageLogger $messageLogger
         * @param string $importName - Optional array of specific import process to run, otherwise if empty,
         * 							    run all available import processes.
         */
        public function runImportsForImportCommand(ImportMessageLogger $messageLogger, $importName = null)
        {
            $messageLogger->addErrorMessage(Yii::t('Default', 'No import processes found.'));
            $messageLogger->addErrorMessage(Yii::t('Default', 'CustomManagement class needs to be extended.'));
        }
    }
?>
