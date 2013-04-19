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
     * Accounts Module Super User Walkthrough.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class EmailMessagesSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $emailAccount               = EmailAccount::resolveAndGetByUserAndName(
                                          Yii::app()->user->userModel); //So the email is configured
            $emailAccount->fromAddress  = 'super@test.zurmo.com';
            $saved                      = $emailAccount->save();
            assert($saved);  // Not Coding Standard

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            $contact = ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
            $contact->primaryEmail = new Email();
            $contact->primaryEmail->emailAddress = 'test@contact.com';
            $saved = $contact->save();
            assert($saved); // Not Coding Standard
        }

        public function testSuperUserCreateMessageAndViewDetails()
        {
            $super                                = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->emailHelper->outboundHost = 'temporaryForTesting';

            $superContactId     = self::getModelIdByModelNameAndName ('Contact', 'superContact superContactson');
            $contact            = Contact::getById($superContactId);

            //Just going to compose without coming from any specific record
            $this->resetGetArray();
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/createEmailMessage');

            //Go to compose without the email address set but the contact set
            $this->setGetArray(array('relatedId' => $superContactId, 'relatedModelClassName' => 'Contact'));
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/createEmailMessage');

            //Go to compose with the email address set and the contact set
            $this->setGetArray(array('toAddress'              => 'test@contact.com',
                                     'relatedId'              => $superContactId,
                                     'relatedModelClassName'  => 'Contact'));
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/createEmailMessage');

            //confirm there are no email messages currently
            $this->assertEquals(0, count(EmailMessage::getAll()));
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            //Test create email with invalid form
            $createEmailMessageFormData = array('recipientsData' => array('to' => 'test@contact.com'),
                                                'subject'        => '',
                                                'content'        => '');
            $this->setPostArray(array('ajax' => 'edit-form', 'CreateEmailMessageForm' => $createEmailMessageFormData));
            $content = $this->runControllerWithExitExceptionAndGetContent('emailMessages/default/createEmailMessage');

            //Confirm that error messages are displayed
            $this->assertContains(Zurmo::t('emailMessagesModule', 'Subject cannot be blank.'), $content);

            //Confirm that no email messages was sent
            $this->assertEquals(0, count(EmailMessage::getAll()));
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            //Validate form
            $createEmailMessageFormData = array('recipientsData' => array('to' => 'test@contact.com'),
                                                'subject'        => 'test subject',
                                                'content'        => array('htmlContent' => '<p>html body content</p>'));
            $this->setGetArray(array('toAddress'                 => 'test@contact.com',
                                     'relatedId'                 => $superContactId,
                                     'relatedModelClassName'     => 'Contact'));
            $this->setPostArray(array('ajax' => 'edit-form', 'CreateEmailMessageForm' => $createEmailMessageFormData));
            $this->runControllerWithExitExceptionAndGetContent('emailMessages/default/createEmailMessage');

            //create email message
            $this->setGetArray(array('toAddress'               => 'test@contact.com',
                                     'relatedId'               => $superContactId,
                                     'relatedModelClassName'   => 'Contact'));
            $this->setPostArray(array('CreateEmailMessageForm' => $createEmailMessageFormData));
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/createEmailMessage', true);

            //confirm there is one email
            $this->assertEquals(1, count(EmailMessage::getAll()));
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
        }
    }
?>
