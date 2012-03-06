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

    class EmailMessage extends OwnedSecurableItem implements MashableActivityInterface
    {
        public static function getMashableActivityRulesType()
        {
            return 'EmailMessage';
        }

        public static function getAllByFolderType($type)
        {
            assert('is_string($type)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'folder',
                    'relatedAttributeName' => 'type',
                    'operatorType'         => 'equals',
                    'value'                => $type,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('EmailMessage');
            $where = RedBeanModelDataProvider::makeWhere('EmailMessage', $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, null, $where, null);
        }

        public function __toString()
        {
            if (trim($this->subject) == '')
            {
                return Yii::t('Default', '(Unnamed)');
            }
            return $this->subject;
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
                    'subject',
                    'type',
                ),
                'relations' => array(
                    'folder'      => array(RedBeanModel::HAS_ONE, 'EmailFolder'),
                    'content'     => array(RedBeanModel::HAS_ONE, 'EmailMessageContent',    RedBeanModel::OWNED),
                    'files'       => array(RedBeanModel::HAS_MANY, 'EmailFileModel',        RedBeanModel::OWNED),
                    'sender'      => array(RedBeanModel::HAS_ONE, 'EmailMessageSender',     RedBeanModel::OWNED),
                    'recipients'  => array(RedBeanModel::HAS_MANY, 'EmailMessageRecipient', RedBeanModel::OWNED),
                ),
                'rules' => array(
                    array('subject', 'required'),
                    array('subject', 'type',    'type' => 'string'),
                    array('subject', 'length',  'min'  => 3, 'max' => 255),
                    array('folder', 'required'),
                    array('sender', 'required'),
                )
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }
    }
?>