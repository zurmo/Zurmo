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
     * Model for storing email folders.
     */
    class EmailFolder extends Item
    {
        const TYPE_INBOX               = 'Inbox';
        const TYPE_SENT                = 'Sent';
        const TYPE_OUTBOX              = 'Outbox';
        const TYPE_DRAFT               = 'Draft';
        const TYPE_OUTBOX_ERROR        = 'OutboxError';
        const TYPE_ARCHIVED            = 'Archived';
        const TYPE_ARCHIVED_UNMATCHED  = 'ArchivedUnmatched';

        public static function getDefaultDraftName()
        {
            return Zurmo::t('EmailMessagesModule', 'Draft');
        }

        public static function getDefaultInboxName()
        {
            return Zurmo::t('EmailMessagesModule', 'Inbox');
        }

        public static function getDefaultSentName()
        {
            return Zurmo::t('EmailMessagesModule', 'Sent');
        }

        public static function getDefaultOutboxName()
        {
            return Zurmo::t('EmailMessagesModule', 'Outbox');
        }

        public static function getDefaultOutboxErrorName()
        {
            return Zurmo::t('EmailMessagesModule', 'Outbox Error');
        }

        public static function getDefaultArchivedName()
        {
            return Zurmo::t('EmailMessagesModule', 'Archived');
        }

        public static function getDefaultArchivedUnmatchedName()
        {
            return Zurmo::t('EmailMessagesModule', 'Archived Unmatched');
        }

        public static function getTranslatedFolderNameByType($type)
        {
            assert('is_string($type)');
            if ($type == self::TYPE_INBOX)
            {
                return self::getDefaultInboxName();
            }
            elseif ($type == self::TYPE_SENT)
            {
                return self::getDefaultSentName();
            }
            elseif ($type == self::TYPE_OUTBOX)
            {
                return self::getDefaultOutboxName();
            }
            elseif ($type == self::TYPE_DRAFT)
            {
                return self::getDefaultDraftName();
            }
            elseif ($type == self::TYPE_OUTBOX_ERROR)
            {
                return self::getDefaultOutboxErrorName();
            }
            elseif ($type == self::TYPE_ARCHIVED)
            {
                return self::getDefaultArchivedName();
            }
            elseif ($type == self::TYPE_ARCHIVED_UNMATCHED)
            {
                return self::getDefaultArchivedUnmatchedName();
            }
            else
            {
                throw new NotSupportedException();
            }
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
                return Zurmo::t('EmailMessagesModule', '(Unnamed)');
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
                    array('type',          'length',  'min'  => 3, 'max' => 20),
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

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'name'   => Zurmo::t('ZurmoModule', 'Name',  array(), null, $language),
                    'type'   => Zurmo::t('Core',        'Type',  array(), null, $language),
                )
            );
        }

        public function beforeDelete()
        {
            if ($this->emailBox->isSpecialBox())
            {
                throw new NotSupportedException();
            }
            return parent::beforeDelete();
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('EmailMessagesModule', 'Email Folder', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('EmailMessagesModule', 'Email Folders', array(), null, $language);
        }
    }
?>