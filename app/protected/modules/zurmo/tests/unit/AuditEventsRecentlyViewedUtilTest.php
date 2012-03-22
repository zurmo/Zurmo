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

    class AuditEventsRecentlyViewedUtilTest extends BaseTest
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

        public function testFetRecentlyViewedAjaxContentByUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
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
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, strval($account1), $account1);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, strval($account2), $account2);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, strval($account1), $account1);

            //Switch users to add an audit event.
            Yii::app()->user->userModel = User::getByUsername('jimmy');
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, strval($account3), $account3);
            Yii::app()->user->userModel = User::getByUsername('super');

            $content = AuditEventsRecentlyViewedUtil::getRecentlyViewedAjaxContentByUser(Yii::app()->user->userModel, 5);
            $this->assertNotEmpty($content);
            $this->assertTrue(strpos($content, 'Dooble2') !== false);
            $this->assertTrue(strpos($content, 'Dooble1') !== false);
        }
    }
?>
