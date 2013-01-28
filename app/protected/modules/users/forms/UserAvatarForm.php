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
     * Form to edit the user avatar
     */
    class UserAvatarForm extends ModelForm
    {
        public $avatarType;
        public $customAvatarEmailAddress;

        public function __construct(User $model)
        {
            $this->model = $model;
            if (isset($this->model->serializedAvatarData))
            {
                $avatar = unserialize($this->model->serializedAvatarData);
            }
            if (isset($avatar['avatarType']))
            {
                $this->avatarType = $avatar['avatarType'];
                $this->customAvatarEmailAddress = $avatar['customAvatarEmailAddress'];
            }
            else
            {
                $this->avatarType = User::AVATAR_TYPE_DEFAULT;
            }
        }

        public function rules()
        {
            return array(
                array('customAvatarEmailAddress', 'email'),
                array('avatarType',               'validateType'),
            );
        }

        public function attributeLabels()
        {
            return array(
                'avatarType'               => Zurmo::t('UsersModule', 'Type'),
                'customAvatarEmailAddress' => Zurmo::t('UsersModule', 'Email Address'),
            );
        }

        public function validateType()
        {
            if ($this->avatarType == User::AVATAR_TYPE_CUSTOM_EMAIL && $this->customAvatarEmailAddress == null)
            {
                 $this->addError('customAvatarEmailAddress',
                    Zurmo::t('UsersModule', 'You need to choose an email address.'));
            }
        }

        public function afterValidate()
        {
            parent::afterValidate();
            $this->model->serializeAndSetAvatarData(array('avatarType' => $this->avatarType,
                                                          'customAvatarEmailAddress' => $this->customAvatarEmailAddress));
        }
    }
?>