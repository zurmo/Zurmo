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
     * A securable item 'owned' by a user in the system.
     */
    class OwnedSecurableItem extends SecurableItem
    {
        protected function constructDerived($bean, $setDefaults)
        {
            assert('$bean === null || $bean instanceof RedBean_OODBBean');
            assert('is_bool($setDefaults)');
            parent::constructDerived($bean, $setDefaults);
            // Even though setting the owner is not technically
            // a default in the sense of a Yii default rule,
            // if true the owner is not set because blank models
            // are used for searching mass updating.
            if ($bean ===  null && $setDefaults)
            {
                $currentUser = Yii::app()->user->userModel;
                if (!$currentUser instanceof User)
                {
                    throw new NoCurrentUserSecurityException();
                }
                $this->unrestrictedSet('owner', $currentUser);
            }
        }

        public function getEffectivePermissions($permitable = null)
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
            $owner = $this->unrestrictedGet('owner');
            # If an owned securable item doesn't yet have an owner
            # then whoever is creating it has full access to it. If they
            # save it with the owner being someone else they are giving
            # it away and potentially lose access to it.
            if ($owner->id < 0 ||
                $owner->isSame($permitable))
            {
                return Permission::ALL;
            }
            else
            {
                return parent::getEffectivePermissions($permitable);
            }
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
            $owner = $this->unrestrictedGet('owner');
            # If an owned securable item doesn't yet have an owner
            # then whoever is creating it has full access to it. If they
            # save it with the owner being someone else they are giving
            # it away and potentially lose access to it.
            if ($owner->id < 0 ||
                $owner->isSame($permitable))
            {
                return array(Permission::ALL, Permission::NONE);
            }
            else
            {
                return parent::getActualPermissions($permitable);
            }
        }

        public function __set($attributeName, $value)
        {
            if ($attributeName == 'owner')
            {
                self::checkPermissionsHasAnyOf(Permission::CHANGE_OWNER);
                $this->isSetting = true;
                try
                {
                    if (!$this->isSaving)
                    {
                        AuditUtil::saveOriginalAttributeValue($this, $attributeName, $value);
                    }
                    parent::unrestrictedSet($attributeName, $value);
                    $this->isSetting = false;
                }
                catch (Exception $e)
                {
                    $this->isSetting = false;
                    throw $e;
                }
            }
            else
            {
                parent::__set($attributeName, $value);
            }
        }

        protected function afterSave()
        {
            if ($this->hasReadPermissionsOptimization())
            {
                if ($this->isNewModel)
                {
                    ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($this);
                }
                elseif (isset($this->originalAttributeValues['owner']))
                {
                    ReadPermissionsOptimizationUtil::ownedSecurableItemOwnerChanged($this,
                                                            User::getById($this->originalAttributeValues['owner'][1]));
                }
            }
            parent::afterSave();
        }

        protected function beforeDelete()
        {
            parent::beforeDelete();
            if ($this->hasReadPermissionsOptimization())
            {
                ReadPermissionsOptimizationUtil::securableItemBeingDeleted($this);
            }
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'relations' => array(
                    'owner' => array(RedBeanModel::HAS_ONE, 'User'),
                ),
                'rules' => array(
                    array('owner', 'required'),
                ),
                'elements' => array(
                    'owner' => 'User',
                ),
            );
            return $metadata;
        }

        /**
         * Override to add ReadPermissionOptimization query parts.
         */
        public static function makeSubsetOrCountSqlQuery($tableName,
                                                         RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter = null,
                                                         $offset = null, $count = null,
                                                         $where = null, $orderBy = null,
                                                         $selectCount = false, $selectDistinct = false,
                                                         array $quotedExtraSelectColumnNameAndAliases = array())
        {
            assert('is_string($tableName) && $tableName != ""');
            assert('$offset  === null || is_integer($offset)  && $offset  >= 0');
            assert('$count   === null || is_integer($count)   && $count   >= 1');
            assert('$where   === null || is_string ($where)   && $where   != ""');
            assert('$orderBy === null || is_string ($orderBy) && $orderBy != ""');
            assert('is_bool($selectCount)');
            assert('is_bool($selectDistinct)');
            $user = Yii::app()->user->userModel;
            if (!$user instanceof User)
            {
                throw new NoCurrentUserSecurityException();
            }
            if ($joinTablesAdapter == null)
            {
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            }
            static::resolveReadPermissionsOptimizationToSqlQuery($user, $joinTablesAdapter, $where, $selectDistinct);
            return parent::makeSubsetOrCountSqlQuery($tableName, $joinTablesAdapter, $offset, $count,
                                                         $where, $orderBy, $selectCount, $selectDistinct,
                                                         $quotedExtraSelectColumnNameAndAliases);
        }

        public static function resolveReadPermissionsOptimizationToSqlQuery(User $user,
                                    RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter,
                                    & $where,
                                    & $selectDistinct)
        {
            assert('$where == null || is_string($where)');
            assert('is_bool($selectDistinct)');
            $modelClassName  = get_called_class();
            $moduleClassName = $modelClassName::getModuleClassName();
            //Currently only adds munge if the module is securable and this model supports it.
            if (static::hasReadPermissionsOptimization() &&$moduleClassName != null &&
                is_subclass_of($moduleClassName, 'SecurableModule'))
            {
                $permission = PermissionsUtil::getActualPermissionDataForReadByModuleNameForCurrentUser($moduleClassName);
                if ($permission == Permission::NONE || $permission == Permission::DENY)
                {
                    $quote                               = DatabaseCompatibilityUtil::getQuote();
                    $modelAttributeToDataProviderAdapter = new OwnedSecurableItemIdToDataProviderAdapter(
                                                               $modelClassName, null);
                    $ownedTableAliasName = ModelDataProviderUtil::
                                           resolveShouldAddFromTableAndGetAliasName( $modelAttributeToDataProviderAdapter,
                                                                                     $joinTablesAdapter);
                    $ownerColumnName = RedBeanModel::getForeignKeyName('OwnedSecurableItem', 'owner');
                    $mungeIds = ReadPermissionsOptimizationUtil::getMungeIdsByUser($user);
                    if ($where != null)
                    {
                        $where = '(' . $where . ') and ';
                    }
                    if (count($mungeIds) > 0 && $permission == Permission::NONE)
                    {
                        $extraOnQueryPart    = " and {$quote}munge_id{$quote} in ('" . join("', '", $mungeIds) . "')";
                        $mungeTableName      = ReadPermissionsOptimizationUtil::getMungeTableName($modelClassName);
                        $mungeTableAliasName = $joinTablesAdapter->addLeftTableAndGetAliasName(
                                                                    $mungeTableName,
                                                                    'securableitem_id',
                                                                    $ownedTableAliasName,
                                                                    'securableitem_id',
                                                                    $extraOnQueryPart);

                        $where .= "($quote$ownedTableAliasName$quote.$quote$ownerColumnName$quote = $user->id OR "; // Not Coding Standard
                        $where .= "$quote$mungeTableName$quote.{$quote}munge_id{$quote} IS NOT NULL)"; // Not Coding Standard
                        $selectDistinct = true; //must use distinct since adding munge table query.
                    }
                    elseif ($permission == Permission::DENY)
                    {
                        $where .= "$quote$ownedTableAliasName$quote.$quote$ownerColumnName$quote = $user->id"; // Not Coding Standard
                    }
                    else
                    {
                        throw new NotSupportedException();
                    }
                }
            }
        }

        public static function isTypeDeletable()
        {
            return false;
        }
    }
?>
