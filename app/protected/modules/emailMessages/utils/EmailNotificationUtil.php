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

    class EmailNotificationUtil
    {
        /**
         * Based on the current theme, retrieve the email notification template for html content and replace the
         * content tags with the appropriate strings
         */
        public static function resolveNotificationHtmlTemplate($bodyContent)
        {
            assert('is_string($bodyContent)');
            $url                                = Yii::app()->createAbsoluteUrl('users/default/configurationEdit',
                                                  array('id' => Yii::app()->user->userModel->id));
            $htmlTemplate                       = self::getNotificationHtmlTemplate();
            $htmlContent                        = array();
            $htmlContent['{bodyContent}']       = $bodyContent;
            $htmlContent['{sourceContent}']     = Zurmo::t('EmailMessagesModule', 'This message sent from Zurmo');
            $htmlContent['{preferenceContent}'] = ZurmoHtml::link(Zurmo::t('EmailMessagesModule', 'Manage your email preferences'), $url);
            return strtr($htmlTemplate, $htmlContent);
        }

        protected static function getNotificationHtmlTemplate()
        {
            $theme        = Yii::app()->theme->name;
            $name         = 'NotificationEmailTemplate';
            $templateName = "themes/$theme/templates/$name.html";
            if (!file_exists($templateName))
            {
                $templateName = "themes/default/templates/$name.html";
            }
            if (file_exists($templateName))
            {
                return file_get_contents($templateName);
            }
        }

        /**
         * Based on the current theme, retrieve the email notification template for text content and replace the
         * content tags with the appropriate strings
         */
        public static function resolveNotificationTextTemplate($bodyContent)
        {
            assert('is_string($bodyContent)');
            $url                                = Yii::app()->createAbsoluteUrl('users/default/configurationEdit',
                                                  array('id' => Yii::app()->user->userModel->id));
            $htmlTemplate                       = self::getNotificationTextTemplate();
            $htmlContent                        = array();
            $htmlContent['{bodyContent}']       = $bodyContent;
            $htmlContent['{sourceContent}']     = Zurmo::t('EmailMessagesModule', 'This message sent from Zurmo');
            $htmlContent['{preferenceContent}'] = Zurmo::t('EmailMessagesModule', 'Manage your email preferences') . ZurmoHtml::link(null, $url);
            return strtr($htmlTemplate, $htmlContent);
        }

        protected static function getNotificationTextTemplate()
        {
            $theme        = Yii::app()->theme->name;
            $name         = 'NotificationEmailTemplate';
            $templateName = "themes/$theme/templates/$name.txt";
            if (!file_exists($templateName))
            {
                $templateName = "themes/default/templates/$name.txt";
            }
            if (file_exists($templateName))
            {
                return file_get_contents($templateName);
            }
        }

        public static function resolveAndSendEmail($senderPerson, $recipients, $subject, $content)
        {
            assert('$senderPerson instanceof User');
            assert('is_array($recipients)');
            assert('is_string($subject)');
            assert('$content instanceof EmailMessageContent');
            if (count($recipients) == 0)
            {
                return;
            }
            $userToSendMessagesFrom     = $senderPerson;
            $emailMessage               = new EmailMessage();
            $emailMessage->owner        = $senderPerson;
            $emailMessage->subject      = $subject;
            $emailMessage->content      = $content;
            $sender                     = new EmailMessageSender();
            $sender->fromAddress        = Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
            $sender->fromName           = strval($userToSendMessagesFrom);
            $sender->personOrAccount    = $userToSendMessagesFrom;
            $emailMessage->sender       = $sender;
            foreach ($recipients as $recipientPerson)
            {
                $recipient                  = new EmailMessageRecipient();
                $recipient->toAddress       = $recipientPerson->primaryEmail->emailAddress;
                $recipient->toName          = strval($recipientPerson);
                $recipient->type            = EmailMessageRecipient::TYPE_TO;
                $recipient->personOrAccount = $recipientPerson;
                $emailMessage->recipients->add($recipient);
            }
            $box                        = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
            Yii::app()->emailHelper->send($emailMessage);
        }
    }
?>