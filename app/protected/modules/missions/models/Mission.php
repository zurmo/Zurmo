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
     * Class for creating Mission models.  A mission is similar to a task except a user can only have one mission at
     * a time and a mission cannot be assigned.  A user must take a mission.
     */
    class Mission extends OwnedSecurableItem implements MashableActivityInterface, MashableInboxInterface
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

        public static function getMashableInboxRulesType()
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
                    'reward',
                    'status',
                ),
                'relations' => array(
                    'comments'                    => array(RedBeanModel::HAS_MANY,  'Comment', RedBeanModel::OWNED,
                                                        RedBeanModel::LINK_TYPE_POLYMORPHIC, 'relatedModel'),
                    'files'                       => array(RedBeanModel::HAS_MANY,  'FileModel', RedBeanModel::OWNED,
                                                        RedBeanModel::LINK_TYPE_POLYMORPHIC, 'relatedModel'),
                    'takenByUser'                 => array(RedBeanModel::HAS_ONE,   'User', RedBeanModel::NOT_OWNED,
                                                        RedBeanModel::LINK_TYPE_SPECIFIC, 'takenByUser'),
                    'personsWhoHaveNotReadLatest' => array(RedBeanModel::HAS_MANY,  'PersonWhoHaveNotReadLatest',
                                                        RedBeanModel::OWNED),
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
                    array('reward',                   'type', 'type' => 'string'),

                ),
                'elements' => array(
                    'description'       => 'TextArea',
                    'dueDateTime'       => 'DateTime',
                    'files'             => 'Files',
                    'latestDateTime'    => 'DateTime',
                    'reward'            => 'TextArea',
                ),
                'defaultSortAttribute' => 'description',
                'noAudit' => array(
                    'description',
                    'dueDateTime',
                    'latestDateTime',
                    'reward',
                ),
            );
            return $metadata;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'comments'        => Zurmo::t('CommentsModule', 'Comments', array(), null, $language),
                    'description'     => Zurmo::t('ZurmoModule',    'Description', array(), null, $language),
                    'dueDateTime'     => Zurmo::t('TasksModule', 'Due On', array(), null, $language),
                    'files'           => Zurmo::t('ZurmoModule', 'Files', array(), null, $language),
                    'latestDateTime'  => Zurmo::t('ActivitiesModule', 'Latest Date Time', array(), null, $language),
                    'personsWhoHaveNotReadLatest' => Zurmo::t('MissionsModule', 'Persons Who Have Not Read Latest', array(), null, $language),
                    'reward'          => Zurmo::t('MissionsModule', 'Reward', array(), null, $language),
                    'status'          => Zurmo::t('MissionsModule', 'Status', array(), null, $language),
                    'takenByUser'     => Zurmo::t('MissionsModule', 'Taken By User', array(), null, $language),
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
            $missionRules = new MissionMashableInboxRules();
            $personsToAddAsHaveNotReadLatest = array();
            if (parent::beforeSave())
            {
                if ($this->getIsNewModel())
                {
                    $this->unrestrictedSet('latestDateTime', DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
                    $personsToAddAsHaveNotReadLatest = MissionsUtil::resolvePeopleToSendNotificationToOnNewMission($this);
                }
                if (isset($this->originalAttributeValues['status']) &&
                    $this->originalAttributeValues['status'] != $this->status &&
                    $this->status == self::STATUS_TAKEN)
                {
                    MissionsUtil::markAllUserHasReadLatestExceptOwnerAndTakenBy($this);
                }
                if ($this->comments->isModified())
                {
                    $this->unrestrictedSet('latestDateTime', DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
                    foreach ($this->comments as $comment)
                    {
                        if ($comment->id < 0)
                        {
                            if (Yii::app()->user->userModel != $this->owner)
                            {
                                $this->sendOwnerUnreadCommentNotification = true;
                            }
                            if (Yii::app()->user->userModel != $this->takenByUser && $this->takenByUser->id > 0)
                            {
                                $this->sendTakenByUserUnreadCommentNotification = true;
                            }
                        }
                    }
                    $people = MissionsUtil::resolvePeopleToSendNotificationToOnNewComment($this, Yii::app()->user->userModel);
                    foreach ($people as $person)
                    {
                        if ($missionRules->hasUserReadLatest($this, $person))
                        {
                            if (!in_array($person, $personsToAddAsHaveNotReadLatest))
                            {
                                $personsToAddAsHaveNotReadLatest[] = $person;
                            }
                        }
                    }
                }
                foreach ($personsToAddAsHaveNotReadLatest as $person)
                {
                    $personWhoHaveNotReadLatest = $missionRules->makePersonWhoHasNotReadLatest($person);
                    $personsToAddAsHaveNotReadLatest[] = $personWhoHaveNotReadLatest;
                    $this->personsWhoHaveNotReadLatest->add($personWhoHaveNotReadLatest);
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
            parent::afterSave();
        }

        public static function hasRelatedItems()
        {
            return false;
        }
    }
?>