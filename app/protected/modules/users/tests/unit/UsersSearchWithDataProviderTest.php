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

    /**
     * Tests on users with data provider
     */
    class UsersSearchWithDataProviderTest extends ZurmoBaseTest
    {
        private $user1;

        private $user2;

        private $user3;

        private $super;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super                              = User::getByUsername('super');
            Yii::app()->user->userModel         = $super;

            $user1                              = new User();
            $user1->username                    = 'user1';
            $user1->firstName                   = 'abel';
            $user1->lastName                    = 'zitabina';
            $user1->setPassword('myuser');
            $user1->save();

            $user2                              = new User();
            $user2->username                    = 'user2';
            $user2->firstName                   = 'zitabina';
            $user2->lastName                    = 'abel';
            $user2->setPassword('myuser');
            $user2->save();

            $user3                              = new User();
            $user3->username                    = 'user3';
            $user3->firstName                   = 'abel';
            $user3->lastName                    = 'abel';
            $user3->setPassword('myuser');
            $user3->save();
        }

        public function setUp()
        {
            parent::setUp();
            $this->super                        = User::getByUsername('super');
            Yii::app()->user->userModel         = $this->super;
            $this->user1                        = User::getByUsername('user1');
            $this->user2                        = User::getByUsername('user2');
            $this->user3                        = User::getByUsername('user3');
        }

        public function testDefaultFullnameOrderOnUsers()
        {
            $searchAttributeData        = array();
            $dataProvider               = new RedBeanModelDataProvider('User', null, false, $searchAttributeData);
            $data                       = $dataProvider->getData();
            $this->assertTrue($this->user3->id == $data[0]->id || $this->user3->id == $data[1]->id);
            $this->assertTrue($this->user2->id == $data[1]->id || $this->user2->id == $data[0]->id);
            $this->assertEquals($this->super, $data[2]);
            $this->assertEquals($this->user1, $data[3]);
        }

        /**
         * @depends testDefaultFullnameOrderOnUsers
         */
        public function testFirstNameOrderOnUsers()
        {
            $searchAttributeData                = array();
            $dataProvider                       = new RedBeanModelDataProvider('User', 'firstName', false, $searchAttributeData);
            $data                               = $dataProvider->getData();
            $this->assertEquals($this->user3, $data[0]);
            $this->assertEquals($this->user1, $data[1]);
            $this->assertEquals($this->super, $data[2]);
            $this->assertEquals($this->user2, $data[3]);
        }

        /**
         * @depends testDefaultFullnameOrderOnUsers
         */
        public function testLastNameOrderOnUsers()
        {
            $searchAttributeData                = array();
            $dataProvider                       = new RedBeanModelDataProvider('User', 'lastName', false, $searchAttributeData);
            $data                               = $dataProvider->getData();
            $this->assertTrue($this->user3->id == $data[0]->id || $this->user3->id == $data[1]->id);
            $this->assertTrue($this->user2->id == $data[1]->id || $this->user2->id == $data[0]->id);
            $this->assertEquals($this->super, $data[2]);
            $this->assertEquals($this->user1, $data[3]);
        }
    }
?>