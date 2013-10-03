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

    abstract class ReadPermissionsSubscriptionUtil
    {
        const TYPE_ADD    = 1;
        const TYPE_DELETE = 2;

        /**
         * Rebuild read permission subscription table
         */
        public static function buildTables()
        {
            foreach (self::getReadSubscriptionModelClassNames() as $modelClassName)
            {
                $readPermissionsSubscriptionTableName  = self::getSubscriptionTableName($modelClassName);
                self::recreateTable($readPermissionsSubscriptionTableName);
            }
            ModelCreationApiSyncUtil::buildTable();
        }

        /**
         * Get all read subscription model class names
         * @return array|mixed
         */
        public static function getReadSubscriptionModelClassNames()
        {
            try
            {
                return GeneralCache::getEntry('readPermissionsSubscriptionModelClassNames');
            }
            catch (NotFoundException $e)
            {
                $readPermissionsSubscriptionModelClassNames = self::findReadSubscriptionModelClassNames();
                GeneralCache::cacheEntry('readPermissionsSubscriptionModelClassNames',
                    $readPermissionsSubscriptionModelClassNames);
                return $readPermissionsSubscriptionModelClassNames;
            }
        }

        /**
         * Get all Read Permissions Subscription Model ClassNames
         * @return array
         */
        //public for testing only.
        public static function findReadSubscriptionModelClassNames()
        {
            $readPermissionsSubscriptionModelClassNames = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                $modelClassNames = $module::getModelClassNames();
                foreach ($modelClassNames as $modelClassName)
                {
                    if (is_subclass_of($modelClassName, 'OwnedSecurableItem') &&
                        $modelClassName::hasReadPermissionsSubscriptionOptimization())
                    {
                        $readPermissionsSubscriptionModelClassNames[] = $modelClassName;
                    }
                }
            }
            return $readPermissionsSubscriptionModelClassNames;
        }

        /**
         * Public for testing only. Need to manually create test model tables that would not be picked up normally.
         * @param $modelSubscriptionTableName
         */
        public static function recreateTable($modelSubscriptionTableName)
        {
            assert('is_string($modelSubscriptionTableName) && $modelSubscriptionTableName  != ""');
            $result = R::getAll("SHOW TABLES LIKE '{$modelSubscriptionTableName}'");
            $tableExists = count($result);

            if (!$tableExists)
            {
                R::exec("create table $modelSubscriptionTableName (
                                               id int(11)         unsigned not null PRIMARY KEY AUTO_INCREMENT ,
                                               userid int(11)     unsigned not null,
                                               modelid int(11)    unsigned not null,
                                               modifieddatetime   datetime DEFAULT null,
                                               subscriptiontype   tinyint(4) DEFAULT null
                                        )");
            }
        }

        protected static function getModelTableName($modelClassName)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            return RedBeanModel::getTableName($modelClassName);
        }

        public static function getSubscriptionTableName($modelClassName)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            return self::getModelTableName($modelClassName) . '_read_subscription';
        }

        /**
         * Update read subscription table for all users and models
         * @param bool $partialBuild
         */
        public static function updateAllReadSubscriptionTables($partialBuild = true)
        {
            $loggedUser = Yii::app()->user->userModel;
            $users = User::getAll();
            foreach ($users as $user)
            {
                Yii::app()->user->userModel = $user;
                $modelClassNames = ReadPermissionsSubscriptionUtil::getReadSubscriptionModelClassNames();
                if (!empty($modelClassNames) && is_array($modelClassNames))
                {
                    foreach ($modelClassNames as $modelClassName)
                    {
                        if ($modelClassName != 'Account')
                        {
                            self::updateReadSubscriptionTableByModelClassNameAndUser($modelClassName,
                                Yii::app()->user->userModel, $partialBuild, true);
                        }
                        else
                        {
                            self::updateReadSubscriptionTableByModelClassNameAndUser($modelClassName,
                                Yii::app()->user->userModel, $partialBuild, false);
                        }
                    }
                }
            }
            Yii::app()->user->userModel = $loggedUser;
        }

        /**
         * Update models in read subscription table based on modelId and userId(userId is used implicitly in getSubsetIds)
         * @param string $modelClassName
         * @param User $user
         * @param bool $partialBuild
         * @param bool $onlyOwnedModels
         */
        public static function updateReadSubscriptionTableByModelClassNameAndUser($modelClassName, User $user,
                                                                                  $partialBuild = true, $onlyOwnedModels = false)
        {
            assert('$modelClassName === null || is_string($modelClassName) && $modelClassName != ""');
            $metadata = array();
            $lastReadPermissionUpdateTimestamp = self::getLastReadPermissionUpdateTimestamp();
            $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($lastReadPermissionUpdateTimestamp);
            $nowDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());

            $metadata['clauses'][1] = array(
                'attributeName'        => 'createdDateTime',
                'operatorType'         => 'lessThanOrEqualTo',
                'value'                => $nowDateTime
            );
            $metadata['structure'] = "1";

            if ($onlyOwnedModels)
            {
                $metadata['clauses'][2] = array(
                    'attributeName'        => 'owner',
                    'operatorType'         => 'equals',
                    'value'                => $user->id
                );
                $metadata['structure'] .= " AND 2";
            }
            if ($partialBuild)
            {
                $metadata['clauses'][3] = array(
                    'attributeName'        => 'createdDateTime',
                    'operatorType'         => 'greaterThan',
                    'value'                => $dateTime
                );
                $metadata['structure'] .= " AND 3";
            }

            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
            $where  = RedBeanModelDataProvider::makeWhere($modelClassName, $metadata, $joinTablesAdapter);
            $userModelIds = $modelClassName::getSubsetIds($joinTablesAdapter, null, null, $where, 'createdDateTime asc');

            // Get models from subscription table
            $tableName = self::getSubscriptionTableName($modelClassName);
            $sql = "SELECT modelid FROM $tableName WHERE userid = " . $user->id .
                " AND subscriptiontype = " . self::TYPE_ADD;

            $permissionTableRows = R::getAll($sql);
            $permissionTableIds = array();
            if (is_array($permissionTableRows) && !empty($permissionTableRows))
            {
                foreach ($permissionTableRows as $permissionTableRow)
                {
                    $permissionTableIds[] = $permissionTableRow['modelid'];
                }
            }
            $modelIdsToAdd = array_diff($userModelIds, $permissionTableIds);
            $modelIdsToDelete = array_diff($permissionTableIds, $userModelIds);
            if (is_array($modelIdsToAdd) && !empty($modelIdsToAdd))
            {
                foreach ($modelIdsToAdd as $modelId)
                {
                    $sql = "DELETE FROM $tableName WHERE
                                                userid = '" . $user->id . "'
                                                AND modelid = '{$modelId}'
                                                AND subscriptiontype='" . self::TYPE_DELETE . "';";
                    R::exec($sql);

                    $sql = "INSERT INTO $tableName VALUES
                                                (null, '" . $user->id . "', '{$modelId}', '{$nowDateTime}', '" . self::TYPE_ADD . "');";
                    R::exec($sql);
                }
            }

            if (is_array($modelIdsToDelete) && !empty($modelIdsToDelete))
            {
                foreach ($modelIdsToDelete as $modelId)
                {
                    $sql = "DELETE FROM $tableName WHERE
                                                userid = '" . $user->id . "'
                                                AND modelid = '{$modelId}'
                                                AND subscriptiontype='" . self::TYPE_ADD . "';";
                    R::exec($sql);

                    $sql = "INSERT INTO $tableName VALUES
                                                (null, '" . $user->id . "', '{$modelId}', '{$nowDateTime}', '" . self::TYPE_DELETE . "');";
                    R::exec($sql);
                }
            }

            self::setTimeReadPermissionUpdateTimestamp($lastReadPermissionUpdateTimestamp);
        }

        /**
         * Get all added or deleted models from read permission subscription table
         * @param $serviceName
         * @param $modelClassName
         * @param $lastUpdateTimestamp
         * @param $type
         * @param $user
         * @return array
         */
        public static function getAddedOrDeletedModelsFromReadSubscriptionTable($serviceName, $modelClassName,
                                                                                $lastUpdateTimestamp, $type, $user)
        {
            assert('$user instanceof User');
            $tableName = self::getSubscriptionTableName($modelClassName);
            $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($lastUpdateTimestamp);
            if ($type == ReadPermissionsSubscriptionUtil::TYPE_DELETE)
            {
                $sql = "SELECT {$tableName}.modelid FROM $tableName" .
                    " WHERE {$tableName}.userid = " . $user->id .
                    " AND {$tableName}.subscriptiontype = " . $type .
                    " AND {$tableName}.modifieddatetime >= '" . $dateTime . "'" .
                    " order by {$tableName}.modifieddatetime ASC, {$tableName}.modelid  ASC";
            }
            else
            {
                $sql = "SELECT {$tableName}.modelid FROM $tableName" .
                    " left join " . ModelCreationApiSyncUtil::TABLE_NAME . " isct " .
                    " on isct.modelid = {$tableName}.modelid" .
                    " AND isct.servicename = '" . $serviceName . "'" .
                    " AND isct.modelclassname = '" . $modelClassName . "'" .
                    " WHERE {$tableName}.userid = " . $user->id .
                    " AND {$tableName}.subscriptiontype = " . $type .
                    " AND {$tableName}.modifieddatetime >= '" . $dateTime . "'" .
                    " AND isct.modelid is null" .
                    " order by {$tableName}.modifieddatetime ASC, {$tableName}.modelid  ASC";
            }
            $modelIdsRows = R::getAll($sql);
            $modelIds = array();
            if (is_array($modelIdsRows) && !empty($modelIdsRows))
            {
                foreach ($modelIdsRows as $modelIdRow)
                {
                    $modelIds[] = $modelIdRow['modelid'];
                }
            }
            return $modelIds;
        }

        /**
         * Get all added model names and ids from read permission subscription table
         * @param $serviceName
         * @param $modelClassName
         * @param $lastUpdateTimestamp
         * @param $user
         * @return array
         */
        public static function getAddedModelNamesAndIdsFromReadSubscriptionTable($serviceName,
                                                                                 $modelClassName,
                                                                                 $lastUpdateTimestamp,
                                                                                 $user)
        {
            assert('$user instanceof User');
            $tableName = self::getSubscriptionTableName($modelClassName);
            $modelTableName = RedBeanModel::getTableName($modelClassName);
            $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($lastUpdateTimestamp);
            $sql = "SELECT {$tableName}.modelid, {$modelTableName}.name FROM $tableName" .
                " left join " . ModelCreationApiSyncUtil::TABLE_NAME . " isct " .
                " on isct.modelid = {$tableName}.modelid" .
                " AND isct.servicename = '" . $serviceName . "'" .
                " AND isct.modelclassname = '" . $modelClassName . "'" .
                " left join {$modelTableName} on {$modelTableName}.id = {$tableName}.modelid" .
                " WHERE {$tableName}.userid = " . $user->id .
                " AND {$tableName}.subscriptiontype = " . self::TYPE_ADD .
                " AND {$tableName}.modifieddatetime >= '" . $dateTime . "'" .
                " AND isct.modelid is null" .
                " order by {$tableName}.modifieddatetime ASC, {$tableName}.modelid  ASC";
            $modelIdsRows = R::getAll($sql);
            $modelIds = array();
            if (is_array($modelIdsRows) && !empty($modelIdsRows))
            {
                foreach ($modelIdsRows as $modelIdRow)
                {
                    $modelIds[$modelIdRow['modelid']] = $modelIdRow['name'];
                }
            }
            return $modelIds;
        }

        /**
         * Get details about read subscription update details from configuration(global metadata)
         * @return array
         */
        public static function getReadSubscriptionUpdateDetails()
        {
            $readSubscriptionUpdateDetails = ZurmoConfigurationUtil::getByModuleName('ZurmoModule',
                'readSubscriptionUpdateDetails');
            return $readSubscriptionUpdateDetails;
        }

        /**
         * Set read subscription update details from configuration(global metadata)
         * @param array $readSubscriptionUpdateDetails
         */
        public static function setReadSubscriptionUpdateDetails($readSubscriptionUpdateDetails)
        {
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'readSubscriptionUpdateDetails',
                $readSubscriptionUpdateDetails);
        }

        /**
         * Get last read permission update timestamp in  read subscription update details(stored in configuration)
         * @return int
         */
        public static function getLastReadPermissionUpdateTimestamp()
        {
            $readSubscriptionUpdateDetails = self::getReadSubscriptionUpdateDetails();
            if (isset($readSubscriptionUpdateDetails['lastReadPermissionUpdateTimestamp']))
            {
                return $readSubscriptionUpdateDetails['lastReadPermissionUpdateTimestamp'];
            }
            else
            {
                return 0;
            }
        }

        /**
         * Set last read permission update timestamp in  read subscription update details(stored in configuration)
         * @param int $lastReadPermissionUpdateTimestamp
         */
        public static function setTimeReadPermissionUpdateTimestamp($lastReadPermissionUpdateTimestamp)
        {
            $readSubscriptionUpdateDetails = self::getReadSubscriptionUpdateDetails();
            $readSubscriptionUpdateDetails['lastReadPermissionUpdateTimestamp'] = $lastReadPermissionUpdateTimestamp;
            self::setReadSubscriptionUpdateDetails($readSubscriptionUpdateDetails);
        }
    }
?>