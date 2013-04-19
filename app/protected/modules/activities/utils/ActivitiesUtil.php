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
     * Helper class for Activity module processes.
     */
    class ActivitiesUtil
    {
        /**
         * Renders and returns string content of summary content for the given model.
         * @param RedBeanModel $model
         * @param mixed $redirectUrl
         * @param string $ownedByFilter
         * @param string $viewModuleClassName
         * @return string content
         */
        public static function renderSummaryContent(RedBeanModel $model, $redirectUrl, $ownedByFilter, $viewModuleClassName)
        {
            assert('is_string($redirectUrl) || $redirectUrl == null');
            assert('is_string($ownedByFilter)');
            assert('is_string($viewModuleClassName)');
            $mashableActivityRules  = MashableActivityRulesFactory::createMashableActivityRulesByModel(get_class($model));
            $orderByAttributeName   = $mashableActivityRules->getLatestActivitiesOrderByAttributeName();
            $summaryContentTemplate = $mashableActivityRules->getSummaryContentTemplate($ownedByFilter, $viewModuleClassName);

            $content  = '<div class="activity-item">';
            //Render icon
            $content  .= '<em class="'.get_class($model).'"></em>';
            //Render date
            $content .= '<strong class="activity-date">'.DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                            $model->{$orderByAttributeName}, 'long', null) . '</strong>';

            $data                                            = array();
            $data['modelStringContent']                      = self::renderModelStringContent($model, $redirectUrl);
            $data['ownerStringContent']                      = self::renderOwnerStringContent($model);
            $data['relatedModelsByImportanceContent']        = $mashableActivityRules->renderRelatedModelsByImportanceContent($model);
            $data['extraContent']                            = self::resolveAndRenderExtraContent($model,
                                                                     $mashableActivityRules);

            //Render display content
            $content .= self::resolveContentTemplate($summaryContentTemplate, $data);
            $content .= '</div>';
            return $content;
        }

        protected static function renderModelStringContent(RedBeanModel $model, $redirectUrl)
        {
            assert('is_string($redirectUrl) || $redirectUrl == null');
            $modelDisplayString = strval($model);
            if (strlen($modelDisplayString) > 200)
            {
                $modelDisplayString = substr($modelDisplayString, 0, 200) . '...';
            }
            if (get_class($model) == 'Task')
            {
                $modelDisplayString = '<span style="text-decoration:line-through;">' . $modelDisplayString . '</span>';
            }
            $params          = array('label' => $modelDisplayString, 'redirectUrl' => $redirectUrl, 'wrapLabel' => false);
            $moduleClassName = $model->getModuleClassName();
            $moduleId        = $moduleClassName::getDirectoryName();
            $element  = new DetailsLinkActionElement('default', $moduleId, $model->id, $params);
            return $element->render();
        }

        protected static function renderOwnerStringContent($model)
        {
            if ($model instanceof OwnedSecurableItem)
            {
                return strval($model->owner);
            }
            else
            {
                return null;
            }
        }

        protected static function resolveAndRenderExtraContent(RedBeanModel $model,
                                                               MashableActivityRules $mashableActivityRules)
        {
            $content      = null;
            $extraContent = $mashableActivityRules->getLatestActivityExtraDisplayStringByModel($model);
            if ($extraContent)
            {
                $content .= '<br/>' . $extraContent;
            }
            return $content;
        }

        protected static function resolveContentTemplate($template, $data)
        {
            return MashableUtil::resolveContentTemplate($template, $data);
        }

        public static function getActivityItemsModelClassNamesDataExcludingContacts()
        {
            $metadata = Activity::getMetadata();
            $activityItemsModelClassNamesData = $metadata['Activity']['activityItemsModelClassNames'];
            foreach ($activityItemsModelClassNamesData as $index => $relationModelClassName)
            {
                if ($relationModelClassName == 'Contact')
                {
                    unset($activityItemsModelClassNamesData[$index]);
                }
            }
            return $activityItemsModelClassNamesData;
        }
    }
?>