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
     * Derived version of @see ExplicitReadWriteModelPermissionsElement.  Pass null for the attribute since the
     * expected attribute name is explicitReadWriteModelPermissions and the ExplicitReadWriteModelPermissions
     * object is created on the fly based on the SecurableItem model data.
     */
    class DerivedExplicitReadWriteModelPermissionsElement extends ExplicitReadWriteModelPermissionsElement
    implements DerivedElementInterface
    {
        /**
         * Dynamically created ExplicitReadWriteModelPermissions based on the model data.
         * @var object
         */
        private $explicitReadWriteModelPermissions;

        protected function assertModelIsValid()
        {
            parent::assertModelIsValid();
            assert('$this->attribute == "null"');
            assert('$this->model instanceof SecurableItem');
        }

        protected function getExplicitReadWriteModelPermissions()
        {
            if ($this->explicitReadWriteModelPermissions != null)
            {
                return $this->explicitReadWriteModelPermissions;
            }
            $this->explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                       makeBySecurableItem($this->model);
            return $this->explicitReadWriteModelPermissions;
        }

        protected function getAttributeName()
        {
            return 'explicitReadWriteModelPermissions';
        }

        /**
         * Override to provide the no form version.
         * @return A string containing the element's label
         */
        protected function renderLabel()
        {
            return $this->resolveNonActiveFormFormattedLabel($this->getFormattedAttributeLabel());
        }

        protected function getFormattedAttributeLabel()
        {
            return Yii::app()->format->text(Zurmo::t('ZurmoModule', 'Who can read and write'));
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
            return Zurmo::t('ZurmoModule', 'Who can read and write');
        }

        /**
         * Based on the model's attribute value being a explicitReadWriteModelPermissions object,
         * resolves the selected type value.
         * @return string
         */
        protected function resolveSelectedType()
        {
            if (!$this->isModelCreateAction() || $this->model->isCopied())
            {
                return parent::resolveSelectedType();
            }
            $selectedType = UserConfigurationFormAdapter::resolveAndGetDefaultPermissionSetting(
                                                                                        Yii::app()->user->userModel);
            if (null == $selectedType)
            {
                return parent::resolveSelectedType();
            }
            else
            {
                return $this->resolveUserPermissionConfigurationToPermissionType($selectedType);
            }
        }

        /**
         * Based on the model's attribute value being a explicitReadWriteModelPermissions object,
         * resolves the selected group value if available.
         * @return string
         */
        protected function resolveSelectedGroup()
        {
            if (!$this->isModelCreateAction()|| $this->model->isCopied())
            {
                return parent::resolveSelectedGroup();
            }
            if (null != $selectedGroup = UserConfigurationFormAdapter::resolveAndGetValue(Yii::app()->user->userModel,
                'defaultPermissionGroupSetting', false))
            {
                return $selectedGroup;
            }
            else
            {
                return parent::resolveSelectedGroup();
            }
        }

        /**
         * Converts User's configuration of selected type to ExplicitReadWriteModelPermissionsElement's compatible
         * @param $selectedType Selected Type index from User's Configuration
         * @return $selectedTypeIndex Selected Type Index converted to ExplicitReadWriteModelPermissionsElement::getPermissionTypes() compatible format
         */
        protected function resolveUserPermissionConfigurationToPermissionType($selectedType)
        {
            assert('is_int($selectedType)');
            assert('$selectedType >= UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER');
            assert('$selectedType <= UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_EVERYONE');
            $userConfigPermissionTypes          = UserConfigurationForm::getAllDefaultPermissionTypes();
            $explicitReadWritePermissionTypes   = parent::getPermissionTypes();
            return array_search($userConfigPermissionTypes[$selectedType], $explicitReadWritePermissionTypes);
        }

        protected function isModelCreateAction()
        {
            return ($this->model->id <= 0);
        }
    }
?>