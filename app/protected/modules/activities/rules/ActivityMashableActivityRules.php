<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Generic rules for any model that extends the Activity class.
     */
    class ActivityMashableActivityRules extends MashableActivityRules
    {
        public function resolveSearchAttributesDataByRelatedItemId($relationItemId)
        {
            assert('is_int($relationItemId)');
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'activityItems',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $relationItemId,
                )
            );
            $searchAttributeData['structure'] = '1';
            return $this->resolveSearchAttributeDataForLatestActivities($searchAttributeData);
        }

        public function resolveSearchAttributesDataByRelatedItemIds($relationItemIds)
        {
            assert('is_array($relationItemIds)');
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'activityItems',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'oneOf',
                    'value'                => $relationItemIds,
                )
            );
            $searchAttributeData['structure'] = '1';
            return $this->resolveSearchAttributeDataForLatestActivities($searchAttributeData);
        }

        public function resolveSearchAttributeDataForLatestActivities($searchAttributeData)
        {
            assert('is_array($searchAttributeData)');
            return $searchAttributeData;
        }

        public function getLatestActivitiesOrderByAttributeName()
        {
            return 'latestDateTime';
        }

        /**
         * Override if you want to display anything extra in the view for a particular model.
         */
        public function getLatestActivityExtraDisplayStringByModel($model)
        {
        }

        /**
         * Renders related models. But only renders one type of related model given that the $model supplied
         * is connected to more than one type of activity item.  There is an order of importance that is checked
         * starting with Account, then Contact, then Opportunity. If none are found, then it grabs the first available.
         * @see getActivityItemsStringContentByModelClassName
         * @param RedBeanModel $model
         */
        public function renderRelatedModelsByImportanceContent(RedBeanModel $model)
        {
            if ($model->activityItems->count() == 0)
            {
                return;
            }
            $stringContent = self::getActivityItemsStringContentByModelClassName($model, 'Account');
            if ($stringContent != null)
            {
                return Zurmo::t('ActivitiesModule', 'for {relatedModelsStringContent}', array('{relatedModelsStringContent}' => $stringContent));
            }
            $stringContent = self::getActivityItemsStringContentByModelClassName($model, 'Contact');
            if ($stringContent != null)
            {
                return Zurmo::t('ActivitiesModule', 'with {relatedContactsStringContent}', array('{relatedContactsStringContent}' => $stringContent));
            }
            $stringContent = self::getActivityItemsStringContentByModelClassName($model, 'Opportunity');
            if ($stringContent != null)
            {
                return Zurmo::t('ActivitiesModule', 'for {relatedModelsStringContent}', array('{relatedModelsStringContent}' => $stringContent));
            }
            $metadata      = Activity::getMetadata();
            $stringContent =  self::getFirstActivityItemStringContent($metadata['Activity']['activityItemsModelClassNames'], $model);
            if ($stringContent != null)
            {
                return Zurmo::t('ActivitiesModule', 'for {relatedModelsStringContent}', array('{relatedModelsStringContent}' => $stringContent));
            }
        }

        protected static function getActivityItemsStringContentByModelClassName(RedBeanModel $model, $castDownModelClassName)
        {
            assert('is_string($castDownModelClassName)');
            $existingModels = array();
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($castDownModelClassName);
            foreach ($model->activityItems as $item)
            {
                try
                {
                    $castedDownModel = $item->castDown(array($modelDerivationPathToItem));
                    if (get_class($castedDownModel) == $castDownModelClassName)
                    {
                        if (strval($castedDownModel) != null)
                        {
                            $params          = array('label' => strval($castedDownModel), 'wrapLabel' => false);
                            $moduleClassName = $castedDownModel->getModuleClassName();
                            $moduleId        = $moduleClassName::getDirectoryName();
                            $element         = new DetailsLinkActionElement('default', $moduleId,
                                                                            $castedDownModel->id, $params);
                            $existingModels[] = $element->render();
                        }
                    }
                }
                catch (NotFoundException $e)
                {
                    //do nothing
                }
            }
            return self::resolveStringValueModelsDataToStringContent($existingModels);
        }

        protected static function getFirstActivityItemStringContent($relationModelClassNames, RedBeanModel $model)
        {
            assert('is_array($relationModelClassNames)');
            foreach ($relationModelClassNames as $relationModelClassName)
            {
                //ASSUMES ONLY A SINGLE ATTACHED ACTIVITYITEM PER RELATION TYPE.
                foreach ($model->activityItems as $item)
                {
                    try
                    {
                        $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($relationModelClassName);
                        $castedDownModel = $item->castDown(array($modelDerivationPathToItem));
                        return strval($castedDownModel);
                    }
                    catch (NotFoundException $e)
                    {
                        //do nothing
                    }
                }
            }
        }
    }
?>