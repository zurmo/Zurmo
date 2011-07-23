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
        public static function load(& $messageLogger)
        {
            assert('$messageLogger instanceof MessageLogger');
            Yii::import('ext.framework.data.*');
            $demoDataByModelClassName = array();
            $loadedModules = array();
            $modules = Module::getModuleObjects();
            foreach($modules as $module)
            {
                static::loadByModule($moduleClassName, $messageLogger, $demoDataByModelClassName);
            }
        }

        protected static function loadByModule($moduleClassName, & $messageLogger,
                                               & $demoDataByModelClassName)
        {
            assert('is_string($moduleClassName');
            assert('$messageLogger instanceof MessageLogger');
            assert('is_array($demoDataByModuleAndModelClassNames)');
            Yii::import('modules.' . $moduleClassName::getDirectoryName() . '.data.*');
            $demoDataMakerClassName = $moduleClassName::getDemoDataMakerClassName();
            if($demoDataMakerClassName != null)
            {
                $dependencies = $demoDataMakerClassName::getDependencies();
                foreach($dependencies as $dependentModuleName)
                {
                    if(!in_array($dependentModuleName, static::$loadedModules))
                    {
                        $module = Yii::app()->findModule($dependentModuleName);
                        static::loadByModule(get_class($module), $messageLogger,
                                             $demoDataByModelClassName);
                    }
                }
                $dataMaker = new $demoDataMakerClassName($moduleClassName);
                $dataMaker->makeAll($demoDataByModelClassName);
                static::$loadedModules[] = $moduleClassName;
                $messageLogger->addInfoMessage(Yii::t('Default', 'Demo data loaded for ' .
                                               $moduleClassName::getModuleLabelByTypeAndLanguage('Plural')));
            }
        }
    }
?>