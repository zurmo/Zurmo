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
     * Helper class for working with Email Messages.
     *
     */
    class EmailMessageUtil
    {
        /**
         * Given post data and an email message, populate the sender and account on the email message if possible.
         * Also add message recipients and any attachments.
         * @param array $postData
         * @param EmailMessage $emailMessage
         * @param User $userToSendMessagesFrom
         * @return boolean
         */
        public static function resolveEmailMessageFromPostData(Array & $postData,
                                                               CreateEmailMessageForm $emailMessageForm,
                                                               User $userToSendMessagesFrom)
        {
            $postVariableName   = get_class($emailMessageForm);
            Yii::app()->emailHelper->loadOutboundSettingsFromUserEmailAccount($userToSendMessagesFrom);
            $toRecipients = explode(",", $postData[$postVariableName]['recipientsData']['to']); // Not Coding Standard
            static::attachRecipientsToMessage($toRecipients,
                                              $emailMessageForm->getModel(),
                                              EmailMessageRecipient::TYPE_TO);
            if (ArrayUtil::getArrayValue($postData[$postVariableName]['recipientsData'], 'cc') != null)
            {
                $ccRecipients = explode(",", $postData[$postVariableName]['recipientsData']['cc']); // Not Coding Standard
                static::attachRecipientsToMessage($ccRecipients,
                                              $emailMessageForm->getModel(),
                                              EmailMessageRecipient::TYPE_CC);
            }
            if (ArrayUtil::getArrayValue($postData[$postVariableName]['recipientsData'], 'bcc') != null)
            {
                $bccRecipients = explode(",", $postData[$postVariableName]['recipientsData']['bcc']); // Not Coding Standard
                static::attachRecipientsToMessage($bccRecipients,
                                                  $emailMessageForm->getModel(),
                                                  EmailMessageRecipient::TYPE_BCC);
            }
            if (isset($postData['filesIds']))
            {
                static::attachFilesToMessage($postData['filesIds'], $emailMessageForm->getModel());
            }
            $emailAccount                           = EmailAccount::getByUserAndName($userToSendMessagesFrom);
            $sender                                 = new EmailMessageSender();
            $sender->fromName                       = Yii::app()->emailHelper->fromName;
            $sender->fromAddress                    = Yii::app()->emailHelper->fromAddress;
            $sender->personOrAccount                = $userToSendMessagesFrom;
            $emailMessageForm->sender               = $sender;
            $emailMessageForm->account              = $emailAccount;
            $emailMessageForm->content->textContent = EmailMessageUtil::resolveTextContent(
                                                        ArrayUtil::getArrayValue(
                                                            $postData[$postVariableName]['content'], 'htmlContent'),
                                                            null);
            $box                                    = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessageForm->folder               = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX);
            return $emailMessageForm;
        }

        /**
         * Adds recipient emails as recipients to the email message.  If the recipient email already matches
         * an person or account on the email message it will ignore it.
         * @param Array $recipients
         * @param EmailMessage $emailMessage
         * @param integer $type
         */
        public static function attachRecipientsToMessage(Array $recipients, EmailMessage $emailMessage, $type)
        {
            assert('is_int($type)');
            $existingPersonsOrAccounts = array();
            if ($emailMessage->recipients->count() >0)
            {
                foreach ($emailMessage->recipients as $recipient)
                {
                    if ($recipient->personOrAccount != null && $recipient->personOrAccount->id > 0)
                    {
                        $existingPersonsOrAccounts[] = $recipient->personOrAccount->getClassId('Item');
                    }
                }
            }
            foreach ($recipients as $recipient)
            {
                if ($recipient != null)
                {
                    $personsOrAccounts = EmailArchivingUtil::
                                         getPersonsAndAccountsByEmailAddressForUser($recipient, Yii::app()->user->userModel);
                    if (empty($personsOrAccounts))
                    {
                        $personsOrAccounts[] = null;
                    }
                    foreach ($personsOrAccounts as $personOrAccount)
                    {
                            if ($personOrAccount == null || !in_array($personOrAccount->getClassId('Item'), $existingPersonsOrAccounts))
                            {
                                $messageRecipient                   = new EmailMessageRecipient();
                                $messageRecipient->toAddress        = $recipient;
                                $messageRecipient->type             = $type;
                                if ($personOrAccount != null)
                                {
                                    $messageRecipient->toName           = strval($personOrAccount);
                                    $messageRecipient->personOrAccount  = $personOrAccount;
                                    $existingPersonsOrAccounts[] = $personOrAccount->getClassId('Item');
                                }
                                $emailMessage->recipients->add($messageRecipient);
                            }
                    }
                }
            }
        }

        /**
         * @param array $filesIds
         * @param EmailMessage $emailMessage
         */
        public static function attachFilesToMessage(Array $filesIds, $emailMessage)
        {
            foreach ($filesIds as $fileId)
            {
                $attachment = FileModel::getById((int)$fileId);
                $emailMessage->files->add($attachment);
            }
        }

        /**
         * Append the email signature, if a user has one, to the htmlContent of the email message.
         * @param EmailMessage $emailMessage
         * @param User $user
         */
        public static function resolveSignatureToEmailMessage(EmailMessage $emailMessage, User $user)
        {
            if ($user->emailSignatures->count() > 0 && $user->emailSignatures[0]->htmlContent != null)
            {
                $emailMessage->content->htmlContent = '<p><br/></p><p>' . $user->emailSignatures[0]->htmlContent . '</p>';
            }
        }

        /**
         * @param EmailMessage $emailMessage
         * @param User $user
         * @param string $toAddress
         * @param mixed $relatedId
         * @param string $relatedModelClassName
         */
        public static function resolvePersonOrAccountToEmailMessage(EmailMessage $emailMessage, User $user,
                                                                    $toAddress = null, $relatedId = null,
                                                                    $relatedModelClassName = null)
        {
            assert('is_string($toAddress) || $toAddress == null');
            assert('is_int($relatedId) || is_string($relatedId) ||$relatedId == null');
            assert('$relatedModelClassName == "Account" || $relatedModelClassName == "Contact" ||
                    $relatedModelClassName == "User" ||$relatedModelClassName == null');
            if ($toAddress != null && $relatedId != null && $relatedModelClassName != null)
            {
                $personOrAccount                    = $relatedModelClassName::getById((int)$relatedId);
                $messageRecipient                   = new EmailMessageRecipient();
                $messageRecipient->toName           = strval($personOrAccount);
                $messageRecipient->toAddress        = $toAddress;
                $messageRecipient->type             = EmailMessageRecipient::TYPE_TO;
                $messageRecipient->personOrAccount  = $personOrAccount;
                $emailMessage->recipients->add($messageRecipient);
            }
        }

        /**
         * Based on security, render an email address as a clickable link to a modal window or just a mailto: link
         * that will open the user's configured email client.
         * @param EmailMessage $emailAddress
         * @param RedBeanModel $model
         */
        public static function renderEmailAddressAsMailToOrModalLinkStringContent($emailAddress, RedBeanModel $model)
        {
            assert('is_string($emailAddress) || $emailAddress == null');
            if ($emailAddress == null)
            {
                return;
            }
            $userCanAccess   = RightsUtil::canUserAccessModule('EmailMessagesModule', Yii::app()->user->userModel);
            $userCanCreate   = RightsUtil::doesUserHaveAllowByRightName(
                               'EmailMessagesModule',
                               EmailMessagesModule::RIGHT_CREATE_EMAIL_MESSAGES,
                               Yii::app()->user->userModel);
            if (!$userCanAccess || !$userCanCreate)
            {
                $showLink = false;
            }
            else
            {
                $showLink = true;
            }
            if ($showLink && !($model instanceof Account))
            {
                $url               = Yii::app()->createUrl('/emailMessages/default/createEmailMessage',
                                                           array('toAddress'             => $emailAddress,
                                                                 'relatedId'             => $model->id,
                                                                 'relatedModelClassName' => get_class($model)));
                $modalAjaxOptions  = ModalView::getAjaxOptionsForModalLink(
                                     Zurmo::t('EmailMessagesModule', 'Compose Email'), 'modalContainer', 'auto', 800,
                                                                    array(
                                                                        'my' => 'top',
                                                                        'at' => 'bottom',
                                                                        'of' => '#HeaderView'));
                $content           = ZurmoHtml::ajaxLink($emailAddress, $url, $modalAjaxOptions);
            }
            else
            {
                $content           = Yii::app()->format->email($emailAddress);
            }
            return $content;
        }

        public static function resolveTextContent($htmlContent, $textContent)
        {
           if ($htmlContent != null && $textContent == null)
           {
               $purifier = new CHtmlPurifier;
               $purifier->options = array('HTML.Allowed' => 'p,br'); // Not Coding Standard
               $textContent = $purifier->purify($htmlContent);
               $textContent = preg_replace('#<br\s*?/?>#i', "\n"  , $textContent);
               $textContent = preg_replace('#<p\s*?/?>#i',  "\n\n", $textContent);
               $textContent = preg_replace('#</p\s*?/?>#i', ""    , $textContent);
           }
           return $textContent;
        }
    }
?>