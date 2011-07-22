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
     * Read only element used to display the text
     * for the password expiry in days.
     */
    class PolicyEffectivePasswordExpiryElement extends TextElement implements DerivedElementInterface
    {
        protected function renderEditable()
        {
            throw new NotSupportedException();
        }

        /**
         * Generate the element label content. Override
         * to always for non-editable label
         * @return A string containing the element's label
         */
        protected function renderLabel()
        {
            return Yii::t('Default', UsersModule::POLICY_PASSWORD_EXPIRES);
        }

        /**
         * Renders a message.
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            $content      = '';
            $expiresValue = $this->model->{$this->getExpiresAttributeName()};
            if ($expiresValue == Policy::YES)
            {
                $expiryValue = $this->model->{$this->getExpiryAttributeName()};
                $content    .= Yii::app()->format->text(yii::t('Default', 'Yes'));
                $content    .= ',&#160;';
                $content    .= Yii::app()->format->text(yii::t('Default', 'every'));
                $content    .= '&#160;';
                $content    .= $expiryValue;
                $content    .= '&#160;';
                $content    .= Yii::app()->format->text(yii::t('Default', 'day(s)'));
            }
            elseif ($expiresValue == Policy::NO || $expiresValue == null)
            {
                $content    .= Yii::app()->format->text(yii::t('Default', 'No'));
            }
            else
            {
                throw new NotSupportedException();
            }
            return $content;
        }

        public static function isReadOnly()
        {
            return true;
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element.
         * @return array of model attributeNames used.
         */
        public static function getModelAttributeNames()
        {
            return array(
                self::getExpiresAttributeName(),
                self::getExpiryAttributeName()
            );
        }

        protected static function getExpiresAttributeName()
        {
            return FormModelUtil::getDerivedAttributeNameFromTwoStrings(
                'UsersModule',
                'POLICY_PASSWORD_EXPIRES' . FormModelUtil::DELIMITER . 'effective');
        }

        protected static function getExpiryAttributeName()
        {
            return FormModelUtil::getDerivedAttributeNameFromTwoStrings(
                'UsersModule',
                'POLICY_PASSWORD_EXPIRY_DAYS' . FormModelUtil::DELIMITER . 'effective');
        }
    }
?>