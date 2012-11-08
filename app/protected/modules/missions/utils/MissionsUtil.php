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
     * Helper class for working with missions
     */
    class MissionsUtil
    {
        /**
         * Renders string content for the mission description or the latest mission comment
         * if it exists.
         * @param Mission $mission
         * @return string
         */
        public static function renderDescriptionAndLatestForDisplayView(Mission $mission)
        {
            $url      = Yii::app()->createUrl('/missions/default/details', array('id' => $mission->id));
            $content  = $mission->description;
            $details  = '<span class="list-item-details">' . Yii::t('Default', 'Updated') . ': ' .
                        DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($mission->latestDateTime) .
                        '</span>';
            $link     = ZurmoHtml::link($content, $url);
            return $link . $details;
        }

        /**
         * Given a mission and a user, mark ownerHasReadLatest true if the user is the owner, if the user is the takenByUser
         * then mark the takenByUserHasReadLatest as true, otherwise do nothing.
         * @param Mission $mission
         * @param User $user
         */
        public static function markUserHasReadLatest(Mission $mission, User $user)
        {
            assert('$mission->id > 0');
            assert('$user->id > 0');
            $save = false;
            if ($user == $mission->owner)
            {
                if (!$mission->ownerHasReadLatest)
                {
                    $mission->ownerHasReadLatest = true;
                    $save                        = true;
                }
            }
            elseif ($user == $mission->takenByUser)
            {
                if (!$mission->takenByUserHasReadLatest)
                {
                    $mission->takenByUserHasReadLatest = true;
                    $save                               = true;
                }
            }
            if ($save)
            {
                $mission->save();
            }
        }

        public static function hasUserReadMissionLatest(Mission $mission, User $user)
        {
            assert('$mission->id > 0');
            assert('$user->id > 0');
            if ($user->isSame($mission->owner))
            {
                return $mission->ownerHasReadLatest;
            }
            elseif ($user == $mission->takenByUser)
            {
                return $mission->takenByUserHasReadLatest;
            }
            return false;
        }

        public static function makeActiveActionElementType($type)
        {
            assert('$type == null || is_int($type)');
            if ($type == null)
            {
                $type = MissionsListConfigurationForm::LIST_TYPE_AVAILABLE;
            }
            if ($type == MissionsListConfigurationForm::LIST_TYPE_CREATED)
            {
                return 'MissionsCreatedLink';
            }
            elseif ($type == MissionsListConfigurationForm::LIST_TYPE_AVAILABLE)
            {
                return 'MissionsAvailableLink';
            }
            elseif ($type == MissionsListConfigurationForm::LIST_TYPE_MINE_TAKEN_BUT_NOT_ACCEPTED)
            {
                return 'MissionsMineTakenButNotAcceptedLink';
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public static function makeDataProviderByType(Mission $mission, $type, $pageSize)
        {
            if ($type == null)
            {
                $type = MissionsListConfigurationForm::LIST_TYPE_AVAILABLE;
            }
            $searchAttributes = array();
            $metadataAdapter  = new MissionsSearchDataProviderMetadataAdapter(
                $mission,
                Yii::app()->user->userModel->id,
                $searchAttributes,
                $type
            );
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                $metadataAdapter->getAdaptedMetadata(),
                'Mission',
                'RedBeanModelDataProvider',
                'latestDateTime',
                true,
                $pageSize
            );
            return $dataProvider;
        }

        /**
         * Create and submit a notification when a status changes.
         * @param User $userToReceiveMessage
         * @param integer $missionId
         * @param string $messageContent
         */
        public static function makeAndSubmitStatusChangeNotificationMessage(User $userToReceiveMessage, $missionId, $messageContent)
        {
            assert('$userToReceiveMessage->id > 0');
            assert('is_int($missionId)');
            assert('is_string($messageContent)');
            $message                      = new NotificationMessage();
            $message->htmlContent         = $messageContent;
            $url                          = Yii::app()->createAbsoluteUrl('missions/default/details/',
                                                                array('id' => $missionId));
            $message->htmlContent        .= '-' . ZurmoHtml::link(Yii::t('Default', 'Click Here'), $url);
            $rules                        = new MissionStatusChangeNotificationRules();
            $rules->addUser($userToReceiveMessage);
            $rules->setAllowDuplicates(true);
            NotificationsUtil::submit($message, $rules);
        }

        /**
         * Create at most one notification for a user when there are new unread comments.
         * @param User $userToReceiveMessage
         * @param integer $missionId
         * @param string $messageContent
         */
        public static function makeAndSubmitNewCommentNotificationMessage(User $userToReceiveMessage)
        {
            assert('$userToReceiveMessage->id > 0');
            $message                      = new NotificationMessage();
            $url                          = Yii::app()->createAbsoluteUrl('missions/default/list/');
            $message->htmlContent         = ZurmoHtml::link(Yii::t('Default', 'Click Here'), $url);
            $rules                        = new MissionUnreadCommentNotificationRules();
            $rules->addUser($userToReceiveMessage);
            NotificationsUtil::submit($message, $rules);
        }
    }
?>