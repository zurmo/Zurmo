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
     * Helper class for managing the explicit setting of permissions against a model in the user interface.
     * @see ExplicitReadWriteModelPermissions
     * @see ExplicitReadWriteModelPermissionsElement
     */
    class ExplicitReadWriteModelPermissionsUtil
    {
        /**
         * Defines the type as being the everyone group.
         * @var intger
         */
        const MIXED_TYPE_EVERYONE_GROUP    = 1;

        /**
         * Defines the type as being a specific group, but not the everyone group.
         * @var intger
         */
        const MIXED_TYPE_NONEVERYONE_GROUP = 2;

        /**
         * Given a mixed permitables data array, make a explicitReadWriteModelPermissions object. The
         * $mixedPermitablesData is an array with 2 sub-arrays.  readOnly and readWrite. These sub-arrays each
         * contain an array of permitable objects.
         * @param array $mixedPermitablesData
         */
        public static function makeByMixedPermitablesData($mixedPermitablesData)
        {
            assert('is_array($mixedPermitablesData)');
            assert('isset($mixedPermitablesData["readOnly"])');
            assert('isset($mixedPermitablesData["readWrite"])');
            $explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            foreach ($mixedPermitablesData['readOnly'] as $permitableData)
            {
                $permitableClassName = key($permitableData);
                $permitableId        = $permitableData[$permitableClassName];
                $explicitReadWriteModelPermissions->addReadOnlyPermitable($permitableClassName::getById($permitableId));
            }
            foreach ($mixedPermitablesData['readWrite'] as $permitableData)
            {
                $permitableClassName = key($permitableData);
                $permitableId        = $permitableData[$permitableClassName];
                $explicitReadWriteModelPermissions->addReadWritePermitable($permitableClassName::getById($permitableId));
            }
            return $explicitReadWriteModelPermissions;
        }

        /**
         * Given a explicitReadWriteModelPermissions object, make a $mixedPermitablesData array.
         * @param ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions
         */
        public static function makeMixedPermitablesDataByExplicitReadWriteModelPermissions(
                               $explicitReadWriteModelPermissions)
        {
            assert('$explicitReadWriteModelPermissions instanceof ExplicitReadWriteModelPermissions ||
                    $explicitReadWriteModelPermissions == null');
            if ($explicitReadWriteModelPermissions == null)
            {
                return null;
            }
            if ($explicitReadWriteModelPermissions->getReadOnlyPermitablesCount() == 0 &&
               $explicitReadWriteModelPermissions->getReadWritePermitablesCount() == 0)
            {
               return null;
            }
            $mixedPermitablesData = array();
            $mixedPermitablesData['readOnly'] = array();
            $mixedPermitablesData['readWrite'] = array();
            foreach ($explicitReadWriteModelPermissions->getReadOnlyPermitables() as $permitable)
            {
                $mixedPermitablesData['readOnly'][] = array(get_class($permitable) => $permitable->id);
            }
            foreach ($explicitReadWriteModelPermissions->getReadWritePermitables() as $permitable)
            {
                $mixedPermitablesData['readWrite'][] = array(get_class($permitable) => $permitable->id);
            }
            return $mixedPermitablesData;
        }

        /**
         * Given post data, which would be coming most likely from the ExplicitReadWriteModelPermissionsElement,
         * transform the post data into a ExplicitReadWriteModelPermissions object.  If the post data contains a 'type'
         * value that is not supported, an exception is thrown.
         * @param array $postData
         * @see ExplicitReadWriteModelPermissionsElement
         */
        public static function makeByPostData($postData)
        {
            assert('is_array($postData)');
            $explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            if ($postData['type'] == null)
            {
                return $explicitReadWriteModelPermissions;
            }
            elseif ($postData['type'] == ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP)
            {
                $explicitReadWriteModelPermissions->addReadWritePermitable(Group::getByName(Group::EVERYONE_GROUP_NAME));
                return $explicitReadWriteModelPermissions;
            }
            elseif ($postData['type'] == ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP)
            {
                assert('isset($postData["nonEveryoneGroup"])');
                $explicitReadWriteModelPermissions->addReadWritePermitable(
                                                    Group::getById((int)$postData["nonEveryoneGroup"]));
                return $explicitReadWriteModelPermissions;
            }
            else
            {
                throw notSupportedException();
            }
        }

        /**
         * Given an array of post data and a securable item, if the post data has the
         * 'explicitReadWriteModelPermissions' present, then make a explicitReadWriteModelPermissions
         * from that data and then resolve against any differences in the securable item.  This means if the
         * post data says a group is read/write, but the existing securable item does not have that, then this
         * signals this will need to be added.  Whereas if the securable item has a read/write group that the
         * post data does not have, this signals that this read/write needs to be removed.
         * @param array $postData
         * @param SecurableItem $securableItem
         */
        public static function resolveByPostDataAndModelThenMake($postData, SecurableItem $securableItem)
        {
            if (isset($postData['explicitReadWriteModelPermissions']))
            {
                $explicitReadWriteModelPermissions = self::
                                                     makeByPostData($postData['explicitReadWriteModelPermissions']);
                self::resolveForDifferencesBySecurableItem($explicitReadWriteModelPermissions, $securableItem);
                return $explicitReadWriteModelPermissions;
            }
            else
            {
                return null;
            }
        }

        /**
         * If the ExplicitReadWriteModelPermissions says a group is read/write, but the existing securable item
         * does not have that, then this signals this group will need to be added.  Whereas if the securable item has a
         * read/write group that the ExplicitReadWriteModelPermissions does not have, this signals that this read/write
         * needs to be removed.
         * @param ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions
         * @param SecurableItem $securableItem
         */
        protected static function resolveForDifferencesBySecurableItem($explicitReadWriteModelPermissions,
                                                                       SecurableItem $securableItem)
        {
            foreach ($securableItem->permissions as $permission)
            {
                $permission->castDownPermitable();
                if ($permission->permitable instanceof Group && $permission->type == Permission::ALLOW)
                {
                    if (Permission::READ == ($permission->permissions & Permission::READ))
                    {
                        if (!$explicitReadWriteModelPermissions->isReadOrReadWritePermitable($permission->permitable))
                        {
                            $explicitReadWriteModelPermissions->addReadWritePermitableToRemove($permission->permitable);
                        }
                    }
                    elseif (Permission::WRITE == ($permission->permissions & Permission::WRITE))
                    {
                        if (!$explicitReadWriteModelPermissions->isReadOrReadWritePermitable($permission->permitable))
                        {
                            $explicitReadWriteModelPermissions->addReadWritePermitableToRemove($permission->permitable);
                        }
                    }
                    break;
                }
            }
        }

        /**
         * Unset the 'explicitReadWriteModelPermissions' array of data in a post data array if it exists.
         * @param array $postData
         * @return array of post data with the 'explicitReadWriteModelPermissions' removed.
         */
        public static function removeIfExistsFromPostData($postData)
        {
            assert('is_array($postData)');
            if (isset($postData['explicitReadWriteModelPermissions']))
            {
                unset($postData['explicitReadWriteModelPermissions']);
            }
            return $postData;
        }

        /**
         * Given a SecurableItem, add and remove permissions
         * based on what the provided ExplicitReadWriteModelPermissions indicates should be done.
         * @param SecurableItem $securableItem
         * @param ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions
         */
        public static function resolveExplicitReadWriteModelPermissions(SecurableItem $securableItem,
                                  ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions)
        {
            assert('$securableItem->id > 0');
            $saveSecurableItem = false;
            if ($explicitReadWriteModelPermissions->getReadOnlyPermitablesCount() > 0)
            {
                $saveSecurableItem = true;
                foreach ($explicitReadWriteModelPermissions->getReadOnlyPermitables() as $permitable)
                {
                    $securableItem->addPermissions($permitable, Permission::READ);
                    if ($permitable instanceof Group)
                    {
                        ReadPermissionsOptimizationUtil::
                        securableItemGivenPermissionsForGroup($securableItem, $permitable);
                    }
                    elseif ($permitable instanceof User)
                    {
                        ReadPermissionsOptimizationUtil::
                        securableItemGivenPermissionsForUser($securableItem, $permitable);
                    }
                    else
                    {
                        throw new NotSupportedException();
                    }
                }
            }
            if ($explicitReadWriteModelPermissions->getReadWritePermitablesCount() > 0)
            {
                $saveSecurableItem = true;
                foreach ($explicitReadWriteModelPermissions->getReadWritePermitables() as $permitable)
                {
                    $securableItem->addPermissions($permitable, Permission::READ_WRITE_CHANGE_PERMISSIONS_CHANGE_OWNER);
                    if ($permitable instanceof Group)
                    {
                        ReadPermissionsOptimizationUtil::
                        securableItemGivenPermissionsForGroup($securableItem, $permitable);
                    }
                    elseif ($permitable instanceof User)
                    {
                        ReadPermissionsOptimizationUtil::
                        securableItemGivenPermissionsForUser($securableItem, $permitable);
                    }
                    else
                    {
                        throw new NotSupportedException();
                    }
                }
            }
            if ($explicitReadWriteModelPermissions->getReadOnlyPermitablesToRemoveCount() > 0)
            {
                $saveSecurableItem = true;
                foreach ($explicitReadWriteModelPermissions->getReadOnlyPermitablesToRemove() as $permitable)
                {
                    $securableItem->removePermissions($permitable, Permission::READ, Permission::ALLOW);
                    if ($permitable instanceof Group)
                    {
                        ReadPermissionsOptimizationUtil::
                        securableItemLostPermissionsForGroup($securableItem, $permitable);
                    }
                    elseif ($permitable instanceof User)
                    {
                        ReadPermissionsOptimizationUtil::
                        securableItemLostPermissionsForUser($securableItem, $permitable);
                    }
                    else
                    {
                        throw new NotSupportedException();
                    }
                }
            }
            if ($explicitReadWriteModelPermissions->getReadWritePermitablesToRemoveCount() > 0)
            {
                $saveSecurableItem = true;
                foreach ($explicitReadWriteModelPermissions->getReadWritePermitablesToRemove() as $permitable)
                {
                    $securableItem->removePermissions($permitable,
                                                      Permission::READ_WRITE_CHANGE_PERMISSIONS_CHANGE_OWNER, Permission::ALLOW);
                    if ($permitable instanceof Group)
                    {
                        ReadPermissionsOptimizationUtil::
                        securableItemLostPermissionsForGroup($securableItem, $permitable);
                    }
                    elseif ($permitable instanceof User)
                    {
                        ReadPermissionsOptimizationUtil::
                        securableItemLostPermissionsForUser($securableItem, $permitable);
                    }
                    else
                    {
                        throw new NotSupportedException();
                    }
                }
            }
            if ($saveSecurableItem)
            {
                return $securableItem->save();
            }
            return true;
        }

        /**
         * Make an ExplicitReadWriteModelPermissions by SecurableItem.
         * @param SecurableItem $securableItem
         */
        public static function makeBySecurableItem(SecurableItem $securableItem)
        {
            $explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            foreach ($securableItem->permissions as $permission)
            {
                $permission->castDownPermitable();
                if ($permission->permitable instanceof Group && $permission->type == Permission::ALLOW)
                {
                    if (Permission::WRITE == ($permission->permissions & Permission::WRITE))
                    {
                        $explicitReadWriteModelPermissions->addReadWritePermitable($permission->permitable);
                    }
                    elseif (Permission::READ == ($permission->permissions & Permission::READ))
                    {
                        $explicitReadWriteModelPermissions->addReadOnlyPermitable($permission->permitable);
                    }
                }
            }
            return $explicitReadWriteModelPermissions;
        }
    }
?>