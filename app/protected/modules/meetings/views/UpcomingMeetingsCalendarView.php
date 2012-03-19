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
     * Base class for displaying meetings on a calendar
     */
    abstract class UpcomingMeetingsCalendarView extends CalendarView
    {
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
            $metadata = array(
                'perUser' => array(
                    'title' => "eval:Yii::t('Default', 'Upcoming MeetingsModulePluralLabel', LabelUtil::getTranslationParamsForAllModules())",
                ),
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array(  'type'            => 'CreateFromRelatedListLink',
                                    'routeModuleId'   => 'eval:$this->moduleId',
                                    'routeParameters' => 'eval:$this->getCreateLinkRouteParameters()'),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        protected function makeDayEvents()
        {
            return $this->getDataProvider()->getData();
        }

        protected function makeDataProvider()
        {
            return new MeetingsCalendarDataProvider('Meeting', $this->makeSearchAttributeData());
        }

        protected function makeSearchAttributeData()
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'activityItems',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => (int)$this->params['relationModel']->getClassId('Item'),
                ),
                2 => array(
                    'attributeName'        => 'startDateTime',
                    'operatorType'         => 'greaterThan',
                    'value'                => DateTimeUtil::
                                              convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay(
                                              DateTimeUtil::getFirstDayOfThisMonthDate())
                ),
                3 => array(
                    'attributeName'        => 'startDateTime',
                    'operatorType'         => 'lessThan',
                    'value'                => DateTimeUtil::
                                              convertDateIntoTimeZoneAdjustedDateTimeEndOfDay(
                                              DateTimeUtil::getLastDayOfThisMonthDate())
                )
                );
            $searchAttributeData['structure'] = '(1 and 2 and 3)';
            return $searchAttributeData;
        }

        public static function getModuleClassName()
        {
            return 'MeetingsModule';
        }
    }
?>