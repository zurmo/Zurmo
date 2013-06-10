<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    abstract class EmailMessageActivitiesDemoDataMaker extends DemoDataMaker
    {
        protected $emailBox;

        public function __construct()
        {
            $user           = User::getByUsername('super');
            $this->emailBox = EmailBoxUtil::getDefaultEmailBoxByUser($user);
        }

        protected function populateMarketingItems($marketingItemClassName)
        {
            foreach ($marketingItemClassName::getAll() as $marketingItem)
            {
                $marketingItem->emailMessage = $this->makeEmailMessage($marketingItem->contact);
                $saved = $marketingItem->unrestrictedSave();
                if (!$saved)
                {
                    throw new NotSupportedException();
                }
            }
        }

        protected function makeEmailMessage(Contact $contact)
        {
            $interval = mt_rand(1, 30) * 86400;
            //#1 Create Archived - Sent
            $emailMessage              = new EmailMessage();
            $emailMessage->setScenario('importModel');
            $emailMessage->owner       = Yii::app()->user->userModel;
            $emailMessage->subject     = 'A test archived sent email';
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My First Message';
            $emailContent->htmlContent = 'Some fake HTML content';
            $emailMessage->content     = $emailContent;
            //Sending is current user (super)
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'super@zurmotest.com';
            $sender->fromName          = 'Super User';
            $sender->personOrAccount   = Yii::app()->user->userModel;
            $emailMessage->sender      = $sender;
            //Recipient is BobMessage
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'bob.message@zurmotest.com';
            $recipient->toName          = strval($contact);
            $recipient->personOrAccount = $contact;
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $emailMessage->recipients->add($recipient);
            $emailMessage->folder       = EmailFolder::getByBoxAndType($this->emailBox, EmailFolder::TYPE_ARCHIVED);
            $emailMessage->sentDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - $interval);
            $emailMessage->createdDateTime = $emailMessage->sentDateTime;
            $saved = $emailMessage->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            return $emailMessage;
        }
    }
?>