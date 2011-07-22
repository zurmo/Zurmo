<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    class ItemTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreationAndModificationTimes()
        {
            $user = UserTestHelper::createBasicUser('Billy');

            $account = new Account();
            $createdTime = time();
            $this->assertWithinTolerance($createdTime, DateTimeUtil::convertDbFormatDateTimeToTimestamp($account->createdDateTime),  2);
            $this->assertWithinTolerance($createdTime, DateTimeUtil::convertDbFormatDateTimeToTimestamp($account->modifiedDateTime), 2);
            sleep(3); // Sleeps are bad in tests, but I need some time to pass
                      // to test these time stamps. The 3's here just need to be more
                      // than the 2's in the asserts. Using 2 and 1 meant it failed
                      // occasionally because the second would tick over just at the
                      // wrong time.
            $account->owner = $user;
            $account->name = 'Test Account';
            $this->assertWithinTolerance($createdTime, DateTimeUtil::convertDbFormatDateTimeToTimestamp($account->createdDateTime),  2);
            $this->assertWithinTolerance(time(),       DateTimeUtil::convertDbFormatDateTimeToTimestamp($account->modifiedDateTime), 2);
            sleep(3);
            $account->officePhone = '1234567890';
            $lastModifiedTime = time();
            $this->assertWithinTolerance($createdTime,      DateTimeUtil::convertDbFormatDateTimeToTimestamp($account->createdDateTime),  2);
            $this->assertWithinTolerance($lastModifiedTime, DateTimeUtil::convertDbFormatDateTimeToTimestamp($account->modifiedDateTime), 2);
            $this->assertTrue($account->save());
            $this->assertWithinTolerance($createdTime,      DateTimeUtil::convertDbFormatDateTimeToTimestamp($account->createdDateTime),  2);
            $this->assertWithinTolerance($lastModifiedTime, DateTimeUtil::convertDbFormatDateTimeToTimestamp($account->modifiedDateTime), 2);
            $id = $account->id;
            unset($account);
            sleep(2);
            $account = Account::getById($id);
            $this->assertEquals('Test Account', $account->name);
            $this->assertEquals('1234567890',   $account->officePhone);
            $this->assertWithinTolerance($createdTime,      DateTimeUtil::convertDbFormatDateTimeToTimestamp($account->createdDateTime),  2);
            $this->assertWithinTolerance($lastModifiedTime, DateTimeUtil::convertDbFormatDateTimeToTimestamp($account->modifiedDateTime), 2);
        }

        /**
         * @depends testCreationAndModificationTimes
         */
        public function testExtraItemsCreatedOnModelInstantiation()
        {
            $countBefore = intval(R::getCell("select count(*) from item;"));
            $account = new Account();
            $countAfter  = intval(R::getCell("select count(*) from item;"));
            $this->assertEquals($countBefore, $countAfter);
        }

        /**
         * @depends testCreationAndModificationTimes
         * @expectedException NotSupportedException
         */
         public function testItemReadOnlyFieldsCreatedTime()
         {
            $account = new Account();
            $createdTime = time();
            $this->assertTrue($account->isAttributeReadOnly('createdDateTime'));
            $account->createdDateTime = time() + 123123;
         }

        /**
         * @depends testCreationAndModificationTimes
         * @expectedException NotSupportedException
         */
         public function testItemReadOnlyFieldsCreatedUser()
         {
            $user = User::getByUsername('billy');
            $account = new Account();
            $this->assertTrue($account->isAttributeReadOnly('createdByUser'));
            $account->createdByUser = $user;
         }

        /**
         * @depends testCreationAndModificationTimes
         * @expectedException NotSupportedException
         */
         public function testItemReadOnlyFieldsModifiedTime()
         {
            $account = new Account();
            $createdTime = time();
            $this->assertTrue($account->isAttributeReadOnly('modifiedDateTime'));
            $account->modifiedDateTime = time() + 123123;
         }

        /**
         * @depends testCreationAndModificationTimes
         * @expectedException NotSupportedException
         */
         public function testItemReadOnlyFieldsModifiedUser()
         {
            $user = User::getByUsername('billy');
            $account = new Account();
            $account->modifiedByUser = $user;
         }

        /**
         * @depends testItemReadOnlyFieldsModifiedUser
         */
         public function testCreatedByAndModifiedByUsersPopulateCorrectly()
         {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $user = User::getByUsername('billy');
            $account = new Account();
            $account->name  = 'aNewDawn Inc';
            $account->owner = $user;
            $this->assertTrue($account->save());
            $account = Account::getById($account->id);
            $this->assertEquals($super, $account->createdByUser);
            $this->assertEquals($super, $account->modifiedByUser);
            Yii::app()->user->userModel = $user;
            $account->name = 'aSecondDawn Inc.';
            $this->assertTrue($account->save());
            $account = Account::getById($account->id);
            $this->assertEquals($super, $account->createdByUser);
            $this->assertEquals($user, $account->modifiedByUser);
         }
    }
?>
