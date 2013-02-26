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
     * Derived version of @see ExplicitReadWriteModelPermissionsElement.
     */
    class DerivedExplicitReadWritePermissionsUserConfigElement extends ExplicitReadWriteModelPermissionsElement
    {
        protected function assertModelIsValid()
        {
            assert('$this->model instanceof UserConfigurationForm');
            assert('$this->model->user instanceof User');
        }

        protected function getPermissionTypes()
        {
            return array(
                UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER                    => Zurmo::t('ZurmoModule', 'Owner'),
                UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER_AND_USERS_IN_GROUP => Zurmo::t('ZurmoModule', 'Owner and users in'),
                UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_EVERYONE                 => Zurmo::t('ZurmoModule', 'Everyone'));
        }

        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        /**
         * Based on the model's attribute value being a explicitReadWriteModelPermissions object,
         * resolves the selected group value if available.
         * @return string
         */
        protected function resolveSelectedGroup()
        {
            return UserConfigurationFormAdapter::resolveAndGetValue($this->model->user, 'defaultPermissionGroupSetting', false);
        }

        /**
         * Based on the model's attribute value being a explicitReadWriteModelPermissions object,
         * resolves the selected type value.
         * @return string
         */
        protected function resolveSelectedType()
        {
            return UserConfigurationFormAdapter::resolveAndGetDefaultPermissionSetting($this->model->user);
        }

        protected function getAttributeName()
        {
            return 'defaultPermissionSetting';
        }

        protected function getSelectableAttributeName()
        {
            return 'defaultPermissionGroupSetting';
        }

        protected function resolveAttributeNameAndRelatedAttributes()
        {
            return array($this->getAttributeName(), null);
        }

        protected function resolveSelectableAttributeNameAndRelatedAttributes()
        {
            return array($this->getSelectableAttributeName(), null);
        }

        protected function renderLabel()
        {
            if ($this->model === null)
            {
                throw new NotImplementedException();
            }
            return ZurmoHtml::label(Zurmo::t('ZurmoModule', 'Who can read and write - Default'), false);
        }
    }
?>