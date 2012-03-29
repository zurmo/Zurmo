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
     * Class for displaying user's upcoming meetings on home page.
     */
    class MyUpcomingMeetingsCalendarView extends UpcomingMeetingsCalendarView
    {
        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('isset($params["controllerId"])');
            assert('isset($params["relationModuleId"])');
            assert('$params["relationModel"] instanceof RedBeanModel || $params["relationModel"] instanceof ModelForm');
            assert('isset($params["portletId"])');
            assert('$this->getRelationAttributeName() != null');
            parent::__construct($viewData, $params, $uniqueLayoutId);
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'perUser' => array(
                    'title' => "eval:Yii::t('Default', 'My Upcoming MeetingsModulePluralLabel', LabelUtil::getTranslationParamsForAllModules())",
                ),
                'global' => array(
                    'panels' => array(),
                ),
            );
            return $metadata;
        }

        protected function makeSearchAttributeData($timeString = null)
        {
            assert('is_string($stringTime) || $stringTime == null');
            $searchAttributeData = parent::makeSearchAttributeData($timeString);
            assert("count(\$searchAttributeData['clauses']) == 2");
            $searchAttributeData['clauses'][3] =
            array(
                'attributeName'        => 'owner',
                'operatorType'         => 'equals',
                'value'                => Yii::app()->user->userModel->id,
            );
            $searchAttributeData['structure'] = '(1 and 2 and 3)';
            return $searchAttributeData;
        }

        /**
         * Override to use myListViewAction.
         * (non-PHPdoc)
         * @see UpcomingMeetingsCalendarView::getPortletChangeMonthUrl()
         */
        protected function getPortletChangeMonthUrl()
        {
            return Yii::app()->createUrl('/' . $this->moduleId . '/defaultPortlet/myListViewAction',
                                                        array_merge($_GET, array(
                                                            'action'         => 'renderMonthEvents',
                                                            'portletId'      => $this->params['portletId'],
                                                            'uniqueLayoutId' => $this->uniqueLayoutId)));
        }
    }
?>