<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class Autoresponder extends Item
    {
        const OPERATION_SUBSCRIBE   = 1;

        const OPERATION_UNSUBSCRIBE = 2;

        public static function getByName($name)
        {
            return static::getByNameOrEquivalent('subject', $name);
        }

        public static function getModuleClassName()
        {
            return 'AutorespondersModule';
        }

        public static function getOperationTypeDropDownArray()
        {
            return array(
                self::OPERATION_SUBSCRIBE       => Zurmo::t('AutorespondersModule', 'Subscription to list'),
                self::OPERATION_UNSUBSCRIBE     => Zurmo::t('AutorespondersModule', 'Unsubscribed from list'),
            );
        }

        /**
         * Returns the display name for the model class.
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('AutorespondersModule', 'Autoresponder', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('AutorespondersModule', 'Autoresponders', array(), null, $language);
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'subject',
                    'htmlContent',
                    'textContent',
                    'fromOperationDurationInterval',
                    'fromOperationDurationType',
                    'operationType',
                    'enableTracking',
                ),
                'rules' => array(
                    array('subject',                'required'),
                    array('subject',                'type',    'type' => 'string'),
                    array('subject',                'length',  'min'  => 3, 'max' => 64),
                    array('htmlContent',            'type',    'type' => 'string'),
                    array('textContent',            'type',    'type' => 'string'),
                    array('htmlContent',            'AtLeastOneContentAreaRequiredValidator'),
                    array('textContent',            'AtLeastOneContentAreaRequiredValidator'),
                    array('htmlContent',            'AutoresponderMergeTagsValidator', 'except' => 'autoBuildDatabase'),
                    array('textContent',            'AutoresponderMergeTagsValidator', 'except' => 'autoBuildDatabase'),
                    array('fromOperationDurationInterval', 'type', 'type' => 'integer'),
                    array('fromOperationDurationInterval', 'numerical', 'min' => 0),
                    array('fromOperationDurationInterval', 'required'),
                    array('fromOperationDurationType',    'type', 'type' => 'string'),
                    array('fromOperationDurationType',    'required'),
                    array('operationType',          'required'),
                    array('operationType',          'type',    'type' => 'integer'),
                    array('operationType',          'numerical'),
                    array('enableTracking',          'boolean'),
                    array('enableTracking',          'default', 'value' => false),

                ),
                'relations' => array(
                    'autoresponderItems'    => array(RedBeanModel::HAS_MANY, 'AutoresponderItem'),
                    'marketingList'         => array(RedBeanModel::HAS_ONE, 'MarketingList', RedBeanModel::NOT_OWNED),
                    'files'                 => array(RedBeanModel::HAS_MANY,  'FileModel', RedBeanModel::OWNED,
                                                    RedBeanModel::LINK_TYPE_POLYMORPHIC, 'relatedModel'),
                ),
                'elements' => array(
                    'htmlContent'                   => 'TextArea',
                    'textContent'                   => 'TextArea',
                    'enableTracking'                => 'CheckBox',
                ),
                'defaultSortAttribute' => 'fromOperationDurationInterval',
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getByOperationType($operationType, $pageSize = null)
        {
            assert('is_int($operationType)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'operationType',
                    'operatorType'         => 'equals',
                    'value'                => $operationType,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, $pageSize, $where);
        }

        public static function getByOperationTypeAndMarketingListId($operationType, $marketingListId, $pageSize = null)
        {
            assert('is_int($operationType)');
            assert('is_int($marketingListId)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'operationType',
                    'operatorType'              => 'equals',
                    'value'                     => $operationType,
                ),
                2 => array(
                    'attributeName'             => 'marketingList',
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => $marketingListId,
                ),
            );
            $searchAttributeData['structure'] = '(1 and 2)';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, $pageSize, $where);
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'subject'               => Zurmo::t('EmailMessagesModule', 'Subject', null,  null, $language),
                    'operationType'         => Zurmo::t('Core', 'Triggered By', null,  null, $language),
                    'fromOperationDurationInterval' => Zurmo::t('AutorespondersModule', 'Send After', null,  null, $language),
                    'htmlContent'           => Zurmo::t('EmailMessagesModule', 'Html Content', null,  null, $language),
                    'textContent'           => Zurmo::t('EmailMessagesModule', 'Text Content', null,  null, $language),
                    'enableTracking'        => Zurmo::t('AutorespondersModule', 'Enable Tracking', null,  null, $language),
                )
            );
        }

        /**
         * @param integer $initialTimeStamp
         * @return integer timestamp based on durationInterval, durationSign, and durationType
         */
        public function resolveNewTimeStampForDuration($initialTimeStamp)
        {
            assert('is_int($initialTimeStamp)');
            return TimeDurationUtil::resolveNewTimeStampForDuration($initialTimeStamp, (int)$this->fromOperationDurationInterval,
                        TimeDurationUtil::DURATION_SIGN_POSITIVE, $this->fromOperationDurationType);
        }

        public function __toString()
        {
            return strval($this->subject);
        }
    }
?>