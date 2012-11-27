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

    class EmailAccountTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
        }

        public function testResolveAndGetByUserAndName()
        {
            //Test a user that not have a Primary Email Address
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $emailAccount = EmailAccount::resolveAndGetByUserAndName($super);
            $this->assertEquals('Default', $emailAccount->name);
            $this->assertEquals($super, $emailAccount->user);
            $this->assertEquals($super->getFullName(), $emailAccount->fromName);
            $this->assertEquals($super->primaryEmail->emailAddress, $emailAccount->fromAddress);
            $this->assertEquals(0, $emailAccount->useCustomOutboundSettings);
            $this->assertEquals('smtp', $emailAccount->outboundType);
            $emailAccountId = $emailAccount->id;
            $emailAccount = EmailAccount::resolveAndGetByUserAndName($super);
            $this->assertNotEquals($emailAccountId, $emailAccount->id);
            $emailAccount->save();
            $this->assertEquals($emailAccount->getError('fromAddress'), 'From Address cannot be blank.');
            $emailAccount->fromAddress = 'super@zurmo.org';
            $emailAccount->save();
            $emailAccountId = $emailAccount->id;
            $emailAccount = EmailAccount::resolveAndGetByUserAndName($super);
            $this->assertEquals($emailAccountId, $emailAccount->id);
            $this->assertEquals('Default', $emailAccount->name);
            $this->assertEquals($super, $emailAccount->user);
            $this->assertEquals($super->getFullName(), $emailAccount->fromName);
            $this->assertEquals('super@zurmo.org', $emailAccount->fromAddress);
            $this->assertEquals(0, $emailAccount->useCustomOutboundSettings);
            $this->assertEquals('smtp', $emailAccount->outboundType);
        }

        /**
         * @depends testResolveAndGetByUserAndName
         */
        public function testGetByUserAndName()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $emailAccount = EmailAccount::getByUserAndName($super);
            $this->assertEquals('Default', $emailAccount->name);
            $this->assertEquals($super, $emailAccount->user);
            $this->assertEquals($super->getFullName(), $emailAccount->fromName);
            $this->assertEquals('super@zurmo.org', $emailAccount->fromAddress);
            $this->assertEquals(0, $emailAccount->useCustomOutboundSettings);
            $this->assertEquals('smtp', $emailAccount->outboundType);
        }
    }
?>