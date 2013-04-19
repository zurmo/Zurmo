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

    class SecurableItem extends Item
    {
        public function getEffectivePermissions($permitable = null)
        {
            list($allowPermissions, $denyPermissions) = $this->getActualPermissions($permitable);
            $permissions = $allowPermissions & ~$denyPermissions;
            assert("($permissions & ~Permission::ALL) == 0");
            return $permissions;
        }

        public function getActualPermissions($permitable = null)
        {
            assert('$permitable === null || $permitable instanceof Permitable');
            if ($permitable === null)
            {
                $permitable = Yii::app()->user->userModel;
                if (!$permitable instanceof User)
                {
                    throw new NoCurrentUserSecurityException();
                }
            }
            if (!SECURITY_OPTIMIZED)
            {
                // The slow way will remain here as documentation
                // for what the optimized way is doing.
                $allowPermissions = Permission::NONE;
                $denyPermissions  = Permission::NONE;
                if (Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME)->contains($permitable))
                {
                    $allowPermissions = Permission::ALL;
                }
                else
                {
                    foreach ($this->unrestrictedGet('permissions') as $permission)
                    {
                        $effectivePermissions = $permission->getEffectivePermissions($permitable);
                        if ($permission->type == Permission::ALLOW)
                        {
                            $allowPermissions |= $effectivePermissions;
                        }
                        else
                        {
                            $denyPermissions  |= $effectivePermissions;
                        }
                    }
                    $allowPermissions |= $this->getPropagatedActualAllowPermissions($permitable);
                    if (!($this instanceof NamedSecurableItem))
                    {
                        foreach (array(get_class($this), static::getModuleClassName()) as $securableItemName)
                        {
                            try
                            {
                                $securableType = NamedSecurableItem::getByName($securableItemName);
                                $typeAllowPermissions = Permission::NONE;
                                $typeDenyPermissions  = Permission::NONE;
                                foreach ($securableType->unrestrictedGet('permissions') as $permission)
                                {
                                    $effectivePermissions = $permission->getEffectivePermissions($permitable);
                                    if ($permission->type == Permission::ALLOW)
                                    {
                                        $typeAllowPermissions |= $effectivePermissions;
                                    }
                                    else
                                    {
                                        $typeDenyPermissions  |= $effectivePermissions;
                                    }
                                    // We shouldn't see something that isn't owned having CHANGE_OWNER.
                                    // assert('$typeAllowPermissions & Permission::CHANGE_OWNER == Permission::NONE');
                                }
                                $allowPermissions |= $typeAllowPermissions;
                                $denyPermissions  |= $typeDenyPermissions;
                            }
                            catch (NotFoundException $e)
                            {
                            }
                        }
                    }
                }
            }
            else
            {
                try
                {
                    $combinedPermissions = PermissionsCache::getCombinedPermissions($this, $permitable);
                }
                catch (NotFoundException $e)
                {
                    $securableItemId = $this      ->getClassId('SecurableItem');
                    $permitableId    = $permitable->getClassId('Permitable');
                    // Optimizations work on the database,
                    // anything not saved will not work.
                    assert('$permitableId > 0');
                    $className       = get_class($this);
                    $moduleName      = static::getModuleClassName();
                    $cachingOn  = DB_CACHING_ON ? 1 : 0;
                    $combinedPermissions = intval(ZurmoDatabaseCompatibilityUtil::
                                                    callFunction("get_securableitem_actual_permissions_for_permitable($securableItemId, $permitableId, '$className', '$moduleName', $cachingOn)"));
                    PermissionsCache::cacheCombinedPermissions($this, $permitable, $combinedPermissions);
                }
                $allowPermissions = ($combinedPermissions >> 8) & Permission::ALL;
                $denyPermissions  = $combinedPermissions        & Permission::ALL;
            }
            assert("($allowPermissions & ~Permission::ALL) == 0");
            assert("($denyPermissions  & ~Permission::ALL) == 0");
            return array($allowPermissions, $denyPermissions);
        }

        public function getPropagatedActualAllowPermissions(Permitable $permitable)
        {
            if ($permitable instanceof User)
            {
                $allowPermissions = Permission::NONE;
                $descendentRoles = $this->getAllDescendentRoles($permitable->role);
                foreach ($descendentRoles as $role)
                {
                    $allowPermissions |= $this->recursiveGetPropagatedAllowPermissions($role);
                }
                return $allowPermissions;
            }
            else
            {
                return Permission::NONE;
            }
        }

        protected function recursiveGetPropagatedAllowPermissions($role)
        {
            if (!SECURITY_OPTIMIZED)
            {
                // The slow way will remain here as documentation
                // for what the optimized way is doing.
                $propagatedPermissions = Permission::NONE;
                foreach ($role->users as $userInRole)
                {
                    $propagatedPermissions |= $this->getEffectivePermissions($userInRole) ;
                }
                return $propagatedPermissions;
            }
            else
            {
                // It should never get here because the optimized version
                // of getActualPermissions will call
                // get_securableitem_propagated_allow_permissions_for_permitable.
                throw new NotSupportedException();
            }
        }

        protected function getAllDescendentRoles($role)
        {
            $descendentRoles = array();
            if (count($role->roles) > 0)
            {
                foreach ($role->roles as $childRole)
                {
                    $descendentRoles[] = $childRole;
                    $descendentRoles = array_merge($descendentRoles,
                                                   $this->getAllDescendentRoles($childRole));
                }
            }
            return $descendentRoles;
        }

        public function getExplicitActualPermissions($permitable = null)
        {
            assert('$permitable === null || $permitable instanceof Permitable');
            if ($permitable === null)
            {
                $permitable = Yii::app()->user->userModel;
                if (!$permitable instanceof User)
                {
                    throw new NoCurrentUserSecurityException();
                }
            }
            $allowPermissions = Permission::NONE;
            $denyPermissions  = Permission::NONE;
            foreach ($this->unrestrictedGet('permissions') as $permission)
            {
                $explicitPermissions = $permission->getExplicitPermissions($permitable);
                if ($permission->type == Permission::ALLOW)
                {
                    $allowPermissions |= $explicitPermissions;
                }
                else
                {
                    $denyPermissions  |= $explicitPermissions;
                }
            }
            assert("($allowPermissions & ~Permission::ALL) == 0");
            assert("($denyPermissions  & ~Permission::ALL) == 0");
            return array($allowPermissions, $denyPermissions);
        }

        public function getInheritedActualPermissions($permitable = null)
        {
            assert('$permitable === null || $permitable instanceof Permitable');
            if ($permitable === null)
            {
                $permitable = Yii::app()->user->userModel;
                if (!$permitable instanceof User)
                {
                    throw new NoCurrentUserSecurityException();
                }
            }
            $allowPermissions = Permission::NONE;
            $denyPermissions  = Permission::NONE;
            foreach ($this->unrestrictedGet('permissions') as $permission)
            {
                $inheritedPermissions = $permission->getInheritedPermissions($permitable);
                if ($permission->type == Permission::ALLOW)
                {
                    $allowPermissions |= $inheritedPermissions;
                }
                else
                {
                    $denyPermissions  |= $inheritedPermissions;
                }
            }
            if (!($this instanceof NamedSecurableItem))
            {
                foreach (array(get_class($this), static::getModuleClassName()) as $securableItemName)
                {
                    try
                    {
                        $securableType = NamedSecurableItem::getByName($securableItemName);
                        $typeAllowPermissions = Permission::NONE;
                        $typeDenyPermissions  = Permission::NONE;
                        foreach ($securableType->permissions as $permission)
                        {
                            $inheritedPermissions = $permission->getInheritedPermissions($permitable);
                            if ($permission->type == Permission::ALLOW)
                            {
                                $typeAllowPermissions |= $inheritedPermissions;
                            }
                            else
                            {
                                $typeDenyPermissions  |= $inheritedPermissions;
                            }
                        }
                        $allowPermissions |= $typeAllowPermissions;
                        $denyPermissions  |= $typeDenyPermissions;
                    }
                    catch (NotFoundException $e)
                    {
                    }
                }
            }
            assert("($allowPermissions & ~Permission::ALL) == 0");
            assert("($denyPermissions  & ~Permission::ALL) == 0");
            return array($allowPermissions, $denyPermissions);
        }

        public function addPermissions(Permitable $permitable, $permissions, $type = Permission::ALLOW)
        {
            assert('is_int($permissions)');
            assert("($permissions & ~Permission::ALL) == 0");
            assert('$permissions != Permission::NONE');
            assert('in_array($type, array(Permission::ALLOW, Permission::DENY))');
            self::checkPermissionsHasAnyOf(Permission::CHANGE_PERMISSIONS);
            if ($this instanceof NamedSecurableItem)
            {
                PermissionsCache::forgetAll();
            }
            else
            {
                PermissionsCache::forgetSecurableItem($this);
            }
            $found = false;
            foreach ($this->permissions as $permission)
            {
                if ($permission->permitable->isSame($permitable) &&
                    $permission->type == $type)
                {
                    $permission->permissions |= $permissions;
                    $found = true;
                    break;
                }
            }
            if (!$found)
            {
                $permission = new Permission();
                $permission->permitable  = $permitable;
                $permission->type        = $type;
                $permission->permissions = $permissions;
                $this->permissions->add($permission);
            }
        }

        public function removePermissions(Permitable $permitable, $permissions = Permission::ALL, $type = Permission::ALLOW_DENY)
        {
            assert('is_int($permissions)');
            assert("($permissions & ~Permission::ALL) == 0");
            assert('$permissions != Permission::NONE');
            assert('in_array($type, array(Permission::ALLOW, Permission::DENY, Permission::ALLOW_DENY))');
            self::checkPermissionsHasAnyOf(Permission::CHANGE_PERMISSIONS);
            if ($this instanceof NamedSecurableItem)
            {
                PermissionsCache::forgetAll();
            }
            else
            {
                PermissionsCache::forgetSecurableItem($this);
            }
            foreach ($this->permissions as $permission)
            {
                if ($permission->permitable->isSame($permitable) &&
                    ($permission->type == $type ||
                     $type == Permission::ALLOW_DENY))
                {
                    $permission->permissions &= ~$permissions;
                    if ($permission->permissions == Permission::NONE)
                    {
                        $this->permissions->remove($permission);
                    }
                }
            }
        }

        public function removeAllPermissions()
        {
            self::checkPermissionsHasAnyOf(Permission::CHANGE_PERMISSIONS);
            PermissionsCache::forgetAll();
            $this->permissions->removeAll();
        }

        public function __get($attributeName)
        {
            if (!$this->isSaving  &&
                !$this->isSetting &&
                !$this->isValidating &&
                // Anyone can get the id and owner, createdByUser, and modifiedByUser anytime.
                !in_array($attributeName, array('id', 'owner', 'createByUser', 'modifiedByUser')))
            {
                self::checkPermissionsHasAnyOf(Permission::READ);
            }
            return parent::__get($attributeName);
        }

        public function __set($attributeName, $value)
        {
            if ($attributeName == 'owner')
            {
                self::checkPermissionsHasAnyOf(Permission::CHANGE_OWNER);
            }
            elseif ($attributeName == 'permissions')
            {
                self::checkPermissionsHasAnyOf(Permission::CHANGE_PERMISSIONS);
            }
            else
            {
                self::checkPermissionsHasAnyOf(Permission::WRITE);
            }
            parent::__set($attributeName, $value);
        }

        public function delete()
        {
            self::checkPermissionsHasAnyOf(Permission::DELETE);
            return parent::delete();
        }

        protected function checkPermissionsHasAnyOf($requiredPermissions)
        {
            assert('is_int($requiredPermissions)');
            $currentUser = Yii::app()->user->userModel;
            $effectivePermissions = $this->getEffectivePermissions($currentUser);
            if (($effectivePermissions & $requiredPermissions) == 0)
            {
                throw new AccessDeniedSecurityException($currentUser, $requiredPermissions, $effectivePermissions);
            }
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'relations' => array(
                    'permissions' => array(RedBeanModel::HAS_MANY, 'Permission', RedBeanModel::OWNED),
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return false;
        }

        /**
         * Override on any models you want to utilize ReadPermissionsOptimization
         */
        public static function hasReadPermissionsOptimization()
        {
            return false;
        }
    }
?>