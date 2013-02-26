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

    class Conversation extends OwnedSecurableItem implements MashableActivityInterface
    {
        public static function getMashableActivityRulesType()
        {
            return 'Conversation';
        }

        public static function getBySubject($subject)
        {
            assert('is_string($subject) && $subject != ""');
            return self::getSubset(null, null, null, "subject = '$subject'");
        }

        public function __toString()
        {
            try
            {
                if (trim($this->subject) == '')
                {
                    return Zurmo::t('ConversationsModule', '(Unnamed)');
                }
                return $this->subject;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        public function resolveIsClosedForNull()
        {
            if ($this->isClosed == true)
            {
                return 1;
            }
            else
            {
                return 0;
            }
        }

        /**
         * Given a user get the count of conversations that have unread comments.
         * @param object $user User
         */
        public static function getUnreadCountByUser(User $user)
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'ownerHasReadLatest',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => (bool)1
                ),
                2 => array(
                    'attributeName'        => 'owner',
                    'operatorType'         => 'equals',
                    'value'                => $user->id
                ),
                3 => array(
                    'attributeName'        => 'conversationParticipants',
                    'relatedAttributeName' => 'person',
                    'operatorType'         => 'equals',
                    'value'                => $user->getClassId('Item'),
                ),
                4 => array(
                    'attributeName'        => 'conversationParticipants',
                    'relatedAttributeName' => 'hasReadLatest',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => (bool)1
                ),
            );
            $searchAttributeData['structure'] = '((1 and 2) or (3 and 4))';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Conversation');
            $where  = RedBeanModelDataProvider::makeWhere('Conversation', $searchAttributeData, $joinTablesAdapter);
            return self::getCount($joinTablesAdapter, $where, null, true);
        }

        public function onCreated()
        {
            parent::onCreated();
            $this->unrestrictedSet('latestDateTime', DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
        }

        public static function getModuleClassName()
        {
            return 'ConversationsModule';
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'description',
                    'latestDateTime',
                    'subject',
                    'ownerHasReadLatest',
                    'isClosed'
                ),
                'relations' => array(
                    'comments'                 => array(RedBeanModel::HAS_MANY,  'Comment', RedBeanModel::OWNED, 'relatedModel'),
                    'conversationItems'        => array(RedBeanModel::MANY_MANY, 'Item'),
                    'conversationParticipants' => array(RedBeanModel::HAS_MANY,  'ConversationParticipant', RedBeanModel::OWNED),
                    'files'                    => array(RedBeanModel::HAS_MANY,  'FileModel', RedBeanModel::OWNED, 'relatedModel'),
                ),
                'rules' => array(
                    array('description',        'type',    'type' => 'string'),
                    array('latestDateTime',     'required'),
                    array('latestDateTime',     'readOnly'),
                    array('latestDateTime',     'type', 'type' => 'datetime'),
                    array('subject',            'required'),
                    array('subject',            'type',    'type' => 'string'),
                    array('subject',            'length',  'min'  => 3, 'max' => 255),
                    array('ownerHasReadLatest', 'boolean'),
                    array('isClosed',           'boolean'),
                ),
                'elements' => array(
                    'conversationItems' => 'ConversationItem',
                    'description'       => 'TextArea',
                    'files'             => 'Files',
                    'latestDateTime'    => 'DateTime',
                ),
                'defaultSortAttribute' => 'subject',
                'noAudit' => array(
                    'description',
                    'latestDateTime',
                    'subject',
                    'ownerHasReadLatest',
                ),
                'conversationItemsModelClassNames' => array(
                    'Account',
                    'Opportunity',
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function getGamificationRulesType()
        {
            return 'ConversationGamification';
        }

        /**
         * Alter hasReadLatest and/or ownerHasReadLatest based on comments being added.
         * (non-PHPdoc)
         * @see Item::beforeSave()
         */
        protected function beforeSave()
        {
            if (parent::beforeSave())
            {
                if ($this->comments->isModified() || $this->getIsNewModel())
                {
                    $this->unrestrictedSet('latestDateTime', DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
                    if ($this->getIsNewModel())
                    {
                        $this->ownerHasReadLatest = true;
                    }
                }
                if ($this->comments->isModified())
                {
                    foreach ($this->comments as $comment)
                    {
                        if ($comment->id < 0)
                        {
                            if (Yii::app()->user->userModel != $this->owner)
                            {
                                $this->ownerHasReadLatest = false;
                            }
                            foreach ($this->conversationParticipants as $position => $participant)
                            {
                                //At this point the createdByUser is not populated yet in the comment, so we can
                                //use the current user.
                                if ($participant->person->getClassId('Item') != Yii::app()->user->userModel->getClassId('Item'))
                                {
                                    $this->conversationParticipants[$position]->hasReadLatest = false;
                                }
                            }
                        }
                    }
                }
                return true;
            }
            else
            {
                return false;
            }
        }

        public static function hasRelatedItems()
        {
            return true;
        }
    }
?>