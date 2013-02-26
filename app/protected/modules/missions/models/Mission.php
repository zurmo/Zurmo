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
     * Class for creating Mission models.  A mission is similar to a task except a user can only have one mission at
     * a time and a mission cannot be assigned.  A user must take a mission.
     */
    class Mission extends OwnedSecurableItem implements MashableActivityInterface
    {
        const STATUS_AVAILABLE = 1;

        const STATUS_TAKEN     = 2;

        const STATUS_COMPLETED = 3;

        const STATUS_REJECTED  = 4;

        const STATUS_ACCEPTED  = 5;

        private $sendOwnerUnreadCommentNotification = false;

        private $sendTakenByUserUnreadCommentNotification = false;

        public static function getMashableActivityRulesType()
        {
            return 'Mission';
        }

        public function __toString()
        {
            try
            {
                if (trim($this->description) == '')
                {
                    return Zurmo::t('MissionsModule', '(Unnamed)');
                }
                return $this->description;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        public function onCreated()
        {
            parent::onCreated();
            $this->unrestrictedSet('latestDateTime', DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
        }

        public static function getModuleClassName()
        {
            return 'MissionsModule';
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
                    'dueDateTime',
                    'latestDateTime',
                    'ownerHasReadLatest',
                    'reward',
                    'status',
                    'takenByUserHasReadLatest',
                ),
                'relations' => array(
                    'comments'                 => array(RedBeanModel::HAS_MANY,  'Comment', RedBeanModel::OWNED, 'relatedModel'),
                    'files'                    => array(RedBeanModel::HAS_MANY,  'FileModel', RedBeanModel::OWNED, 'relatedModel'),
                    'takenByUser'              => array(RedBeanModel::HAS_ONE,   'User'),
                ),
                'rules' => array(
                    array('description',              'required'),
                    array('description',              'type', 'type' => 'string'),
                    array('dueDateTime',              'type', 'type' => 'datetime'),
                    array('latestDateTime',           'required'),
                    array('latestDateTime',           'readOnly'),
                    array('latestDateTime',           'type', 'type' => 'datetime'),
                    array('status',                   'required'),
                    array('status',                   'type',    'type' => 'integer'),
                    array('ownerHasReadLatest',       'boolean'),
                    array('reward',                   'type', 'type' => 'string'),
                    array('takenByUserHasReadLatest', 'boolean'),

                ),
                'elements' => array(
                    'description'       => 'TextArea',
                    'dueDateTime'       => 'DateTime',
                    'files'             => 'Files',
                    'latestDateTime'    => 'DateTime',
                    'reward'            => 'TextArea',
                ),
                'defaultSortAttribute' => 'subject',
                'noAudit' => array(
                    'description',
                    'dueDateTime',
                    'latestDateTime',
                    'ownerHasReadLatest',
                    'reward',
                    'takenByUserHasReadLatest'
                ),
            );
            return $metadata;
        }

        protected function untranslatedAttributeLabels()
        {
            return array_merge(parent::untranslatedAttributeLabels(),
                array(
                    'dueDateTime'       => 'Due On',
                )
            );
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
            return 'MissionGamification';
        }

        /**
         * Alter takenByUserHasReadLatest and/or ownerHasReadLatest based on comments being added.
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
                                $this->ownerHasReadLatest                 = false;
                                $this->sendOwnerUnreadCommentNotification = true;
                            }
                            if (Yii::app()->user->userModel != $this->takenByUser && $this->takenByUser->id > 0)
                            {
                                $this->takenByUserHasReadLatest                 = false;
                                $this->sendTakenByUserUnreadCommentNotification = true;
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

        /**
         * After a mission is saved, if it is new, then a notification should go out to all users alerting them
         * of a new mission.  Depending on the status change of the mission, a notification can go out as well to
         * the owner or user who has taken the mission.
         */
        protected function afterSave()
        {
            if ($this->isNewModel && $this->getScenario() != 'autoBuildDatabase' &&
                $this->getScenario() != 'importModel')
            {
                MissionsUtil::makeAndSubmitNewMissionNotificationMessage($this);
            }
            if (((isset($this->originalAttributeValues['status'])) && !$this->isNewModel) &&
                $this->originalAttributeValues['status'] != $this->status)
            {
                if ($this->status == self::STATUS_TAKEN)
                {
                    $messageContent = Zurmo::t('MissionsModule', 'A mission you created has been taken on by {takenByUserName}',
                                                        array('{takenByUserName}' => strval($this->takenByUser)));
                    MissionsUtil::makeAndSubmitStatusChangeNotificationMessage($this->owner, $this->id, $messageContent);
                }
                elseif ($this->status == self::STATUS_COMPLETED)
                {
                    $messageContent = Zurmo::t('MissionsModule', 'A mission you created has been completed');
                    MissionsUtil::makeAndSubmitStatusChangeNotificationMessage($this->owner, $this->id, $messageContent);
                }
                elseif ($this->status == self::STATUS_REJECTED && $this->takenByUser->id > 0)
                {
                    $messageContent = Zurmo::t('MissionsModule', 'A mission you completed has been rejected');
                    MissionsUtil::makeAndSubmitStatusChangeNotificationMessage($this->takenByUser, $this->id, $messageContent);
                }
                elseif ($this->status == self::STATUS_ACCEPTED && $this->takenByUser->id > 0)
                {
                    $messageContent = Zurmo::t('MissionsModule', 'A mission you completed has been accepted');
                    MissionsUtil::makeAndSubmitStatusChangeNotificationMessage($this->takenByUser, $this->id, $messageContent);
                }
            }
            if ($this->getScenario() != 'importModel' && $this->sendOwnerUnreadCommentNotification)
            {
                MissionsUtil::makeAndSubmitNewCommentNotificationMessage($this->owner);
            }
            elseif ($this->getScenario() != 'importModel' &&
                   $this->sendTakenByUserUnreadCommentNotification && $this->takenByUser->id > 0)
            {
                MissionsUtil::makeAndSubmitNewCommentNotificationMessage($this->takenByUser);
            }
            parent::afterSave();
        }

        public static function hasRelatedItems()
        {
            return false;
        }
    }
?>