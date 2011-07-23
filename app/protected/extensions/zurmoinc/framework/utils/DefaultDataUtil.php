<?php
    /**
     * Helper class to load default data for each module in the application.
     */
    class DefaultDataUtil
    {
        public static function load(& $messageLogger)
        {
            assert('$messageLogger instanceof MessageLogger');
            Yii::import('ext.framework.data.*');
            $modules = Module::getModuleObjects();
            foreach($modules as $module)
            {
                Yii::import('modules.' . $module::getDirectoryName() . '.data.*');
                $defaultDataMakerClassName = $module::getDefaultDataMakerClassName();
                if($defaultDataMakerClassName != null)
                {
                    $dataMaker = new $defaultDataMakerClassName();
                    $defaultDataMakerClassName->make();
                    $messageLogger->addInfoMessage(Yii::t('Default', 'Default data loaded for ' .
                                                   $module::getModuleLabelByTypeAndLanguage('Plural')));
                }
            }
        }
    }
?>