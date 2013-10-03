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
     * Class ModelStateChangesSubscriptionUtil
     */
    class ModelStateChangesSubscriptionUtil
    {
        /**
         * Get array of new or modified models
         * @param $serviceName
         * @param $modelClassName
         * @param $pageSize
         * @param $offset
         * @param $timestamp
         * @param null $stateMetadataAdapterClassName
         * @param null $owner
         * @return bool
         */
        public static function getCreatedModels($serviceName, $modelClassName, $pageSize, $offset, $timestamp,
                                                $stateMetadataAdapterClassName = null, $owner = null)
        {
            $metadata = array();

            $modelIds = ReadPermissionsSubscriptionUtil::getAddedOrDeletedModelsFromReadSubscriptionTable(
                $serviceName, $modelClassName, $timestamp, ReadPermissionsSubscriptionUtil::TYPE_ADD,
                Yii::app()->user->userModel);
            if (!is_array($modelIds) || empty($modelIds))
            {
                return false;
            }
            $metadata['clauses'] = array(
                1 => array(
                    'attributeName'        => 'id',
                    'operatorType'         => 'oneOf',
                    'value'                => $modelIds
                ),
            );
            if (isset($owner) && $owner instanceof User)
            {
                $metadata['clauses'][2] = array(
                    'attributeName'        => 'owner',
                    'operatorType'         => 'equals',
                    'value'                => $owner->id
                );
                $metadata['structure'] = "(1 AND 2)";
            }
            else
            {
                $metadata['structure'] = "1";
            }

            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
            if ($stateMetadataAdapterClassName != null)
            {
                $stateMetadataAdapter = new $stateMetadataAdapterClassName($metadata);
                $metadata = $stateMetadataAdapter->getAdaptedDataProviderMetadata();
            }
            $where  = RedBeanModelDataProvider::makeWhere($modelClassName, $metadata, $joinTablesAdapter);
            return $modelClassName::getSubset($joinTablesAdapter, $offset, $pageSize, $where);
        }

        /**
         * @param string $serviceName
         * @param $serviceName
         * @param $modelClassName
         * @param $pageSize
         * @param $offset
         * @param $timestamp
         * @param null $stateMetadataAdapterClassName
         * @return array
         */
        public static function getDeletedModelIds($serviceName, $modelClassName, $pageSize, $offset, $timestamp,
                                                  $stateMetadataAdapterClassName = null)
        {
            $modelIds = ReadPermissionsSubscriptionUtil::getAddedOrDeletedModelsFromReadSubscriptionTable(
                $serviceName, $modelClassName, $timestamp, ReadPermissionsSubscriptionUtil::TYPE_DELETE,
                Yii::app()->user->userModel);
            $modelIds = array_slice($modelIds, $offset, $pageSize);
            return $modelIds;
        }

        /**
         * Get array of modified models
         * @param $modelClassName
         * @param $pageSize
         * @param $offset
         * @param $timestamp
         * @param null $stateMetadataAdapterClassName
         * @param null $owner
         * @return mixed
         */
        public static function getUpdatedModels($modelClassName, $pageSize, $offset, $timestamp,
                                                $stateMetadataAdapterClassName = null, $owner = null)
        {
            if ($timestamp != 0)
            {
                $metadata = array();
                $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($timestamp);
                $metadata['clauses'] = array(
                    1 => array(
                        'attributeName'        => 'modifiedDateTime',
                        'operatorType'         => 'greaterThan',
                        'value'                => $dateTime
                    )
                );

                if (isset($owner) && $owner instanceof User)
                {
                    $metadata['clauses'][2] = array(
                        'attributeName'        => 'owner',
                        'operatorType'         => 'equals',
                        'value'                => $owner->id
                    );
                    $metadata['structure'] = "(1 AND 2) AND (item.modifiedDateTime > (3 + item.createdDateTime))";
                }
                else
                {
                    $metadata['structure'] = "1 AND (item.modifiedDateTime > (3 + item.createdDateTime))";
                }

                $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
                if ($stateMetadataAdapterClassName != null)
                {
                    $stateMetadataAdapter = new $stateMetadataAdapterClassName($metadata);
                    $metadata = $stateMetadataAdapter->getAdaptedDataProviderMetadata();
                }
                $where  = RedBeanModelDataProvider::makeWhere($modelClassName, $metadata, $joinTablesAdapter);
                return $modelClassName::getSubset($joinTablesAdapter, $offset, $pageSize, $where, 'modifiedDateTime asc');
            }
            else
            {
                return array();
            }
        }
    }
?>