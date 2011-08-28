<?php
    /**
     * Helper class for working with latest activity views.
     */
    class LatestActivitiesUtil
    {
        /**
         * Based on the current user, return model class names and thier display labels.  Only include models
         * that the user has a right to access its corresponding module, as well as only models that implement the
         * MashableActivityInterface.
         * @return array of model class names and display labels.
         */
        public static function getMashableModelDataForCurrentUser()
        {
            //todo: cache results to improve performance if needed.
            $mashableModelClassNames = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                $modelClassNames = $module::getModelClassNames();
                foreach ($modelClassNames as $modelClassName)
                {
                    $classToEvaluate     = new ReflectionClass($modelClassName);
                    if ($classToEvaluate->implementsInterface('MashableActivityInterface') &&
                    !$classToEvaluate->isAbstract())
                    {
                        if (RightsUtil::canUserAccessModule(get_class($module), Yii::app()->user->userModel))
                        {
                            $mashableModelClassNames[$modelClassName] =
                                $modelClassName::getModelLabelByTypeAndLanguage('Plural');
                        }
                    }
                }
            }
            return $mashableModelClassNames;
        }

        /**
         * Given an array of modelClassNames and relationItemIds build an array of searchAttributeData that
         * can be used by the RedBeanModelsDataProvider to produce a union query of data.
         * @param array $modelClassNames
         * @param array $relationItemIds
         * @return array $modelClassNamesAndSearchAttributeData
         */
        public static function getSearchAttributesDataByModelClassNamesAndRelatedItemIds($modelClassNames,
                                                                                        $relationItemIds)
        {
            assert('is_array($modelClassNames)');
            assert('is_array($relationItemIds)');
            $modelClassNamesAndSearchAttributeData = array();
            foreach ($modelClassNames as $modelClassName)
            {
                $mashableActivityRules =
                    MashableActivityRulesFactory::createMashableActivityRulesByModel($modelClassName);
                if (count($relationItemIds) > 1)
                {
                    $searchAttributesData =
                        $mashableActivityRules->resolveSearchAttributesDataByRelatedItemIds($relationItemIds);
                }
                else
                {
                    $searchAttributesData =
                        $mashableActivityRules->resolveSearchAttributesDataByRelatedItemId($relationItemIds[0]);
                }
                $modelClassNamesAndSearchAttributeData[$modelClassName] = $searchAttributesData;
            }
            return $modelClassNamesAndSearchAttributeData;
        }

        /**
         * Given an array of modelClassNames build an array of sortAttributeData that
         * can be used by the RedBeanModelsDataProvider to produce a union query of data.
         * @param array $modelClassNames
         * @return array $modelClassNamesAndSortAttributes
         */
        public static function getSortAttributesByMashableModelClassNames($modelClassNames)
        {
            $modelClassNamesAndSortAttributes = array();
            foreach ($modelClassNames as $modelClassName)
            {
                $mashableActivityRules =
                    MashableActivityRulesFactory::createMashableActivityRulesByModel($modelClassName);
                $modelClassNamesAndSortAttributes[$modelClassName] =
                    $mashableActivityRules->getLatestActivitiesOrderByAttributeName();
            }
            return $modelClassNamesAndSortAttributes;
        }

        /**
         * Given an array of $mashableModelClassNames, filter out and return that array based on the $filteredByModelName
         * value.  If $filteredByModelName is set to LatestActivitiesConfigurationForm::FILTERED_BY_ALL then the array
         * will be returned as it was passed in, otherwise filter the array to a specific model.
         * @param array $mashableModelClassNames
         * @param string $filteredByModelName
         * @return array of filtered $mashableModelClassNames
         */
        public static function resolveMashableModelClassNamesByFilteredBy($mashableModelClassNames, $filteredByModelName)
        {
            assert('is_array($mashableModelClassNames)');
            assert('is_string($filteredByModelName) || $filteredByModelName == null');
            foreach ($mashableModelClassNames as $index => $modelClassName)
            {
                if ( $filteredByModelName != LatestActivitiesConfigurationForm::FILTERED_BY_ALL &&
                    $filteredByModelName != $modelClassName)
                {
                    unset($mashableModelClassNames[$index]);
                }
            }
            return array_values($mashableModelClassNames);
        }
    }
?>