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
                                                                                        $relationItemIds,
                                                                                        $ownedByFilter)
        {
            assert('is_array($modelClassNames)');
            assert('is_array($relationItemIds)');
            assert('$ownedByFilter == LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL ||
                    $ownedByFilter == LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER');
            $modelClassNamesAndSearchAttributeData = array();
            foreach ($modelClassNames as $modelClassName)
            {
                $mashableActivityRules =     // Not Coding Standard
                    MashableActivityRulesFactory::createMashableActivityRulesByModel($modelClassName);
                if (count($relationItemIds) > 1)
                {
                    $searchAttributesData =     // Not Coding Standard
                        $mashableActivityRules->resolveSearchAttributesDataByRelatedItemIds($relationItemIds);
                }
                else
                {
                    $searchAttributesData =    // Not Coding Standard
                        $mashableActivityRules->resolveSearchAttributesDataByRelatedItemId($relationItemIds[0]);
                }
                static::resolveSearchAttributesDataByOwnedByFilter($searchAttributesData, $ownedByFilter);
                $modelClassNamesAndSearchAttributeData[] = array($modelClassName => $searchAttributesData);
            }
            return $modelClassNamesAndSearchAttributeData;
        }

        protected static function resolveSearchAttributesDataByOwnedByFilter(& $searchAttributesData, $ownedByFilter)
        {
            assert('is_array($searchAttributesData)');
            assert('$ownedByFilter == LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL ||
                    $ownedByFilter == LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER');
            if($ownedByFilter == LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER)
            {
                $clauseCount = count($searchAttributesData['clauses']);
                $searchAttributesData['clauses'][] = array(
                        'attributeName'        => 'owner',
                        'operatorType'         => 'equals',
                        'value'                => Yii::app()->user->userModel->id,
                );
                if($clauseCount == 0)
                {
                    $searchAttributesData = '1';
                }
                else
                {
                    $searchAttributesData['structure'] = $searchAttributesData['structure'] . ' and ' . ($clauseCount + 1);
                }
            }
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
                $mashableActivityRules =    // Not Coding Standard
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