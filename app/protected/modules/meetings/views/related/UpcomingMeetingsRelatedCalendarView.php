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
     * Base class for displaying meetings on a calendar for a related model
     */
    abstract class UpcomingMeetingsRelatedCalendarView extends UpcomingMeetingsCalendarView
    {
        abstract protected function getRelationAttributeName();

        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('isset($params["controllerId"])');
            assert('isset($params["relationModuleId"])');
            assert('$params["relationModel"] instanceof RedBeanModel || $params["relationModel"] instanceof ModelForm');
            assert('isset($params["portletId"])');
            assert('$this->getRelationAttributeName() != null');
            parent::__construct($viewData, $params, $uniqueLayoutId);
        }

        /**
         * What kind of PortletRules this view follows.
         * @return PortletRulesType as string.
         */
        public static function getPortletRulesType()
        {
            return 'RelatedCalendar';
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata = array_merge($metadata, array(
                'perUser' => array(
                    'title' => "eval:Zurmo::t('MeetingsModule', 'Upcoming MeetingsModulePluralLabel', LabelUtil::getTranslationParamsForAllModules())",
                ),
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array(  'type'            => 'CreateFromRelatedListLink',
                                    'routeModuleId'   => 'eval:$this->moduleId',
                                    'routeParameters' => 'eval:$this->getCreateLinkRouteParameters()'),
                        ),
                    ),
                    'panels' => array(),
                ),
            ));
            return $metadata;
        }

        protected function getCreateLinkRouteParameters()
        {
            return array(
                'relationAttributeName' => $this->getRelationAttributeName(),
                'relationModelId'       => $this->params['relationModel']->id,
                'relationModuleId'      => $this->params['relationModuleId'],
                'redirectUrl'           => $this->params['redirectUrl'],
            );
        }

        protected function makeSearchAttributeData($stringTime = null)
        {
            assert('is_string($stringTime) || $stringTime == null');
            $searchAttributeData = parent::makeSearchAttributeData($stringTime);
            assert("count(\$searchAttributeData['clauses']) == 2");
            $searchAttributeData['clauses'][3] =
            array(
                'attributeName'        => 'activityItems',
                'relatedAttributeName' => 'id',
                'operatorType'         => 'equals',
                'value'                => (int)$this->params['relationModel']->getClassId('Item')
            );
            $searchAttributeData['structure'] = '(1 and 2 and 3)';
            return $searchAttributeData;
        }

        protected function getPortletSelectDayUrl()
        {
            return Yii::app()->createUrl('/meetings/default/daysMeetingsFromCalendarModalList',
                                                        array_merge($_GET, array(
                                                            'redirectUrl'            => $this->params['redirectUrl'],
                                                            'relationModelId'        => $this->params['relationModel']->id,
                                                            'relationModelClassName' => get_class($this->params['relationModel'])
                                                            )));
        }

        public function resolvePortletModuleId()
        {
            return $this->params['relationModuleId'];
        }
    }
?>