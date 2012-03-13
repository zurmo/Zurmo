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

    /**
     * Model for storing email folders.
     */
    class EmailFolder extends Item
    {
        const TYPE_INBOX        = 'Inbox';
        const TYPE_SENT         = 'Sent';
        const TYPE_OUTBOX       = 'Outbox';
        const TYPE_DRAFT        = 'Draft';
        const TYPE_OUTBOX_ERROR = 'OutboxError';

        public static function getDefaultDraftName()
        {
            return Yii::t('Default', 'Draft');
        }

        public static function getDefaultInboxName()
        {
            return Yii::t('Default', 'Inbox');
        }

        public static function getDefaultSentName()
        {
            return Yii::t('Default', 'Sent');
        }

        public static function getDefaultOutboxName()
        {
            return Yii::t('Default', 'Outbox');
        }

        public static function getDefaultOutboxErrorName()
        {
            return Yii::t('Default', 'Outbox Error');
        }

        public static function getByBoxAndType(EmailBox $box, $type)
        {
            assert('$box->id > 0');
            assert('is_string($type)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'type',
                    'operatorType'         => 'equals',
                    'value'                => $type,
                ),
                2 => array(
                    'attributeName'        => 'emailBox',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $box->id,
                ),
            );
            $searchAttributeData['structure'] = '1 and 2';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('EmailFolder');
            $where = RedBeanModelDataProvider::makeWhere('EmailFolder', $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, null, $where, null);
            if (count($models) == 0)
            {
                throw new NotFoundException();
            }
            elseif (count($models) > 1)
            {
                throw new NotSupportedException();
            }
            else
            {
                return $models[0];
            }
        }

        public function __toString()
        {
            if (trim($this->name) == '')
            {
                return Yii::t('Default', '(Unnamed)');
            }
            return $this->name;
        }

        public static function getModuleClassName()
        {
            return 'EmailMessagesModule';
        }

        public static function canSaveMetadata()
        {
            return false;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                    'type',
                ),
                'relations' => array(
                    'emailBox' => array(RedBeanModel::HAS_ONE, 'EmailBox'),
                ),
                'rules' => array(
                    array('name',          'required'),
                    array('name',          'type',    'type' => 'string'),
                    array('name',          'length',  'min'  => 3, 'max' => 64),
                    array('type',          'type',    'type' => 'string'),
                    array('type',          'length',  'min'  => 3, 'max' => 12),
                    //If we didn't need emailBox required,
                    //we could use HAS_MANY_BELONGS_TO as the emailBox relation
                    array('emailBox',      'required'),
                )
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public function beforeDelete()
        {
            if ($this->emailBox->isSpecialBox())
            {
                throw new NotSupportedException();
            }
            parent::beforeDelete();
        }
    }
?>