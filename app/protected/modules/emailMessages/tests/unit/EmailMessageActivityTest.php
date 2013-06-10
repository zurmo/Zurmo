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
    class EmailMessageActivityTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetEmailMessageActivityById()
        {
            $emailMessageActivity                          = new EmailMessageActivity();
            $emailMessageActivity->type                    = EmailMessageActivity::TYPE_OPEN;
            $emailMessageActivity->quantity                = 10;
            $this->assertTrue($emailMessageActivity->save());
            $id = $emailMessageActivity->id;
            unset($emailMessageActivity);
            $emailMessageActivity = EmailMessageActivity::getById($id);
            $this->assertEquals(EmailMessageActivity::TYPE_OPEN     ,   $emailMessageActivity->type);
            $this->assertEquals(10                                  ,   $emailMessageActivity->quantity);
        }

        /**
         * @depends testCreateAndGetEmailMessageActivityById
         */
        public function testRequiredAttributes()
        {
            $emailMessageActivity                          = new EmailMessageActivity();
            $this->assertFalse($emailMessageActivity->save());
            $errors = $emailMessageActivity->getErrors();
            $this->assertNotEmpty($errors);
            $this->assertCount(2, $errors);
            $this->assertArrayHasKey('type', $errors);
            $this->assertEquals('Type cannot be blank.', $errors['type'][0]);
            $this->assertArrayHasKey('quantity', $errors);
            $this->assertEquals('Quantity cannot be blank.', $errors['quantity'][0]);

            $emailMessageActivity->type                    = EmailMessageActivity::TYPE_CLICK;
            $emailMessageActivity->quantity                = 5;
            $emailMessageUrl                               = new EmailMessageUrl();
            $emailMessageUrl->url                          = 'http://www.example.com';
            $emailMessageActivity->emailMessageUrl         = $emailMessageUrl;
            $this->assertTrue($emailMessageActivity->save());
            $id = $emailMessageActivity->id;
            unset($emailMessageActivity);
            $emailMessageActivity = EmailMessageActivity::getById($id);
            $this->assertEquals(EmailMessageActivity::TYPE_CLICK   ,   $emailMessageActivity->type);
            $this->assertEquals(5                                  ,   $emailMessageActivity->quantity);
            $this->assertEquals('http://www.example.com'           ,   $emailMessageActivity->emailMessageUrl->url);
        }

        /**
         * @depends testCreateAndGetEmailMessageActivityById
         */
        public function testGetByType()
        {
            $emailMessageActivities = EmailMessageActivity::getByType(EmailMessageActivity::TYPE_OPEN);
            $this->assertCount(1, $emailMessageActivities);
            $emailMessageActivities = EmailMessageActivity::getByType(EmailMessageActivity::TYPE_CLICK);
            $this->assertCount(1, $emailMessageActivities);
        }

        /**
         * @depends testCreateAndGetEmailMessageActivityById
         */
        public function testGetLabel()
        {
            $emailMessageActivity  = RandomDataUtil::getRandomValueFromArray(EmailMessageActivity::getAll());
            $this->assertNotNull($emailMessageActivity);
            $this->assertEquals('Email Message Activity',  $emailMessageActivity::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Email Message Activities', $emailMessageActivity::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testCreateAndGetEmailMessageActivityById
         */
        public function testDeleteEmailMessageActivity()
        {
            $emailMessageActivities = EmailMessageActivity::getAll();
            $this->assertCount(2, $emailMessageActivities);
            $emailMessageActivities[0]->delete();
            $emailMessageActivities = EmailMessageActivity::getAll();
            $this->assertEquals(1, count($emailMessageActivities));
        }

        /**
         * @depends testCreateAndGetEmailMessageActivityById
         */
        public function testEmailMessageActivityStringValue()
        {
            $emailMessageActivities = EmailMessageActivity::getAll();
            $this->assertCount(1, $emailMessageActivities);
            $types  = EmailMessageActivity::getTypesArray();
            $type   = $types[$emailMessageActivities[0]->type];
            $expectedStringValue = $emailMessageActivities[0]->latestDateTime . ': ' .
                                    strval($emailMessageActivities[0]->person) . '/' . $type;
            $this->assertEquals($expectedStringValue, strval($emailMessageActivities[0]));
        }
    }
?>