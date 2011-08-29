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

    class Group extends Permitable
    {
        const EVERYONE_GROUP_NAME             = 'Everyone';
        const SUPER_ADMINISTRATORS_GROUP_NAME = 'Super Administrators';

        // Everyone and SuperAdministrators are not subtypes
        // because it introduces too much complication with the
        // RedBeanModel mapping, and the subtypes would have no
        // data. This is simply a way to identify the special
        // groups without string comparisons. It is far from
        // ideal, but having spent some time on a subclassed
        // version it is perhaps better. For further thought.
        protected $isEveryone            = false;
        protected $isSuperAdministrators = false;

        public static function getByName($name)
        {
            assert('is_string($name)');
            assert('$name != ""');
            $bean = R::findOne('_group', "name = '$name'");
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                if ($name != self::EVERYONE_GROUP_NAME &&
                    $name != self::SUPER_ADMINISTRATORS_GROUP_NAME)
                {
                    throw new NotFoundException();
                }
                $group = new Group();
                $group->unrestrictedSet('name', $name);
            }
            else
            {
                $group = self::makeModel($bean);
            }
            $group->setSpecialGroup();
            return $group;
        }

        public static function getById($id, $modelClassName = null)
        {
            $group = parent::getById($id, $modelClassName);
            $group->setSpecialGroup();
            return $group;
        }

        protected function constructDerived($bean, $setDefaults)
        {
            assert('$bean === null || $bean instanceof RedBean_OODBBean');
            assert('is_bool($setDefaults)');
            parent::constructDerived($bean, $setDefaults);
            $this->setSpecialGroup();
        }

        protected function setSpecialGroup()
        {
            $this->isEveryone            = $this->name == self::EVERYONE_GROUP_NAME;
            $this->isSuperAdministrators = $this->name == self::SUPER_ADMINISTRATORS_GROUP_NAME;
        }

        public function canGivePermissions()
        {
            return !$this->isSuperAdministrators;
        }

        public function canModifyMemberships()
        {
            return !$this->isEveryone;
        }

        public function canModifyName()
        {
            return !($this->isEveryone ||
                     $this->isSuperAdministrators);
        }

        public function canModifyRights()
        {
            return !$this->isSuperAdministrators;
        }

        public function canModifyPolicies()
        {
            return true;
        }

        public function isDeletable()
        {
            return !($this->isEveryone ||
                     $this->isSuperAdministrators);
        }

        public function contains(Permitable $permitable)
        {
            if ($this->isEveryone ||
                parent::contains($permitable))
            {
                return true;
            }
            else
            {
                if ($permitable instanceof User)
                {
                    foreach ($this->users as $user)
                    {
                        if ($user->isSame($permitable))
                        {
                            return true;
                        }
                    }
                }
                foreach ($this->groups as $group)
                {
                    if ($group->contains($permitable))
                    {
                        return true;
                    }
                }
            }
            return false;
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

        public static function mangleTableName()
        {
            return true;
        }

        protected function untranslatedAttributeLabels()
        {
            return array_merge(parent::untranslatedAttributeLabels(), array(
                'group' => 'Parent Group',
            ));
        }

        public function __get($attributeName)
        {
            if ($this->isEveryone)
            {
                if ($attributeName == 'name')
                {
                    return Yii::t('Default', self::EVERYONE_GROUP_NAME);
                }
                if ($attributeName == 'group')
                {
                    return null;
                }
                if (in_array($attributeName, array('users',
                                                   'groups')))
                {
                    throw new NotSupportedException();
                }
            }
            if ($this->isSuperAdministrators)
            {
                if ($attributeName == 'name')
                {
                    return Yii::t('Default', self::SUPER_ADMINISTRATORS_GROUP_NAME);
                }
                if ($attributeName == 'rights')
                {
                    throw new NotSupportedException();
                }
            }
            return parent::__get($attributeName);
        }

        public function __set($attributeName, $value)
        {
            if (in_array($value, array(self::EVERYONE_GROUP_NAME,
                                       self::SUPER_ADMINISTRATORS_GROUP_NAME)) ||
                $this->isEveryone &&
                    in_array($attributeName, array('name', 'group', 'users', 'groups')) ||
                $this->isSuperAdministrators &&
                    in_array($attributeName, array('name', 'rights')))
            {
                throw new NotSupportedException();
            }
            parent::__set($attributeName, $value);
        }

        public function getEffectiveRight($moduleName, $rightName)
        {
            return $this->getActualRight($moduleName, $rightName) == Right::ALLOW ? Right::ALLOW : Right::DENY;
        }

        public function getActualRight($moduleName, $rightName)
        {
            assert('is_string($moduleName)');
            assert('is_string($rightName)');
            assert('$moduleName != ""');
            assert('$rightName != ""');
            if ($this->isSuperAdministrators)
            {
                return Right::ALLOW;
            }
            if (!SECURITY_OPTIMIZED)
            {
                return parent::getActualRight($moduleName, $rightName);
            }
            else
            {
                // Optimizations work on the database,
                // anything not saved will not work.
                assert('$this->id > 0');
                return intval(ZurmoDatabaseCompatibilityUtil::
                                callFunction("get_group_actual_right({$this->id}, '$moduleName', '$rightName')"));
            }
        }

        public function getPropagatedActualAllowRight($moduleName, $rightName)
        {
            return Right::NONE;
        }

        public function getInheritedActualRight($moduleName, $rightName)
        {
            assert('is_string($moduleName)');
            assert('is_string($rightName)');
            assert('$moduleName != ""');
            assert('$rightName  != ""');
            if ($this->isEveryone)
            {
                return Right::NONE;
            }
            if (!SECURITY_OPTIMIZED)
            {
                return parent::getInheritedActualRight($moduleName, $rightName);
            }
            else
            {
                // Optimizations work on the database,
                // anything not saved will not work.
                assert('$this->id > 0');
                return intval(ZurmoDatabaseCompatibilityUtil::
                                callFunction("get_group_inherited_actual_right({$this->id}, '$moduleName', '$rightName')"));
            }
        }

        protected function getInheritedActualRightIgnoringEveryone($moduleName, $rightName)
        {
            assert('is_string($moduleName)');
            assert('is_string($rightName)');
            assert('$moduleName != ""');
            assert('$rightName  != ""');
            if (!SECURITY_OPTIMIZED)
            {
                // The slow way will remain here as documentation
                // for what the optimized way is doing.
                $combinedRight = Right::NONE;
                if ($this->group != null && $this->group->id > 0)
                {
                    $combinedRight = $this->group->getExplicitActualRight                 ($moduleName, $rightName) |
                                     $this->group->getInheritedActualRightIgnoringEveryone($moduleName, $rightName);
                }
                if (($combinedRight & Right::DENY) == Right::DENY)
                {
                    return Right::DENY;
                }
                assert('in_array($combinedRight, array(Right::NONE, Right::ALLOW))');
                return $combinedRight;
            }
            else
            {
                // It should never get here because the optimized version
                // of getInheritedActualRight will call
                // get_group_inherited_actual_right_ignoring_everyone.
                throw new NotSupportedException();
            }
        }

        public function getInheritedActualPolicy($moduleName, $policyName)
        {
            assert('is_string($moduleName)');
            assert('is_string($policyName)');
            assert('$moduleName != ""');
            assert('$policyName != ""');
            if ($this->isEveryone)
            {
                return null;
            }
            return parent::getInheritedActualPolicy($moduleName, $policyName);
        }

        public function getInheritedActualPolicyIgnoringEveryone($moduleName, $policyName)
        {
            assert('is_string($moduleName)');
            assert('is_string($policyName)');
            assert('$moduleName != ""');
            assert('$policyName != ""');
            if ($this->group != null && $this->group->id > 0 && !$this->isSame($this->group)) // Prevent cycles in database autobuild.
            {
                $value = $this->group->getExplicitActualPolicy($moduleName, $policyName);
                if ($value !== null)
                {
                    return $value;
                }
                $value = $this->group->getInheritedActualPolicyIgnoringEveryone($moduleName, $policyName);
                if ($value !== null)
                {
                    return $value;
                }
            }
            return null;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                ),
                'relations' => array(
                    'users'  => array(RedBeanModel::MANY_MANY,           'User'),
                    'group'  => array(RedBeanModel::HAS_MANY_BELONGS_TO, 'Group'),
                    'groups' => array(RedBeanModel::HAS_MANY,            'Group'),
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

        /**
         * Used to validate if the group name is a reserved word.
         * @return boolean, true if valid. false if not.
         */
        public function isNameNotAReservedName($name)
        {
            $name = strtolower($name);

            $group1 = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $group2 = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            if (($name == strtolower(Group::EVERYONE_GROUP_NAME) && $this->id != $group1->id) ||
                ($name == strtolower(Group::SUPER_ADMINISTRATORS_GROUP_NAME) && $this->id != $group2->id)
            )
            {
                $this->addError('name', Yii::t('Default', 'This name is reserved. Please pick a different name.'));
                return false;
            }
            return true;
        }

        public static function getModuleClassName()
        {
            return 'GroupsModule';
        }

        protected function afterSave()
        {
            if (((isset($this->originalAttributeValues['group'])) || $this->isNewModel) &&
                $this->group != null && $this->group->id > 0)
            {
                ReadPermissionsOptimizationUtil::groupAddedToGroup($this);
            }
            parent::afterSave();
        }

        protected function beforeSave()
        {
            if (parent::beforeSave())
            {
                if (isset($this->originalAttributeValues['group']) && $this->originalAttributeValues['group'][1] > 0)
                {
                    //copy to new object, so we can populate the old parent group as the related group.
                    //otherwise it gets passed by reference. We need the old $this->group information to properly
                    //utilize the groupBeingRemovedFromGroup method.
                    $group = unserialize(serialize($this));
                    $group->group = Group::getById($this->originalAttributeValues['group'][1]);
                    ReadPermissionsOptimizationUtil::groupBeingRemovedFromGroup($group);
                    assert('$this->originalAttributeValues["group"][1] != $this->group->id');
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
            ReadPermissionsOptimizationUtil::groupBeingDeleted($this);
        }
    }
?>
