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
     * Helper class for conversation participant logic.
     */
    class ConversationParticipantsUtil
    {
        /**
         * Given a Conversation and User, determine if the user is already a conversationParticipant.
         * @param Conversation $model
         * @param User $user
         */
        public static function isUserAParticipant(Conversation $model, User $user)
        {
            if ($model->conversationParticipants->count() > 0)
            {
                foreach ($model->conversationParticipants as $participant)
                {
                    if ($participant->person->getClassId('Item') == $user->getClassId('Item'))
                    {
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * Based on the post data, resolve the conversation participants. While this is being resolved also
         * resolve the correct read/write permissions.
         * @param object $model - Conversation Model
         * @param array $postData
         * @param object $explicitReadWriteModelPermissions - ExplicitReadWriteModelPermissions model
         * @returns Array of persons who have been added as participants
         */
        public static function resolveConversationHasManyParticipantsFromPost(
                                    Conversation $conversation, $postData, $explicitReadWriteModelPermissions)
        {
            assert('$explicitReadWriteModelPermissions instanceof ExplicitReadWriteModelPermissions');
            $newPeopleIndexedByItemId = array();
            if (isset($postData['itemIds']) && strlen($postData['itemIds']) > 0)
            {
                $itemIds = explode(",", $postData['itemIds']);  // Not Coding Standard
                foreach ($itemIds as $itemId)
                {
                    if ($itemId != $conversation->owner->getClassId('Item'))
                    {
                        $newPeopleIndexedByItemId[$itemId] = static::castDownItem(Item::getById((int)$itemId));
                    }
                }
                if ($conversation->conversationParticipants->count() > 0)
                {
                    $participantsToRemove = array();
                    foreach ($conversation->conversationParticipants as $index => $existingParticipantModel)
                    {
                        if (!isset($newPeopleIndexedByItemId[$existingParticipantModel->person->getClassId('Item')]))
                        {
                            $participantsToRemove[] = $existingParticipantModel;
                        }
                        else
                        {
                            unset($newPeopleIndexedByItemId[$existingParticipantModel->person->getClassId('Item')]);
                        }
                    }
                    foreach ($participantsToRemove as $participantModelToRemove)
                    {
                        $conversation->conversationParticipants->remove($participantModelToRemove);
                        $person = static::castDownItem($participantModelToRemove->person);
                        if ($person instanceof Permitable)
                        {
                            $explicitReadWriteModelPermissions->addReadWritePermitableToRemove($person);
                        }
                    }
                }
                //Now add missing participants
                foreach ($newPeopleIndexedByItemId as $personOrUserModel)
                {
                    $conversation->conversationParticipants->add(static::makeConversationParticipantByPerson($personOrUserModel));
                    if ($personOrUserModel instanceof Permitable)
                    {
                        $explicitReadWriteModelPermissions->addReadWritePermitable($personOrUserModel);
                    }
                }
            }
            else
            {
                //remove all participants
                $conversation->conversationParticipants->removeAll();
                $explicitReadWriteModelPermissions->removeAllReadWritePermitables();
            }
            return $newPeopleIndexedByItemId;
        }

        public static function resolveEmailInvitesByPeople($conversation, $people)
        {
            assert('$conversation instanceof Conversation && $conversation->id > 0');
            if (count($people) == 0)
            {
                return;
            }
            foreach ($people as $personOrUserModel)
            {
                static::sendEmailInviteToParticipant($conversation, $personOrUserModel);
            }
        }

        protected static function castDownItem(Item $item)
        {
            foreach (array('Contact', 'User') as $modelClassName)
            {
                try
                {
                    $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($modelClassName);
                    return $item->castDown(array($modelDerivationPathToItem));
                }
                catch (NotFoundException $e)
                {
                }
            }
            throw new NotSupportedException();
        }

        protected static function makeConversationParticipantByPerson($personOrUserModel)
        {
            assert('$personOrUserModel instanceof User || $personOrUserModel instanceof Person');
            $conversationParticipant                = new ConversationParticipant();
            $conversationParticipant->hasReadLatest = false;
            $conversationParticipant->person        = $personOrUserModel;
            return $conversationParticipant;
        }

        public static function sendEmailInviteToParticipant(Conversation $conversation, $person)
        {
            assert('$conversation->id > 0');
            assert('$person instanceof User || $person instanceof Contact');
            if ($person->primaryEmail->emailAddress !== null &&
                (($person instanceof User &&
                !UserConfigurationFormAdapter::resolveAndGetValue($person, 'turnOffEmailNotifications')) ||
                 $person instanceof Contact))
            {
                $userToSendMessagesFrom     = $conversation->owner;
                $emailMessage               = new EmailMessage();
                $emailMessage->owner        = Yii::app()->user->userModel;
                $emailMessage->subject      = Zurmo::t('ConversationsModule', 'You have been invited to participate in a conversation');
                $emailContent               = new EmailMessageContent();
                $emailContent->textContent  = EmailNotificationUtil::
                                                resolveNotificationTextTemplate(
                                                static::getParticipantInviteEmailTextContent($conversation));
                $emailContent->htmlContent  = EmailNotificationUtil::
                                                resolveNotificationHtmlTemplate(
                                                static::getParticipantInviteEmailHtmlContent($conversation));
                $emailMessage->content      = $emailContent;
                $sender                     = new EmailMessageSender();
                $sender->fromAddress        = Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
                $sender->fromName           = strval($userToSendMessagesFrom);
                $sender->personOrAccount    = $userToSendMessagesFrom;
                $emailMessage->sender       = $sender;
                $recipient                  = new EmailMessageRecipient();
                $recipient->toAddress       = $person->primaryEmail->emailAddress;
                $recipient->toName          = strval($person);
                $recipient->type            = EmailMessageRecipient::TYPE_TO;
                $recipient->personOrAccount = $person;
                $emailMessage->recipients->add($recipient);
                $box                        = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
                $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
                try
                {
                    Yii::app()->emailHelper->send($emailMessage);
                }
                catch (CException $e)
                {
                    //Not sure what to do yet when catching an exception here. Currently ignoring gracefully.
                }
            }
        }

        protected static function getParticipantInviteEmailTextContent(Conversation $conversation)
        {
            $url     = static::getUrlToConversationDetailAndRelationsView($conversation->id);
            $content = Zurmo::t('ConversationsModule', '{headerStartTag}Join the Conversation{headerEndTag}{headerLineBreak}{ownerName} ' .
                                         'would like you to join a conversation {strongStartTag}"{conversationSubject}"{strongEndTag}',
                               array('{headerStartTag}'      => null,
                                     '{headerEndTag}'        => null,
                                     '{headerLineBreak}'     => "\n\n",
                                     '{strongStartTag}'      => null,
                                     '{strongEndTag}'        => null,
                                     '{ownerName}'           => $conversation->owner,
                                     '{conversationSubject}' => $conversation->subject));
            $content .= "\n\n";
            $content .= ZurmoHtml::link($url, $url);
            return $content;
        }

        protected static function getParticipantInviteEmailHtmlContent(Conversation $conversation)
        {
            $url     = static::getUrlToConversationDetailAndRelationsView($conversation->id);
            $content = Zurmo::t('ConversationsModule', '{headerStartTag}Join the Conversation{headerEndTag}{headerLineBreak}{ownerName} ' .
                                         'would like you to join a conversation {strongStartTag}"{conversationSubject}"{strongEndTag}',
                               array('{headerStartTag}'      => '<h2 class="h2">',
                                     '{headerEndTag}'        => '</h2>',
                                     '{headerLineBreak}'     => null,
                                     '{strongStartTag}'      => '<strong>',
                                     '{strongEndTag}'        => '</strong>',
                                     '{ownerName}'           => $conversation->owner,
                                     '{conversationSubject}' => $conversation->subject));
            $content .= "<br/>";
            $content .= ZurmoHtml::link(Zurmo::t('ConversationsModule', 'Click Here'), $url);
            return $content;
        }

        public static function getUrlToConversationDetailAndRelationsView($id)
        {
            assert('is_int($id)');
            return Yii::app()->createAbsoluteUrl('conversations/default/details/', array('id' => $id));
        }

        public static function getConversationParticipants(Conversation $conversation)
        {
            $participants = array();
            foreach ($conversation->conversationParticipants as $participant)
            {
                $participants[] = static::castDownItem($participant->person);
            }
            return $participants;
        }
    }
?>