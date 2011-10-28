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
            return $this->resolveNonActiveFormFormattedLabel(
                   Yii::app()->format->text(Yii::t('Default', 'Who can read and write')));
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
            return Yii::t('Default', 'Who can read and write');
        }
    }
?>