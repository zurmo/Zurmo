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
     * Helper class for working with conversations
     */
    class ConversationsUtil
    {
        /**
         * Renders string content for the conversation subject and either the description or latest conversation comment
         * if it exists.
         * @param Conversation $conversation
         * @return string
         */
        public static function renderSubjectAndLatestForDisplayView(Conversation $conversation)
        {
            $url      = Yii::app()->createUrl('/conversations/default/details', array('id' => $conversation->id));
            $content  = $conversation->subject;
            $details  = '<span class="list-item-details">' . Zurmo::t('ConversationsModule', 'Updated') . ': ' .
                                DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($conversation->latestDateTime) .
                                '</span>';
            $link     = ZurmoHtml::link($content, $url);
            return $link . $details;
        }

        /**
         * For the current user, render a string of how many unread conversations exist for the user.
         * @return string
         */
        public static function getUnreadCountTabMenuContentForCurrentUser()
        {
            return Conversation::getUnreadCountByUser(Yii::app()->user->userModel);
        }

        /**
         * Given a conversation and a user, mark that the user has read or not read the latest changes as a conversation
         * participant, or if the user is the owner, than as the owner.
         * @param Conversation $conversation
         * @param User $user
         * @param Boolean $hasReadLatest
         */
        public static function markUserHasReadLatest(Conversation $conversation, User $user, $hasReadLatest = true)
        {
            assert('$conversation->id > 0');
            assert('$user->id > 0');
            assert('is_bool($hasReadLatest)');
            $save = false;
            if ($user->getClassId('Item') == $conversation->owner->getClassId('Item'))
            {
                if ($conversation->ownerHasReadLatest != $hasReadLatest)
                {
                    $conversation->ownerHasReadLatest = $hasReadLatest;
                    $save                             = true;
                }
            }
            else
            {
                foreach ($conversation->conversationParticipants as $position => $participant)
                {
                    if ($participant->person->getClassId('Item') == $user->getClassId('Item') && $participant->hasReadLatest != $hasReadLatest)
                    {
                        $conversation->conversationParticipants[$position]->hasReadLatest = $hasReadLatest;
                        $save                                                             = true;
                    }
                }
            }
            if ($save)
            {
                $conversation->save();
            }
        }

        public static function hasUserReadConversationLatest(Conversation $conversation, User $user)
        {
            assert('$conversation->id > 0');
            assert('$user->id > 0');
            if ($user->isSame($conversation->owner))
            {
                return $conversation->ownerHasReadLatest;
            }
            else
            {
                foreach ($conversation->conversationParticipants as $position => $participant)
                {
                    if ($participant->person->getClassId('Item') == $user->getClassId('Item'))
                    {
                        return $participant->hasReadLatest;
                    }
                }
            }
            return false;
        }

        public static function resolvePeopleOnConversation(Conversation $conversation)
        {
            $people   = ConversationParticipantsUtil::getConversationParticipants($conversation);
            $people[] = $conversation->owner;
            return $people;
        }

        /**
         * Given a Conversation and the User that created the new comment
         * return the people on the conversation to send new notification to
         * @param Conversation $conversation
         * @param User $user
         * @return Array $peopleToSendNotification
         */
        public static function  resolvePeopleToSendNotificationToOnNewComment(Conversation $conversation, User $user)
        {
            $peopleToSendNotification = array();
            $peopleOnConversation     = self::resolvePeopleOnConversation($conversation);
            foreach ($peopleOnConversation as $people)
            {
                if (!$people->isSame($user))
                {
                    $peopleToSendNotification[] = $people;
                }
            }
            return $peopleToSendNotification;
        }
    }
?>