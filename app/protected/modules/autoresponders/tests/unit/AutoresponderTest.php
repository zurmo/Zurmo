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
    class AutoresponderTest extends ZurmoBaseTest
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

        public function testCreateAndGetAutoresponderById()
        {
            $autoresponder                          = new Autoresponder();
            $autoresponder->subject                 = 'Test Autoresponder subject 01';
            $autoresponder->htmlContent             = 'Test HtmlContent 01';
            $autoresponder->textContent             = 'Test TextContent 01';
            $autoresponder->fromOperationDurationInterval = '1';
            $autoresponder->fromOperationDurationType     = TimeDurationUtil::DURATION_TYPE_WEEK;
            $autoresponder->operationType           = Autoresponder::OPERATION_SUBSCRIBE;
            $this->assertTrue($autoresponder->save());
            $id = $autoresponder->id;
            unset($autoresponder);
            $autoresponder = Autoresponder::getById($id);
            $this->assertEquals('Test Autoresponder subject 01'      ,   $autoresponder->subject);
            $this->assertEquals('Test HtmlContent 01'                ,   $autoresponder->htmlContent);
            $this->assertEquals('Test TextContent 01'                ,   $autoresponder->textContent);
            $this->assertEquals('1'                                  ,   $autoresponder->fromOperationDurationInterval);
            $this->assertEquals(TimeDurationUtil::DURATION_TYPE_WEEK ,   $autoresponder->fromOperationDurationType);
            $this->assertEquals(Autoresponder::OPERATION_SUBSCRIBE   ,   $autoresponder->operationType);
            $this->assertEquals(0    ,   $autoresponder->enableTracking);
        }

        /**
         * @depends testCreateAndGetAutoresponderById
         */
        public function testRequiredAttributes()
        {
            $autoresponder                          = new Autoresponder();
            $this->assertFalse($autoresponder->save());
            $errors = $autoresponder->getErrors();
            $this->assertNotEmpty($errors);
            $this->assertCount(5, $errors);
            $this->assertArrayHasKey('subject', $errors);
            $this->assertEquals('Subject cannot be blank.', $errors['subject'][0]);
            $this->assertArrayHasKey('textContent', $errors);
            $this->assertEquals('Please provide at least one of the contents field.', $errors['textContent'][0]);
            $this->assertArrayHasKey('fromOperationDurationInterval', $errors);
            $this->assertEquals('Send After cannot be blank.', $errors['fromOperationDurationInterval'][0]);
            $this->assertArrayHasKey('fromOperationDurationType', $errors);
            $this->assertEquals('From Operation Duration Type cannot be blank.', $errors['fromOperationDurationType'][0]);
            $this->assertArrayHasKey('operationType', $errors);
            $this->assertEquals('Triggered By cannot be blank.', $errors['operationType'][0]);

            $autoresponder->subject                       = 'Test Autoresponder subject 02';
            $autoresponder->htmlContent                   = 'Test HtmlContent 02';
            $autoresponder->textContent                   = 'Test TextContent 02';
            $autoresponder->fromOperationDurationInterval = '1';
            $autoresponder->fromOperationDurationType     = TimeDurationUtil::DURATION_TYPE_MONTH;
            $autoresponder->operationType                 = Autoresponder::OPERATION_UNSUBSCRIBE;
            $autoresponder->enableTracking                = 1;
            $this->assertTrue($autoresponder->save());
            $id = $autoresponder->id;
            unset($autoresponder);
            $autoresponder = Autoresponder::getById($id);
            $this->assertEquals('Test Autoresponder subject 02'        ,   $autoresponder->subject);
            $this->assertEquals('Test HtmlContent 02'                  ,   $autoresponder->htmlContent);
            $this->assertEquals('Test TextContent 02'                  ,   $autoresponder->textContent);
            $this->assertEquals('1'                                    ,   $autoresponder->fromOperationDurationInterval);
            $this->assertEquals(TimeDurationUtil::DURATION_TYPE_MONTH  ,   $autoresponder->fromOperationDurationType);
            $this->assertEquals(Autoresponder::OPERATION_UNSUBSCRIBE   ,   $autoresponder->operationType);
            $this->assertEquals(1                                      ,   $autoresponder->enableTracking);
        }

        /**
         * @depends testCreateAndGetAutoresponderById
         */
        public function testGetByOperationType()
        {
            $autoresponders = Autoresponder::getByOperationType(Autoresponder::OPERATION_SUBSCRIBE);
            $this->assertCount(1, $autoresponders);
            $autoresponders = Autoresponder::getByOperationType(Autoresponder::OPERATION_UNSUBSCRIBE);
            $this->assertCount(1, $autoresponders);
        }

        /**
         * @depends testCreateAndGetAutoresponderById
         */
        public function testGetByOperationTypeAndMarketingListId()
        {
            $marketingList = MarketingListTestHelper::createMarketingListByName('MarketingList Name 01');
            AutoresponderTestHelper::createAutoresponder('subject 01', 'text 01', null, 10,
                                                            Autoresponder::OPERATION_SUBSCRIBE, true, $marketingList);
            AutoresponderTestHelper::createAutoresponder('subject 02', 'text 02', null, 20,
                                                            Autoresponder::OPERATION_SUBSCRIBE, false, $marketingList);
            AutoresponderTestHelper::createAutoresponder('subject 03', 'text 03', null, 30,
                                                            Autoresponder::OPERATION_UNSUBSCRIBE, true, $marketingList);
            $autoresponders = Autoresponder::getByOperationTypeAndMarketingListId(Autoresponder::OPERATION_SUBSCRIBE,
                                                                                                    $marketingList->id);
            $this->assertCount(2, $autoresponders);
            $autoresponders = Autoresponder::getByOperationTypeAndMarketingListId(Autoresponder::OPERATION_UNSUBSCRIBE,
                                                                                                    $marketingList->id);
            $this->assertCount(1, $autoresponders);
        }

        /**
         * @depends testCreateAndGetAutoresponderById
         */
        public function testGetAutoresponderByName()
        {
            $autoresponder = Autoresponder::getByName('Test Autoresponder subject 01');
            $this->assertEquals(1, count($autoresponder));
            $this->assertEquals('Test Autoresponder subject 01', $autoresponder[0]->subject);
        }

        /**
         * @depends testCreateAndGetAutoresponderById
         */
        public function testGetLabel()
        {
            $autoresponder  = RandomDataUtil::getRandomValueFromArray(Autoresponder::getAll());
            $this->assertNotNull($autoresponder);
            $this->assertEquals('Autoresponder',  $autoresponder::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Autoresponders', $autoresponder::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testCreateAndGetAutoresponderById
         */
        public function testDeleteAutoresponder()
        {
            $autoresponders = Autoresponder::getAll();
            $this->assertCount(5, $autoresponders);
            $autoresponders[0]->delete();
            $autoresponders = Autoresponder::getAll();
            $this->assertEquals(4, count($autoresponders));
        }

        public function testResolveNewTimeStampForDuration()
        {
            $autoresponder = AutoresponderTestHelper::createAutoresponder(
                    'test autoresponder for resolve time stamp',
                    'sample text content',
                    'sample html content',
                    5,
                    Autoresponder::OPERATION_UNSUBSCRIBE);
            $this->assertEquals(5 * 24 * 60 * 60, $autoresponder->resolveNewTimeStampForDuration(0));
            $autoresponder->fromOperationDurationType = TimeDurationUtil::DURATION_TYPE_MINUTE;
            $this->assertEquals(5 * 60, $autoresponder->resolveNewTimeStampForDuration(0));
            $autoresponder->fromOperationDurationInterval = 10;
            $this->assertEquals(10 * 60, $autoresponder->resolveNewTimeStampForDuration(0));
            $autoresponder->fromOperationDurationType = TimeDurationUtil::DURATION_TYPE_HOUR;
            $this->assertEquals(10 * 60 * 60, $autoresponder->resolveNewTimeStampForDuration(0));
        }
    }
?>