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
     * Utilize this element to display a dropdown of available users that are super administrators.  The key is the
     * user id and the value is the strval of the $user.
     */
    class UserToSendNotificationFromElement extends Element
    {
        /**
         * Renders the editable dropdown content.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            $dropDownArray = $this->getDropDownArray();
            $value         = $this->model->{$this->attribute};
            $htmlOptions   = array('id'   => $this->getEditableInputId($this->attribute));
            $content       = CHtml::dropDownList($this->getEditableInputName($this->attribute),
                                                 $value,
                                                 $dropDownArray,
                                                 $htmlOptions);
            $content      .= self::renderTooltipContent();
            return $content;
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        protected static function renderTooltipContent()
        {
            $title       = Yii::t('Default', 'Zurmo sends out system notifications.  The notifications must appear ' .
                                             'as coming from a super administrative user.');
            $content     = '&#160;<span id="send-notifications-from-user-tooltip" class="tooltip"  title="' . $title . '">';
            $content    .= Yii::t('Default', '?') . '</span>';
            $qtip = new ZurmoTip();
            $qtip->addQTip("#send-notifications-from-user-tooltip");
            return $content;
        }

        protected function renderError()
        {
            return null;
        }

        protected function getDropDownArray()
        {
            $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            $data  = array();
            foreach ($group->users as $user)
            {
                $data[$user->id] = strval($user);
            }
            return $data;
        }
    }
?>