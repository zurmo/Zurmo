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
     * Pass null for the attribute since the expected attribute name is UserStatus and the userStatus
     * object is created on the fly based on the user's explicit rights.
     */
    class DerivedUserStatusElement extends Element implements DerivedElementInterface
    {
        /**
         * Dynamically created userStatus based on the user's explicit rights.
         * @var object
         */
        private $userStatus;

        /**
         * Renders the editable dropdown content.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            $this->assertModelIsValid();
            $dropDownArray = UserStatusUtil::getStatusArray();
            $value         = UserStatusUtil::getSelectedValueByUser($this->getUserModel());
            $htmlOptions   = array('id'   => $this->getEditableInputId('userStatus'));
            $content       = CHtml::dropDownList($this->getEditableInputName('userStatus'),
                                                 $value,
                                                 $dropDownArray,
                                                 $htmlOptions);
            $content      .= self::renderTooltipContent();
            return $content;
        }

        protected function renderControlNonEditable()
        {
            $this->assertModelIsValid();
            return Yii::app()->format->text(UserStatusUtil::getSelectedValueByUser($this->getUserModel()));
        }

        protected static function renderTooltipContent()
        {
            $title       = Yii::t('Default', 'Inactive users cannot log in using the web, mobile or web API. Login for' .
                                             ' active users is controlled by group rights.');
            $content     = '&#160;<span id="user-status-tooltip" class="tooltip"  title="' . $title . '">';
            $content    .= Yii::t('Default', 'What is this?') . '</span>';

            Yii::import('application.extensions.qtip.QTip');
            $qtip = new QTip();
            $qtip->addQTip("#user-status-tooltip");
            return $content;
        }

        protected function renderError()
        {
            return null;
        }

        protected function assertModelIsValid()
        {
            assert('$this->attribute == "null"');
            assert('$this->model instanceof User ||
                   ($this->model instanceof ModelForm && $this->model->getModel() instanceof User)');
        }

        protected function getAttributeName()
        {
            return 'userStatus';
        }

        protected function renderLabel()
        {
            return $this->resolveNonActiveFormFormattedLabel($this->getFormattedAttributeLabel());
        }

        protected function getFormattedAttributeLabel()
        {
            return Yii::app()->format->text(Yii::t('Default', 'Status'));
        }


        /**
         * Method required by interface. Returns empty array since there are no real model
         * atttribute names for this element.
         */
        public static function getModelAttributeNames()
        {
            return array();
        }

        public static function getDisplayName()
        {
            return Yii::t('Default', 'Status');
        }

        protected function getUserModel()
        {
            if ($this->model instanceof User)
            {
                return $this->model;
            }
            elseif ($this->model instanceof ModelForm)
            {
                return $this->model->getModel();
            }
        }
    }
?>