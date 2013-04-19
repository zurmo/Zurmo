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
     * Class to help interacting with classes that implement the MashableActivityInterface.
     * An example is the latest activity view which contains a mashable activity feed for things that have occured
     * in the past.  Either related to a specific model or generically across the entire system.
     */
    abstract class MashableActivityRules
    {
        /**
         * Given an item id of a related model, make a searchAttributesData array that includes that item id as a
         * filter for the query.
         * @param integer $relationItemId
         */
        abstract public function resolveSearchAttributesDataByRelatedItemId($relationItemId);

        /**
         * Given multiple item ids of related models, make a searchAttributesData array that includes these item ids
         * as filters for the query.
         * @param array $relationItemIds
         */
        abstract public function resolveSearchAttributesDataByRelatedItemIds($relationItemIds);

        /**
         * Given a searchAttributeData array, add any extra filtering needed for the query based on the model specific
         * requirements for what should be shown in a mashable activity feed.
         * @param array $searchAttributeData
         */
        abstract public function resolveSearchAttributeDataForLatestActivities($searchAttributeData);

        /**
         * For a given model, what attribute is used for the ordering in a latest activity feed.
         */
        abstract public function getLatestActivitiesOrderByAttributeName();

        /**
         * Override if you want to display anything extra in the view for a particular model.
         */
        abstract public function getLatestActivityExtraDisplayStringByModel($model);

        /**
         * Override to define how related models are displayed if at all.
         * @param RedBeanModel $model
         */
        public function renderRelatedModelsByImportanceContent(RedBeanModel $model)
        {
        }

        /**
         * Override to customize summary content.
         * @param string $ownedByFilter
         * @param string $viewModuleClassName
         */
        public function getSummaryContentTemplate($ownedByFilter, $viewModuleClassName)
        {
            assert('is_string($ownedByFilter)');
            assert('is_string($viewModuleClassName)');
            if ($ownedByFilter != LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER)
            {
                return "<span>{modelStringContent}</span><span class='less-pronounced-text'>" .
                       Zurmo::t('ZurmoModule', 'by {ownerStringContent}') . "</span><span>{extraContent}</span>";
            }
            else
            {
                return "<span>{modelStringContent}</span><span>{extraContent}</span>";
            }
        }

        protected static function resolveStringValueModelsDataToStringContent($modelsAndStringData)
        {
            assert('is_array($modelsAndStringData)');
            $content = null;
            foreach ($modelsAndStringData as $modelStringContent)
            {
                if ($content != null)
                {
                    $content .= ', ';
                }
                $content .= $modelStringContent;
            }
            return $content;
        }

        public static function resolveSearchAttributesDataByOwnedByFilter(& $searchAttributesData, $ownedByFilter)
        {
            assert('is_array($searchAttributesData)');
            assert('$ownedByFilter == LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL ||
                    $ownedByFilter == LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER ||
                    is_int($ownedByFilter)');
            if ($ownedByFilter == LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER || is_int($ownedByFilter))
            {
                if (is_int($ownedByFilter))
                {
                    $userId = $ownedByFilter;
                }
                else
                {
                    $userId = Yii::app()->user->userModel->id;
                }
                static::resolveSearchAttributesDataByOwnedByFilterClauses($searchAttributesData, $userId);
            }
        }

        protected static function resolveSearchAttributesDataByOwnedByFilterClauses(& $searchAttributesData, $userId)
        {
            assert('is_array($searchAttributesData)');
            assert('is_int($userId)');
            $clauseCount = count($searchAttributesData['clauses']);
            $searchAttributesData['clauses'][] = array(
                    'attributeName'        => 'owner',
                    'operatorType'         => 'equals',
                    'value'                => $userId,
            );
            if ($clauseCount == 0)
            {
                $searchAttributesData['structure'] = '0';
            }
            else
            {
                $searchAttributesData['structure'] = $searchAttributesData['structure'] . ' and ' . ($clauseCount + 1);
            }
        }
    }
?>