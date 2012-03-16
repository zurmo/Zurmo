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

    /**
     * Testing the views for configuring outbound email
     */
    class EmailConfigurationSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $user = new User();
            $user->username           = 'super2';
            $user->title->value       = 'Mr.';
            $user->firstName          = 'Clark2';
            $user->lastName           = 'Kent2';
            $user->setPassword('super2');
            $saved = $user->save();
            assert($saved); // Not Coding Standard

            $group = Group::getByName('Super Administrators');
            $group->users->add($user);
            $saved = $group->save();
            assert($saved); // Not Coding Standard
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/configurationEdit');
        }

        public function testSuperUserModifyOutboundEmailConfiguration()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $super2 = User::getByUsername('super2');

            $this->assertEquals('smtp', Yii::app()->emailHelper->outboundType);
            $this->assertNull(Yii::app()->emailHelper->outboundHost);
            $this->assertEquals(25, Yii::app()->emailHelper->outboundPort);
            $this->assertNull(Yii::app()->emailHelper->outboundUsername);
            $this->assertNull(Yii::app()->emailHelper->outboundPassword);
            $this->assertEquals($super->id, Yii::app()->emailHelper->getUserToSendNotificationsAs()->id);

            //Change email settings
            $this->resetGetArray();
            $this->setPostArray(array('OutboundEmailConfigurationForm' => array(
                                    'host'                              => 'abc',
                                    'port'                              => '565',
                                    'username'                          => 'myuser',
                                    'password'                          => 'apassword',
                                    'userIdOfUserToSendNotificationsAs' => $super2->id)));
            $this->runControllerWithRedirectExceptionAndGetContent('emailMessages/default/configurationEdit');
            $this->assertEquals('Outbound email configuration saved successfully.', Yii::app()->user->getFlash('notification'));

            //Confirm the setting did in fact change correctly
            $emailHelper = new EmailHelper;
            $this->assertEquals('smtp',     Yii::app()->emailHelper->outboundType);
            $this->assertEquals('abc',      Yii::app()->emailHelper->outboundHost);
            $this->assertEquals('565',      Yii::app()->emailHelper->outboundPort);
            $this->assertEquals('myuser',   Yii::app()->emailHelper->outboundUsername);
            $this->assertEquals('apassword', Yii::app()->emailHelper->outboundPassword);
            $this->assertEquals($super2->id,    Yii::app()->emailHelper->getUserToSendNotificationsAs()->id);
        }
    }
?>