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
     * Model for storing message queue information.  When creating email messages to send when a workflow is fired
     * the send dateTime can be a period in the future, for example 1 year.  This class is used for making models
     * that store that information and then is processed by a job @see WorkflowMessageInQueueJob
     */
    class WorkflowMessageInQueue extends Item
    {
        /**
         * @return bool
         */
        public static function canSaveMetadata()
        {
            return true;
        }

        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'modelClassName',
                    'processDateTime',
                    'serializedData',
                ),
                'relations' => array(
                    'modelItem'       => array(RedBeanModel::HAS_ONE,   'Item',          RedBeanModel::NOT_OWNED),
                    'savedWorkflow'   => array(RedBeanModel::HAS_ONE,   'SavedWorkflow', RedBeanModel::NOT_OWNED),
                    'triggeredByUser' => array(RedBeanModel::HAS_ONE,   'User',          RedBeanModel::NOT_OWNED,
                                               RedBeanModel::LINK_TYPE_SPECIFIC, 'triggeredByUser'),
                ),
                'rules' => array(
                    array('modelClassName',   'required'),
                    array('modelClassName',   'type',   'type' => 'string'),
                    array('modelClassName',   'length', 'max'  => 64),
                    array('modelItem',        'required'),
                    array('processDateTime',  'required'),
                    array('processDateTime',  'type', 'type' => 'datetime'),
                    array('savedWorkflow',    'required'),
                    array('serializedData',   'required'),
                    array('serializedData',   'type', 'type' => 'string'),
                ),
                'elements' => array(
                    'processDateTime' => 'DateTime'
                ),
                'defaultSortAttribute' => 'processDateTime',
            );
            return $metadata;
        }

        /**
         * @return bool
         */
        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * @return null|string
         */
        public static function getModuleClassName()
        {
            return 'WorkflowsModule';
        }

        /**
         * @param $pageSize
         * @return array of WorkflowMessageInQueue models
         */
        public static function getModelsToProcess($pageSize)
        {
            assert('is_int($pageSize)');
            $timeStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'processDateTime',
                    'operatorType'         => 'lessThan',
                    'value'                => $timeStamp,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('WorkflowMessageInQueue');
            $where = RedBeanModelDataProvider::makeWhere('WorkflowMessageInQueue', $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, $pageSize, $where, null);
        }
    }
?>