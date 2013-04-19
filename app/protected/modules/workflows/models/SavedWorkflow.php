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
     * Model to store information about a single workflow including time trigger, triggers, action, and email messages
     *
     */
    class SavedWorkflow extends Item
    {
        /**
         * @param $name
         * @return Array of SavedWorkflow models that match the given name or an empty array if nothing matches
         */
        public static function getByName($name)
        {
            assert('is_string($name) && $name != ""');
            return self::getSubset(null, null, null, "name = '$name'");
        }

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
                    'description',
                    'isActive',
                    'moduleClassName',
                    'name',
                    'order',
                    'serializedData',
                    'triggerOn',
                    'type'
                ),
                'rules' => array(
                    array('description',         'type',   'type' => 'string'),
                    array('isActive',            'boolean'),
                    array('moduleClassName',     'required'),
                    array('moduleClassName',     'type',   'type' => 'string'),
                    array('moduleClassName',     'length', 'max'  => 64),
                    array('name',                'required'),
                    array('name',                'type',   'type' => 'string'),
                    array('name',                'length', 'max'  => 64),
                    array('order',               'type',    'type' => 'integer'),
                    array('serializedData',      'required'),
                    array('serializedData',      'type', 'type' => 'string'),
                    array('type',                'required'),
                    array('type',                'type',   'type' => 'string'),
                    array('type',                'length', 'max'  => 15),
                    array('triggerOn',           'required'),
                    array('triggerOn',           'type',   'type' => 'string'),
                    array('triggerOn',           'length', 'max'  => 15),
                ),
                'elements' => array(
                    'triggerOn'       => 'TriggerOnStaticDropDown',
                    'type'            => 'WorkflowTypeStaticDropDown',
                    'moduleClassName' => 'ModuleForWorkflowStaticDropDown',
                )
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
         * @return string
         */
        public function __toString()
        {
            if (trim($this->name) == '')
            {
                return Yii::t('Default', '(Unnamed)');
            }
            return $this->name;
        }

        /**
         * @param $moduleClassName
         * @param $isNewModel
         * @return Array of SavedWorkflow models
         */
        public static function getActiveByModuleClassNameAndIsNewModel($moduleClassName, $isNewModel)
        {
            assert('is_string($moduleClassName)');
            assert('is_bool($isNewModel)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'moduleClassName',
                    'operatorType'         => 'equals',
                    'value'                => $moduleClassName,
                ),
                2 => array(
                    'attributeName'        => 'isActive',
                    'operatorType'         => 'equals',
                    'value'                => true,
                ),
                3 => array(
                    'attributeName'        => 'triggerOn',
                    'operatorType'         => 'equals',
                    'value'                => Workflow::TRIGGER_ON_NEW_AND_EXISTING
                ),
                4 => array(
                    'attributeName'        => 'triggerOn',
                    'operatorType'         => 'equals',
                    'value'                => self::resolveExtraTriggerOnValueByIsNewModel($isNewModel)
                ),
            );
            $searchAttributeData['structure'] = '1 AND 2 AND (3 OR 4)';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('SavedWorkflow');
            $where   = RedBeanModelDataProvider::makeWhere('SavedWorkflow', $searchAttributeData, $joinTablesAdapter);
            $orderBy = RedBeanModelDataProvider::resolveSortAttributeColumnName('SavedWorkflow', $joinTablesAdapter, 'order');
            return self::getSubset($joinTablesAdapter, null, null, $where, $orderBy);
        }

        /**
         * @param $moduleClassName
         * @return Array of SavedWorkflow models
         */
        public static function getAllByModuleClassName($moduleClassName)
        {
            assert('is_string($moduleClassName)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'moduleClassName',
                    'operatorType'         => 'equals',
                    'value'                => $moduleClassName,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('SavedWorkflow');
            $where   = RedBeanModelDataProvider::makeWhere('SavedWorkflow', $searchAttributeData, $joinTablesAdapter);
            $orderBy = RedBeanModelDataProvider::resolveSortAttributeColumnName('SavedWorkflow', $joinTablesAdapter, 'order');
            return self::getSubset($joinTablesAdapter, null, null, $where, $orderBy);
        }

        public static function getGamificationRulesType()
        {
            return 'WorkflowGamification';
        }

        /**
         * @param $isNewModel
         * @return string
         */
        protected static function resolveExtraTriggerOnValueByIsNewModel($isNewModel)
        {
            assert('is_bool($isNewModel)');
            if ($isNewModel)
            {
                return Workflow::TRIGGER_ON_NEW;
            }
            else
            {
                return Workflow::TRIGGER_ON_EXISTING;
            }
        }
    }
?>