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
    class CampaignItemActivityTest extends ZurmoBaseTest
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

        public function testCreateAndGetCampaignItemActivityById()
        {
            $campaignItemActivity                   = new CampaignItemActivity();
            $campaignItemActivity->type             = CampaignItemActivity::TYPE_OPEN;
            $campaignItemActivity->quantity         = 10;
            $campaignItemActivity->latestSourceIP   = '111.222.112.122';
            $this->assertTrue($campaignItemActivity->save());
            $id = $campaignItemActivity->id;
            unset($campaignItemActivity);
            $campaignItemActivity              = CampaignItemActivity::getById($id);
            $this->assertEquals(CampaignItemActivity::TYPE_OPEN         ,   $campaignItemActivity->type);
            $this->assertEquals(10                                      ,   $campaignItemActivity->quantity);
            $this->assertEquals('111.222.112.122'                       ,   $campaignItemActivity->latestSourceIP);
        }

        public function testCreateAndGetCampaignItemActivityWithCampaignItemById()
        {
            $contact            = ContactTestHelper::createContactByNameForOwner('contact 01', $this->user);
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 01');
            $campaign           = CampaignTestHelper::createCampaign('campaign 01',
                                                                        'subject 01',
                                                                        'text Content 01',
                                                                        'html Content 01',
                                                                        'fromName 01',
                                                                        'fromAddress01@zurmo.com',
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        $marketingList);
            $processed          = 0;
            $campaignItem       = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $campaignItemActivity                           = new CampaignItemActivity();
            $campaignItemActivity->type                     = CampaignItemActivity::TYPE_CLICK;
            $campaignItemActivity->quantity                 = 1;
            $campaignItemActivity->campaignItem             = $campaignItem;
            $campaignItemActivity->latestSourceIP           = '121.212.122.112';
            $this->assertTrue($campaignItemActivity->save());
            $id = $campaignItemActivity->id;
            unset($campaignItemActivity);
            $campaignItemActivity = CampaignItemActivity::getById($id);
            $this->assertEquals(CampaignItemActivity::TYPE_CLICK,   $campaignItemActivity->type);
            $this->assertEquals(1                               ,   $campaignItemActivity->quantity);
            $this->assertEquals($campaignItem                   ,   $campaignItemActivity->campaignItem);
            $this->assertEquals('121.212.122.112'               ,   $campaignItemActivity->latestSourceIP);
        }

        /**
         * @depends testCreateAndGetCampaignItemActivityById
         */
        public function testRequiredAttributes()
        {
            $campaignItemActivity                                   = new CampaignItemActivity();
            $this->assertFalse($campaignItemActivity->save());
            $errors                                                 = $campaignItemActivity->getErrors();
            $this->assertNotEmpty($errors);
            $this->assertCount(2,                                   $errors);
            $this->assertArrayHasKey('type',                        $errors);
            $this->assertEquals('Type cannot be blank.',            $errors['type'][0]);
            $this->assertArrayHasKey('quantity',                    $errors);
            $this->assertEquals('Quantity cannot be blank.',        $errors['quantity'][0]);

            $campaignItemActivity->type                             = CampaignItemActivity::TYPE_CLICK;
            $campaignItemActivity->quantity                         = 5;
            $emailMessageUrl                                        = new EmailMessageUrl();
            $emailMessageUrl->url                                   = 'http://www.example.com';
            $campaignItemActivity->emailMessageUrl                  = $emailMessageUrl;
            $campaignItemActivity->latestSourceIP                   = '131.113.112.121';
            $this->assertTrue($campaignItemActivity->save());
            $id                                                     = $campaignItemActivity->id;
            unset($campaignItemActivity);
            $campaignItemActivity                                   = CampaignItemActivity::getById($id);
            $this->assertEquals(CampaignItemActivity::TYPE_CLICK,   $campaignItemActivity->type);
            $this->assertEquals(5                               ,   $campaignItemActivity->quantity);
            $this->assertEquals('http://www.example.com'        ,   $campaignItemActivity->emailMessageUrl->url);
            $this->assertEquals('131.113.112.121'               ,   $campaignItemActivity->latestSourceIP);
        }

        /**
         * @depends testCreateAndGetCampaignItemActivityById
         */
        public function testGetByType()
        {
            $campaignItemActivities     = CampaignItemActivity::getByType(CampaignItemActivity::TYPE_OPEN);
            $this->assertCount(1,       $campaignItemActivities);
            $campaignItemActivities     = CampaignItemActivity::getByType(CampaignItemActivity::TYPE_CLICK);
            $this->assertCount(2,       $campaignItemActivities);
        }

        /**
         * @depends testCreateAndGetCampaignItemActivityById
         */
        public function testGetLabel()
        {
            $campaignItemActivity  = RandomDataUtil::getRandomValueFromArray(CampaignItemActivity::getAll());
            $this->assertNotNull($campaignItemActivity);
            $this->assertEquals('Campaign Item Activity',   $campaignItemActivity::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Campaign Item Activities', $campaignItemActivity::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testCreateAndGetCampaignItemActivityById
         */
        public function testDeleteCampaignItemActivity()
        {
            $campaignItemActivities = CampaignItemActivity::getAll();
            $this->assertCount(3, $campaignItemActivities);
            $campaignItemActivities[0]->delete();
            $campaignItemActivities = CampaignItemActivity::getAll();
            $this->assertEquals(2, count($campaignItemActivities));
        }

        /**
         * @depends testCreateAndGetCampaignItemActivityById
         */
        public function testCampaignItemActivityStringValue()
        {
            $campaignItemActivities     = CampaignItemActivity::getAll();
            $this->assertCount(2, $campaignItemActivities);
            $types                      = CampaignItemActivity::getTypesArray();
            $type                       = $types[$campaignItemActivities[0]->type];
            $expectedStringValue        = $campaignItemActivities[0]->latestDateTime . ': ' .
                                                    strval($campaignItemActivities[0]->person) . '/' . $type;
            $this->assertEquals($expectedStringValue, strval($campaignItemActivities[0]));
        }

        /**
         * @depends testCreateAndGetCampaignItemActivityById
         */
        public function testCreateNewActivity()
        {
            $url                = null;
            $sourceIP           = '58.10.38.112';
            $type               = CampaignItemActivity::TYPE_OPEN;
            $campaignItems      = CampaignItem::getAll();
            $this->assertNotEmpty($campaignItems);
            $campaignItem       = $campaignItems[0];
            $persons            = Person::getAll();
            $this->assertNotEmpty($persons);
            $person             = $persons[0];
            $saved              = CampaignItemActivity::createNewActivity($type,
                                                                            $campaignItem->id,
                                                                            $person->id,
                                                                            $url,
                                                                            $sourceIP,
                                                                            $campaignItem);
            $this->assertTrue($saved);

            $contact            = ContactTestHelper::createContactByNameForOwner('contact 02', $this->user);
            $personId           = $contact->getClassId('Person');
            // now try same thing but with a url this time.
            $type               = CampaignItemActivity::TYPE_CLICK;
            $url                = 'http://www.zurmo.com';
            $saved              = CampaignItemActivity::createNewActivity($type,
                                                                            $campaignItem->id,
                                                                            $personId,
                                                                            $url,
                                                                            $sourceIP,
                                                                            $campaignItem);
            $this->assertTrue($saved);

            // test that creating the one with url created one with open too:
            $activity           = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                                        CampaignItemActivity::TYPE_OPEN,
                                                                                        $campaignItem->id,
                                                                                        $personId);
            $this->assertNotEmpty($activity);
            $this->assertCount(1, $activity);
        }

        /**
         * @depends testCreateNewActivity
         */
        public function testGetByTypeAndModelIdAndPersonIdAndUrl()
        {
            $type               = CampaignItemActivity::TYPE_OPEN;
            $url                = null;
            $persons            = Person::getAll();
            $this->assertNotEmpty($persons);
            $person             = $persons[0];
            $campaignItems      = CampaignItem::getAll();
            $this->assertNotEmpty($campaignItems);
            $campaignItem       = $campaignItems[0];

            $activities         = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl($type, $campaignItem->id,
                                                                                                            $person->id,
                                                                                                            $url);
            $this->assertNotEmpty($activities);
            $this->assertCount(1,                   $activities);
            $activity                               = $activities[0];
            $this->assertEquals($type,              $activity->type);
            $this->assertEquals(1,                  $activity->quantity);
            $this->assertEquals($person,            $activity->person);
            $this->assertEquals($campaignItem,      $activity->campaignItem);

            // now try same thing but with a url this time.
            $contact                                = Contact::getByName('contact 02 contact 02son');
            $personId                               = $contact[0]->getClassId('Person');
            $person                                 = Person::getById($personId);
            $type                                   = CampaignItemActivity::TYPE_CLICK;
            $url                                    = 'http://www.zurmo.com';
            $activities                             = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl($type,
                                                                                                    $campaignItem->id,
                                                                                                    $personId,
                                                                                                    $url);
            $this->assertNotEmpty($activities);
            $this->assertCount(1,                  $activities);
            $activity                              = $activities[0];
            $this->assertEquals($type,             $activity->type);
            $this->assertEquals(1,                 $activity->quantity);
            $this->assertEquals($person,           $activity->person);
            $this->assertEquals($campaignItem,     $activity->campaignItem);
        }
    }
?>