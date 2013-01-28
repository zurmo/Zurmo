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
     * Specific rules for the mission model.
     */
    class MissionMashableActivityRules extends MashableActivityRules
    {
        public function resolveSearchAttributesDataByRelatedItemId($relationItemId)
        {
            return array();
        }

        public function resolveSearchAttributesDataByRelatedItemIds($relationItemIds)
        {
            return array();
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
         * (non-PHPdoc)
         * @see MashableActivityRules::getSummaryContentTemplate()
         */
        public function getSummaryContentTemplate($ownedByFilter, $viewModuleClassName)
        {
            assert('is_string($ownedByFilter)');
            assert('is_string($viewModuleClassName)');
            if ($ownedByFilter != LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER &&
               $viewModuleClassName != 'UsersModule')
            {
                if ($viewModuleClassName == 'HomeModule')
                {
                    return "<span>{modelStringContent}</span><br/>" .
                           "<span class='less-pronounced-text'>" .
                           Zurmo::t('MissionsModule', 'created by {ownerStringContent}') . "</span>" .
                           "<span>{extraContent}</span>";
                }
                else
                {
                    return "<span>{modelStringContent} </span><span class='less-pronounced-text'>" .
                           Zurmo::t('MissionsModule', 'created by {ownerStringContent}') . "</span><span>{extraContent}</span>";
                }
            }
            else
            {
                if ($viewModuleClassName == 'HomeModule' || $viewModuleClassName == 'UsersModule')
                {
                    return "<span>{modelStringContent}</span><br/><span>{extraContent}</span>";
                }
                else
                {
                    return "<span>{modelStringContent}</span>";
                }
            }
        }

        /**
         * (non-PHPdoc)
         * @see MashableActivityRules::getLatestActivityExtraDisplayStringByModel()
         */
        public function getLatestActivityExtraDisplayStringByModel($model)
        {
            assert('$model instanceof Mission');
            $content      = null;
            $filesContent =  FileModelDisplayUtil::renderFileDataDetailsWithDownloadLinksContent($model, 'files');
            if ($filesContent != null)
            {
                if ($content != null)
                {
                    $content .= '<br/>';
                }
                $content .= $filesContent;
            }
            return $content;
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
            $searchAttributesData['clauses'][] = array(
                    'attributeName'        => 'takenByUser',
                    'operatorType'         => 'equals',
                    'value'                => $userId,
            );
            if ($clauseCount == 0)
            {
                $searchAttributesData['structure'] = '0';
            }
            else
            {
                $searchAttributesData['structure'] = $searchAttributesData['structure'] .
                ' and (' . ($clauseCount + 1) . ' or ' . ($clauseCount + 2) . ')';
            }
        }
    }
?>