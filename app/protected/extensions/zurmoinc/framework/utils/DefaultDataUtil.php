<?php
    /**
     * Helper class to load default data for each module in the application.
     */
    class DefaultDataUtil
    {
        public static function load(& $messageLogger)
        {
            assert('$messageLogger instanceof MessageLogger');
            Yii::import('application.extensions.zurmoinc.framework.data.*');
            $modules = Module::getModuleObjects();
            foreach($modules as $module)
            {
                $parentModule = $module->getParentModule();
                if($parentModule != null)
                {
                    Yii::import('application.modules.' . $parentModule::getDirectoryName() . '.data.*');
                }
                else
                {
                    Yii::import('application.modules.' . $module::getDirectoryName() . '.data.*');
                }
                $defaultDataMakerClassName = $module::getDefaultDataMakerClassName();
                if($defaultDataMakerClassName != null)
                {
                    $dataMaker = new $defaultDataMakerClassName();
                    $dataMaker->make();
                    $messageLogger->addInfoMessage(Yii::t('Default', 'Default data loaded for ' .
                                                   $module::getModuleLabelByTypeAndLanguage('Plural')));
                }
            }
        }
    }
?>