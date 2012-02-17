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

    class Role extends Item
    {
        public static function getByName($name)
        {
            assert('is_string($name)');
            assert('$name != ""');
            $bean = R::findOne('role', "name = '$name'");
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                throw new NotFoundException();
            }
            return self::makeModel($bean);
        }

        public function __toString()
        {
            assert('$this->name === null || is_string($this->name)');
            if ($this->name === null)
            {
                return Yii::t('Default', '(Unnamed)');
            }
            return $this->name;
        }

        protected function untranslatedAttributeLabels()
        {
            return array_merge(parent::untranslatedAttributeLabels(), array(
                'role' => 'Parent Role',
            ));
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                ),
                'relations' => array(
                    'role'  => array(RedBeanModel::HAS_MANY_BELONGS_TO, 'Role'),
                    'roles' => array(RedBeanModel::HAS_MANY,            'Role'),
                    'users' => array(RedBeanModel::HAS_MANY,            'User'),
                ),
                'rules' => array(
                    array('name', 'required'),
                    array('name', 'unique'),
                    array('name', 'type',   'type' => 'string'),
                    array('name', 'length', 'min'  => 3, 'max' => 64),
                ),
                'defaultSortAttribute' => 'name'
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getModuleClassName()
        {
            return 'RolesModule';
        }

        protected function afterSave()
        {
            if (((isset($this->originalAttributeValues['role'])) || $this->isNewModel) &&
                $this->role != null && $this->role->id > 0)
            {
                ReadPermissionsOptimizationUtil::roleParentSet($this);
            }
            parent::afterSave();
        }

        protected function beforeSave()
        {
            if (parent::beforeSave())
            {
                if (isset($this->originalAttributeValues['role']) && $this->originalAttributeValues['role'][1] > 0)
                {
                    //copy to new object, so we can populate the old parent role as the related role.
                    //otherwise it gets passed by reference. We need the old $this->role information to properly
                    //utilize the roleParentBeingRemoved method.
                    $role = unserialize(serialize($this));
                    $role->role = Role::getById($this->originalAttributeValues['role'][1]);
                    ReadPermissionsOptimizationUtil::roleParentBeingRemoved($role);
                    assert('$this->originalAttributeValues["role"][1] != $this->role->id');
                }
                return true;
            }
            else
            {
                return false;
            }
        }

        protected function beforeDelete()
        {
            parent::beforeDelete();
            ReadPermissionsOptimizationUtil::roleBeingDeleted($this);
        }

        protected function afterDelete()
        {
            PermissionsCache::forgetAll();
            RightsCache::forgetAll();
            PoliciesCache::forgetAll();
        }
    }
?>
