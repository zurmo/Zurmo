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
     * Override class is used specifically by the
     * testing framework to handle testing of inbound and outbound email.
     */
    class EmailHelperForTesting extends EmailHelper
    {
        public $sendEmailThroughTransport = false;

        /**
         * Override to avoid actually sending emails out through transport.
         * (non-PHPdoc)
         * @see EmailHelper::sendEmail()
         */
        protected function sendEmail(Mailer $mailer, EmailMessage $emailMessage)
        {
            if (!$this->sendEmailThroughTransport)
            {
                $emailMessage->error    = null;
                $emailMessage->folder   = EmailFolder::getByBoxAndType($emailMessage->folder->emailBox, EmailFolder::TYPE_SENT);
            }
            else
            {
                parent::sendEmail($mailer, $emailMessage);
            }
        }

        //For testing only
        public function getSentCount()
        {
            return count(EmailMessage::getAllByFolderType(EmailFolder::TYPE_SENT));
        }

        /**
         * For testing only
         * @param string $subject
         * @param string $from
         * @param mixed(string || array) $to
         * @param string $textContent
         * @param string $htmlContent
         * @param mixed(string || array) $cc
         * @param mixed(string || array) $bcc
         * @param array $attachments
         * @param array $settings
         */
        public function sendRawEmail($subject, $from, $to, $textContent = '', $htmlContent = '', $cc = null, $bcc = null, $attachments = null, $settings = null)
        {
            assert('is_string($subject) && $subject != ""');
            assert('is_string($from)    && $from != ""');
            assert('(is_array($to) || is_string($to)) && !empty($to)');
            assert('is_string($textContent)');
            assert('is_string($htmlContent)');
            assert('$textContent != ""  || $htmlContent != ""');
            assert('is_array($cc)       || !isset($cc)');
            assert('is_array($bcc)      || !isset($bcc)');
            assert('is_array($attachments) || !isset($attachments)');

            $mailer           = $this->getOutboundMailer();
            if (!$settings)
            {
                $mailer->mailer   = $this->outboundType;
                $mailer->host     = $this->outboundHost;
                $mailer->port     = $this->outboundPort;
                $mailer->username = $this->outboundUsername;
                $mailer->password = $this->outboundPassword;
                $mailer->security = $this->outboundSecurity;
            }
            else
            {
                //$mailer->mailer   = $settings['outboundType'];
                $mailer->host     = $settings['outboundHost'];
                $mailer->port     = $settings['outboundPort'];
                $mailer->username = $settings['outboundUsername'];
                $mailer->password = $settings['outboundPassword'];
                $mailer->security = $settings['outboundSecurity'];
            }

            $mailer->Subject  = $subject;
            if ($htmlContent == null && $textContent != null)
            {
                $mailer->body     = $textContent;
                $mailer->altBody  = $textContent;
            }
            elseif ($htmlContent != null && $textContent == null)
            {
                $mailer->body     = $htmlContent;
            }
            elseif ($htmlContent != null && $textContent != null)
            {
                $mailer->body     = $htmlContent;
                $mailer->altBody  = $textContent;
            }

            $mailer->From = $from;

            if (is_array($to) && !empty($to))
            {
                foreach ($to as $recipientEmail)
                {
                    $mailer->addAddressByType($recipientEmail, '', EmailMessageRecipient::TYPE_TO);
                }
            }
            else
            {
                $mailer->addAddressByType($to, '', EmailMessageRecipient::TYPE_TO);
            }

            if (is_array($cc) && !empty($cc))
            {
                foreach ($cc as $recipientEmail)
                {
                    $mailer->addAddressByType($recipientEmail, '', EmailMessageRecipient::TYPE_CC);
                }
            }

            if (is_array($bcc) && !empty($bcc))
            {
                foreach ($bcc as $recipientEmail)
                {
                    $mailer->addAddressByType($recipientEmail, '', EmailMessageRecipient::TYPE_BCC);
                }
            }

            if (isset($attachments) && !empty($attachments))
            {
                foreach ($attachments as $file)
                {
                    $mailer->attachFromPath($file);
                }
            }

            $acceptedRecipients = $mailer->send();
            if ($acceptedRecipients > 0)
            {
                // Do nothing
            }
            else
            {
                // To-Do: make exception or something else
                echo "There was error while sending email";
            }
        }
    }
?>