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
     * The base calendar view.
     */
    abstract class CalendarView extends ModelView implements PortletViewInterface
    {
        protected $controllerId;

        protected $moduleId;

        protected $params;

        protected $viewData;

        protected $uniqueLayoutId;

        protected $dataProvider;

        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('isset($params["controllerId"])');
            $this->modelClassName    = $this->getModelClassName();
            $this->viewData          = $viewData;
            $this->params            = $params;
            $this->uniqueLayoutId    = $uniqueLayoutId;
            $this->controllerId      = $this->resolveControllerId();
            $this->moduleId          = $this->resolveModuleId();
        }

        /**
         * Renders content for a calendar.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $content     = $this->renderViewToolBar();
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("Calendar");
            $cClipWidget->widget('application.core.widgets.Calendar', array(
                'language'            => YiiToJqueryUIDatePickerLocalization::getLanguage(),
                'htmlOptions'         => array(
                    'id'              => 'calendar' . $this->uniqueLayoutId,
                    'name'            => 'calendar' . $this->uniqueLayoutId,
                ),
                'options'             => array(
                    'dateFormat'      => YiiToJqueryUIDatePickerLocalization::resolveDateFormat(
                                            DateTimeUtil::getLocaleDateFormat()),
                    'onChangeMonthYear'   => $this->getOnChangeMonthScript(),
                    'onSelect'            => $this->getOnSelectScript(),
                    'altFormat'           => 'yy-mm-dd',
                    'altField'            => '#calendarSelectedDate' . $this->uniqueLayoutId
                ),
                'dayEvents'           => $this->makeDayEvents(),

            ));
            $cClipWidget->endClip();
            $content .= $cClipWidget->getController()->clips['Calendar'];
            $content .= ZurmoHtml::textField('calendarSelectedDate' . $this->uniqueLayoutId,
                                         null,
                                         array('id'    => 'calendarSelectedDate' . $this->uniqueLayoutId,
                                               'style' => 'display:none;'));
            return $content;
        }

        protected function getOnChangeMonthScript()
        {
        }

        protected function getOnSelectScript()
        {
        }

        protected function makeDayEvents()
        {
            return array();
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        public function getTitle()
        {
            if (!empty($this->viewData['title']))
            {
                return $this->viewData['title'];
            }
            else
            {
                return static::getDefaultTitle();
            }
        }

        public static function getDefaultTitle()
        {
            $metadata = self::getMetadata();
            $title    = $metadata['perUser']['title'];
            MetadataUtil::resolveEvaluateSubString($title);
            return $title;
        }

        public static function canUserConfigure()
        {
            return false;
        }

        public static function getDesignerRulesType()
        {
        }

        /**
         * Override to add a display description.  An example would be 'Contacts for Account'.  This display description
         * can then be used by external classes interfacing with the view in order to display information to the user in
         * the user interface.
         */
        public static function getDisplayDescription()
        {
            return null;
        }

        public function getModelClassName()
        {
            $moduleClassName = $this->getActionModuleClassName();
            return $moduleClassName::getPrimaryModelName();
        }

        /**
         * What kind of PortletRules this view follows.
         * @return PortletRulesType as string.
         */
        public static function getPortletRulesType()
        {
            return 'Calendar';
        }

        /**
         * Controller Id for the link to models from rows in the calendar view.
         */
        private function resolveControllerId()
        {
            return 'default';
        }

        /**
         * Module Id for the link to models from rows in the calendar view.
         */
        private function resolveModuleId()
        {
            $moduleClassName = $this->getActionModuleClassName();
            return $moduleClassName::getDirectoryName();
        }

        /**
         * Module class name for models linked from rows in the calendar view.
         */
        protected function getActionModuleClassName()
        {
            $calledClass = get_called_class();
            return $calledClass::getModuleClassName();
        }

        protected function getDataProvider()
        {
            if ($this->dataProvider == null)
            {
                $this->dataProvider = $this->makeDataProvider();
            }
            return $this->dataProvider;
        }

        protected function makeDataProvider()
        {
            throw new NotImplementedException();
        }
    }
?>