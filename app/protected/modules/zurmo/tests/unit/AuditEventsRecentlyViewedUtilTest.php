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

    class AuditEventsRecentlyViewedUtilTest extends ZurmoBaseTest
    {
        public function setUp()
        {
            parent::setUp();
            AuditEvent::$isTableOptimized = false;
        }

        public function teardown()
        {
            AuditEvent::$isTableOptimized = false;
            parent::teardown();
        }

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $user = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $user;
            $user = new User();
            $user->username  = 'jimmy';
            $user->firstName = 'James';
            $user->lastName  = 'Boondog';
            assert($user->save()); // Not Coding Standard
            assert(AuditEvent::getCount() == 4); // Not Coding Standard
        }

        public function testResolveNewRecentlyViewedModel()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $this->assertNull(ZurmoConfigurationUtil::
                                    getForCurrentUserByModuleName('ZurmoModule', 'recentlyViewed'));
            $account1       = new Account();
            $account1->name = 'For test recently viewed';
            $this->assertTrue($account1->save());
            AuditEventsRecentlyViewedUtil::resolveNewRecentlyViewedModel('AccountsModule', $account1, 2);
            $this->assertEquals(serialize(array(array('AccountsModule', $account1->id, strval($account1)))),
                                ZurmoConfigurationUtil::
                                    getForCurrentUserByModuleName('ZurmoModule', 'recentlyViewed'));
            AuditEventsRecentlyViewedUtil::resolveNewRecentlyViewedModel('AccountsModule', $account1, 2);
            $this->assertEquals(serialize(array(array('AccountsModule', $account1->id, strval($account1)))),
                                ZurmoConfigurationUtil::
                                    getForCurrentUserByModuleName('ZurmoModule', 'recentlyViewed'));
            $account2       = new Account();
            $account2->name = 'For test recently viewed';
            $this->assertTrue($account2->save());
            AuditEventsRecentlyViewedUtil::resolveNewRecentlyViewedModel('AccountsModule', $account2, 2);
            $this->assertEquals(serialize(array(array('AccountsModule', $account2->id, strval($account2)),
                                                array('AccountsModule', $account1->id, strval($account1)))),
                                ZurmoConfigurationUtil::
                                    getForCurrentUserByModuleName('ZurmoModule', 'recentlyViewed'));
            AuditEventsRecentlyViewedUtil::resolveNewRecentlyViewedModel('AccountsModule', $account1, 2);
            $this->assertEquals(serialize(array(array('AccountsModule', $account1->id, strval($account1)),
                                                array('AccountsModule', $account2->id, strval($account2)))),
                                ZurmoConfigurationUtil::
                                    getForCurrentUserByModuleName('ZurmoModule', 'recentlyViewed'));
            $account3       = new Account();
            $account3->name = 'For test recently viewed';
            $this->assertTrue($account3->save());
            AuditEventsRecentlyViewedUtil::resolveNewRecentlyViewedModel('AccountsModule', $account3, 2);
            $this->assertEquals(serialize(array(array('AccountsModule', $account3->id, strval($account3)),
                                                array('AccountsModule', $account1->id, strval($account1)))),
                                ZurmoConfigurationUtil::
                                    getForCurrentUserByModuleName('ZurmoModule', 'recentlyViewed'));
        }

        public function testFetRecentlyViewedAjaxContentByUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            ZurmoConfigurationUtil::setForCurrentUserByModuleName('ZurmoModule', 'recentlyViewed', null);
            $account1 = new Account();
            $account1->name = 'Dooble1';
            $this->assertTrue($account1->save());

            $account2 = new Account();
            $account2->name = 'Dooble2';
            $this->assertTrue($account2->save());

            $account3 = new Account();
            $account3->name  = 'Dooble3';
            $account3->owner = User::getByUsername('jimmy');
            $this->assertTrue($account3->save());

            $content = AuditEventsRecentlyViewedUtil::getRecentlyViewedAjaxContentByUser(Yii::app()->user->userModel, 5);
            $this->assertEquals('There are no recently viewed items.', $content);

            //Now create some audit entries for the Item Viewed event.
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($account1), 'AccountsModule'), $account1);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($account2), 'AccountsModule'), $account2);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($account1), 'AccountsModule'), $account1);

            //Switch users to add an audit event.
            Yii::app()->user->userModel = User::getByUsername('jimmy');
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($account3), 'AccountsModule'), $account3);
            Yii::app()->user->userModel = User::getByUsername('super');

            $content = AuditEventsRecentlyViewedUtil::getRecentlyViewedAjaxContentByUser(Yii::app()->user->userModel, 5);
            $this->assertNotEmpty($content);
            $this->assertTrue(strpos($content, 'Dooble2') !== false);
            $this->assertTrue(strpos($content, 'Dooble1') !== false);
        }

        public function testDeleteModelFromRecentlyViewed()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            ZurmoConfigurationUtil::setForCurrentUserByModuleName('ZurmoModule', 'recentlyViewed', null);
            $account1 = new Account();
            $account1->name = 'Dooble1';
            $this->assertTrue($account1->save());

            $account2 = new Account();
            $account2->name = 'Dooble2';
            $this->assertTrue($account2->save());

            $account3 = new Account();
            $account3->name  = 'Dooble3';
            $account3->owner = User::getByUsername('jimmy');
            $this->assertTrue($account3->save());

            //Now create some audit entries for the Item Viewed event.
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($account1), 'AccountsModule'), $account1);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($account2), 'AccountsModule'), $account2);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($account1), 'AccountsModule'), $account3);
            $content = AuditEventsRecentlyViewedUtil::getRecentlyViewedAjaxContentByUser(Yii::app()->user->userModel, 5);
            $this->assertContains('Dooble1', $content);
            $this->assertContains('Dooble2', $content);
            $this->assertContains('Dooble3', $content);

            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_DELETED, strval($account1), $account1);
            $content = AuditEventsRecentlyViewedUtil::getRecentlyViewedAjaxContentByUser(Yii::app()->user->userModel, 5);
            $this->assertNotContains('Dooble1', $content);
            $this->assertContains('Dooble2', $content);
            $this->assertContains('Dooble3', $content);
        }
    }
?>
