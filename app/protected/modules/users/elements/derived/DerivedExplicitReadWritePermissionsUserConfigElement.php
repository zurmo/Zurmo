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