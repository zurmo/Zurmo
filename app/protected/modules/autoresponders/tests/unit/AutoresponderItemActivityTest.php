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
    class AutoresponderItemActivityTest extends ZurmoBaseTest
    {
        protected $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user                         = User::getByUsername('super');
            Yii::app()->user->userModel         =  $this->user;
        }

        public function testCreateAndGetAutoresponderItemActivityById()
        {
            $autoresponderItemActivity                  = new AutoresponderItemActivity();
            $autoresponderItemActivity->type            = AutoresponderItemActivity::TYPE_OPEN;
            $autoresponderItemActivity->quantity        = 10;
            $autoresponderItemActivity->latestSourceIP  = '10.11.12.13';
            $this->assertTrue($autoresponderItemActivity->save());
            $id = $autoresponderItemActivity->id;
            unset($autoresponderItemActivity);
            $autoresponderItemActivity              = AutoresponderItemActivity::getById($id);
            $this->assertEquals(AutoresponderItemActivity::TYPE_OPEN    ,   $autoresponderItemActivity->type);
            $this->assertEquals(10                                      ,   $autoresponderItemActivity->quantity);
            $this->assertEquals('10.11.12.13'                           ,   $autoresponderItemActivity->latestSourceIP);
        }

        public function testCreateAndGetAutoresponderItemActivityWithAutoresponderItemById()
        {
            $contact                                        = ContactTestHelper::createContactByNameForOwner(
                                                                                                    'contact 01',
                                                                                                    $this->user);
            $marketingList                                  = MarketingListTestHelper::createMarketingListByName(
                                                                                                    'marketingList 01');
            $autoresponder                                  = AutoresponderTestHelper::createAutoresponder(
                                                                                    'subject 01',
                                                                                    'text content',
                                                                                    'html content',
                                                                                    1,
                                                                                    Autoresponder::OPERATION_SUBSCRIBE,
                                                                                    true,
                                                                                    $marketingList);
            $processed                                      = 0;
            $processDateTime                                = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - 100);
            $autoresponderItem                              = AutoresponderItemTestHelper::createAutoresponderItem(
                                                                                                        $processed,
                                                                                                        $processDateTime,
                                                                                                        $autoresponder,
                                                                                                        $contact
                                                                                                    );
            $autoresponderItemActivity                      = new AutoresponderItemActivity();
            $autoresponderItemActivity->type                = AutoresponderItemActivity::TYPE_CLICK;
            $autoresponderItemActivity->quantity            = 1;
            $autoresponderItemActivity->latestSourceIP      = '11.12.13.14';
            $autoresponderItemActivity->autoresponderItem   = $autoresponderItem;
            $this->assertTrue($autoresponderItemActivity->save());
            $id = $autoresponderItemActivity->id;
            unset($autoresponderItemActivity);
            $autoresponderItemActivity = AutoresponderItemActivity::getById($id);
            $this->assertEquals(AutoresponderItemActivity::TYPE_CLICK,   $autoresponderItemActivity->type);
            $this->assertEquals(1                                    ,   $autoresponderItemActivity->quantity);
            $this->assertEquals('11.12.13.14'                        ,   $autoresponderItemActivity->latestSourceIP);
            $this->assertEquals($autoresponderItem                   ,   $autoresponderItemActivity->autoresponderItem);
        }

        /**
         * @depends testCreateAndGetAutoresponderItemActivityById
         */
        public function testRequiredAttributes()
        {
            $autoresponderItemActivity                          = new AutoresponderItemActivity();
            $this->assertFalse($autoresponderItemActivity->save());
            $errors = $autoresponderItemActivity->getErrors();
            $this->assertNotEmpty($errors);
            $this->assertCount(2, $errors);
            $this->assertArrayHasKey('type', $errors);
            $this->assertEquals('Type cannot be blank.', $errors['type'][0]);
            $this->assertArrayHasKey('quantity', $errors);
            $this->assertEquals('Quantity cannot be blank.', $errors['quantity'][0]);

            $autoresponderItemActivity->type                    = AutoresponderItemActivity::TYPE_CLICK;
            $autoresponderItemActivity->quantity                = 5;
            $emailMessageUrl                                    = new EmailMessageUrl();
            $emailMessageUrl->url                               = 'http://www.example.com';
            $autoresponderItemActivity->emailMessageUrl         = $emailMessageUrl;
            $autoresponderItemActivity->latestSourceIP          = '12.13.14.15';
            $this->assertTrue($autoresponderItemActivity->save());
            $id = $autoresponderItemActivity->id;
            unset($autoresponderItemActivity);
            $autoresponderItemActivity = AutoresponderItemActivity::getById($id);
            $this->assertEquals(AutoresponderItemActivity::TYPE_CLICK,   $autoresponderItemActivity->type);
            $this->assertEquals(5                                    ,   $autoresponderItemActivity->quantity);
            $this->assertEquals('12.13.14.15'                        ,   $autoresponderItemActivity->latestSourceIP);
            $this->assertEquals('http://www.example.com'             ,   $autoresponderItemActivity->emailMessageUrl->url);
        }

        /**
         * @depends testCreateAndGetAutoresponderItemActivityById
         */
        public function testGetByType()
        {
            $autoresponderItemActivities = AutoresponderItemActivity::getByType(AutoresponderItemActivity::TYPE_OPEN);
            $this->assertCount(1, $autoresponderItemActivities);
            $autoresponderItemActivities = AutoresponderItemActivity::getByType(AutoresponderItemActivity::TYPE_CLICK);
            $this->assertCount(2, $autoresponderItemActivities);
        }

        /**
         * @depends testCreateAndGetAutoresponderItemActivityById
         */
        public function testGetLabel()
        {
            $autoresponderItemActivity  = RandomDataUtil::getRandomValueFromArray(AutoresponderItemActivity::getAll());
            $this->assertNotNull($autoresponderItemActivity);
            $this->assertEquals('Autoresponder Item Activity',   $autoresponderItemActivity::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Autoresponder Item Activities', $autoresponderItemActivity::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testCreateAndGetAutoresponderItemActivityById
         */
        public function testDeleteAutoresponderItemActivity()
        {
            $autoresponderItemActivities = AutoresponderItemActivity::getAll();
            $this->assertCount(3, $autoresponderItemActivities);
            $autoresponderItemActivities[0]->delete();
            $autoresponderItemActivities = AutoresponderItemActivity::getAll();
            $this->assertEquals(2, count($autoresponderItemActivities));
        }

        /**
         * @depends testCreateAndGetAutoresponderItemActivityById
         */
        public function testAutoresponderItemActivityStringValue()
        {
            $autoresponderItemActivities = AutoresponderItemActivity::getAll();
            $this->assertCount(2, $autoresponderItemActivities);
            $types  = AutoresponderItemActivity::getTypesArray();
            $type   = $types[$autoresponderItemActivities[0]->type];
            $expectedStringValue = $autoresponderItemActivities[0]->latestDateTime . ': ' .
                                    strval($autoresponderItemActivities[0]->person) . '/' . $type;
            $this->assertEquals($expectedStringValue, strval($autoresponderItemActivities[0]));
        }

        /**
         * @depends testCreateAndGetAutoresponderItemActivityById
         */
        public function testCreateNewActivity()
        {
            $url                            = null;
            $sourceIP                       = '13.14.15.16';
            $type                           = AutoresponderItemActivity::TYPE_OPEN;
            $autoresponderItems             = AutoresponderItem::getAll();
            $this->assertNotEmpty($autoresponderItems);
            $autoresponderItem              = $autoresponderItems[0];
            $persons                        = Person::getAll();
            $this->assertNotEmpty($persons);
            $person                         = $persons[0];
            $saved                          = AutoresponderItemActivity::createNewActivity($type,
                                                                                            $autoresponderItem->id,
                                                                                            $person->id,
                                                                                            $url,
                                                                                            $sourceIP,
                                                                                            $autoresponderItem);
            $this->assertTrue($saved);

            // now try same thing but with a url this time.
            $contact                        = ContactTestHelper::createContactByNameForOwner('contact 02', $this->user);
            $personId                       = $contact->getClassId('Person');
            $type                           = AutoresponderItemActivity::TYPE_CLICK;
            $url                            = 'http://www.zurmo.com';
            $saved                          = AutoresponderItemActivity::createNewActivity($type,
                                                                                            $autoresponderItem->id,
                                                                                            $personId,
                                                                                            $url,
                                                                                            $sourceIP,
                                                                                            $autoresponderItem);
            $this->assertTrue($saved);

            // test that creating the one with url created one with open too:
            $activity                       = AutoresponderItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                                AutoresponderItemActivity::TYPE_OPEN,
                                                                                $autoresponderItem->id,
                                                                                $personId);
            $this->assertNotEmpty($activity);
            $this->assertCount(1, $activity);
        }

        /**
         * @depends testCreateNewActivity
         */
        public function testGetByTypeAndModelIdAndPersonIdAndUrl()
        {
            $type                                           = AutoresponderItemActivity::TYPE_OPEN;
            $url                                            = null;
            $persons                                        = Person::getAll();
            $this->assertNotEmpty($persons);
            $person                                         = $persons[0];
            $autoresponderItems                             = AutoresponderItem::getAll();
            $this->assertNotEmpty($autoresponderItems);
            $autoresponderItem                              = $autoresponderItems[0];

            $activities = AutoresponderItemActivity::getByTypeAndModelIdAndPersonIdAndUrl($type,
                                                                                            $autoresponderItem->id,
                                                                                            $person->id,
                                                                                            $url);
            $this->assertNotEmpty($activities);
            $this->assertCount(1,                           $activities);
            $activity                                       = $activities[0];
            $this->assertEquals($type,                      $activity->type);
            $this->assertEquals(1,                          $activity->quantity);
            $this->assertEquals($person,                    $activity->person);
            $this->assertEquals($autoresponderItem,         $activity->autoresponderItem);

            // now try same thing but with a url this time.
            $contact                                        = Contact::getByName('contact 02 contact 02son');
            $personId                                       = $contact[0]->getClassId('Person');
            $person                                         = Person::getById($personId);
            $type                                           = AutoresponderItemActivity::TYPE_CLICK;
            $url                                            = 'http://www.zurmo.com';
            $activities = AutoresponderItemActivity::getByTypeAndModelIdAndPersonIdAndUrl($type,
                                                                                            $autoresponderItem->id,
                                                                                            $personId,
                                                                                            $url);
            $this->assertNotEmpty($activities);
            $this->assertCount(1,                       $activities);
            $activity                                   = $activities[0];
            $this->assertEquals($type,                  $activity->type);
            $this->assertEquals(1,                      $activity->quantity);
            $this->assertEquals($person,                $activity->person);
            $this->assertEquals($autoresponderItem,     $activity->autoresponderItem);
        }
    }
?>