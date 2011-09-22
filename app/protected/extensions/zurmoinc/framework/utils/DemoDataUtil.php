<?php
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
    }
?>