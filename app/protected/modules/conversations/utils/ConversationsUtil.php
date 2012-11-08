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
            $details  = '<span class="list-item-details">' . Yii::t('Default', 'Updated') . ': ' .
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
         * Given a conversation and a user, mark that the user has either read the latest comment as a conversation
         * participant, or if the user is the owner, than as the owner.
         * @param Conversation $conversation
         * @param User $user
         */
        public static function markUserHasReadLatest(Conversation $conversation, User $user)
        {
            assert('$conversation->id > 0');
            assert('$user->id > 0');
            $save = false;
            if ($user == $conversation->owner)
            {
                if (!$conversation->ownerHasReadLatest)
                {
                    $conversation->ownerHasReadLatest = true;
                    $save                             = true;
                }
            }
            else
            {
                foreach ($conversation->conversationParticipants as $position => $participant)
                {
                    if ($participant->person->getClassId('Item') == $user->getClassId('Item') && !$participant->hasReadLatest)
                    {
                        $conversation->conversationParticipants[$position]->hasReadLatest = true;
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
    }
?>