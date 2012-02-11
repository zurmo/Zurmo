<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * Helper class to load demo data for each module in the application.
     */
    class DemoDataUtil
    {
        protected static $loadedModules = array();

        /**
         * Loads all module demo data based on a dependency tree loading the dependent module data first.
         * @param object $messageLogger
         */
        public static function load(& $messageLogger, $loadMagnitude = null)
        {
            assert('$messageLogger instanceof MessageLogger');
            assert('$loadMagnitude == null || is_int($loadMagnitude)');
            Yii::import('application.extensions.zurmoinc.framework.data.*');
            $loadedModules = array();
            $demoDataHelper = new DemoDataHelper();
            $modules = Module::getModuleObjects();

            foreach ($modules as $module)
            {
                static::loadByModule($module, $messageLogger, $demoDataHelper, $loadMagnitude);
            }
        }

        protected static function loadByModule($module, & $messageLogger,
                                               & $demoDataHelper, $loadMagnitude = null)
        {
            assert('$module instanceof Module');
            assert('$messageLogger instanceof MessageLogger');
            assert('$demoDataHelper instanceof DemoDataHelper');
            assert('$loadMagnitude == null || is_int($loadMagnitude)');
            $parentModule = $module->getParentModule();
            if ($parentModule != null)
            {
                Yii::import('application.modules.' . $parentModule::getDirectoryName() . '.data.*');
            }
            else
            {
                Yii::import('application.modules.' . $module::getDirectoryName() . '.data.*');
            }
            $demoDataMakerClassName = $module::getDemoDataMakerClassName();
            if ($demoDataMakerClassName != null && !in_array($module->getName(), static::$loadedModules))
            {
                $dependencies = $demoDataMakerClassName::getDependencies();
                foreach ($dependencies as $dependentModuleName)
                {
                    if (!in_array($dependentModuleName, static::$loadedModules))
                    {
                        $dependentModule       = Yii::app()->findModule($dependentModuleName);
                        static::loadByModule($dependentModule, $messageLogger,
                                             $demoDataHelper, $loadMagnitude);
                    }
                }
                $dataMaker = new $demoDataMakerClassName(get_class($module));
                if ($loadMagnitude != null)
                {
                    $dataMaker->setLoadMagnitude($loadMagnitude);
                }
                $dataMaker->makeAll($demoDataHelper);
                static::$loadedModules[] = $module->getName();
                $messageLogger->addInfoMessage(Yii::t('Default', 'Demo data loaded for ' .
                                               $module::getModuleLabelByTypeAndLanguage('Plural')));
            }
        }

        public static function unsetLoadedModules()
        {
            static::$loadedModules = array();
        }
    }
?>