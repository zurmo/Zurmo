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

    class DashboardTest extends BaseTest
    {
        public function testGetNextLayoutId()
        {
            $this->assertEquals(2, Dashboard::getNextLayoutId());
            $user = UserTestHelper::createBasicUser('Billy');

            Yii::app()->user->userModel = User::getByUsername('billy');

            for ($i = 1; $i <= 3; $i++)
            {
                $dashboard = new Dashboard();
                $dashboard->name       = "Dashboard $i";
                $dashboard->layoutId   = $i;
                $dashboard->owner      = $user;
                $dashboard->layoutType = '100';
                $dashboard->isDefault  = false;
                $this->assertTrue($dashboard->save());
            }
            $this->assertEquals(4, Dashboard::getNextLayoutId());
        }

        /**
         * @depends testGetNextLayoutId
         */
        public function testGetByLayoutId()
        {
            $user = User::getByUserName('billy');
            Yii::app()->user->userModel = $user;
            for ($i = 1; $i <= 3; $i++)
            {
                $dashboard = Dashboard::getByLayoutId($i);
                $this->assertEquals($i,             $dashboard->layoutId);
                $this->assertEquals("Dashboard $i", $dashboard->name);
                $this->assertEquals($user->id,      $dashboard->owner->id);
                $this->assertEquals('100',          $dashboard->layoutType);
                $this->assertEquals(0,      $dashboard->isDefault);
            }
        }

        /**
         * @depends testGetByLayoutId
         * @expectedException NotFoundException
         */
        public function testGetByLayoutIdForNonexistentId()
        {
            $dashboard = Dashboard::getByLayoutId(123123);
        }

        /**
         * @depends testGetNextLayoutId
         */
        public function testGetByLayoutIdAndUserId()
        {
            $user = User::getByUserName('billy');
            Yii::app()->user->userModel = $user;
            for ($i = 1; $i <= 3; $i++)
            {
                $dashboard = Dashboard::getByLayoutIdAndUser($i, $user);
                $this->assertEquals($i,             $dashboard->layoutId);
                $this->assertEquals("Dashboard $i", $dashboard->name);
                $this->assertEquals('100',          $dashboard->layoutType);
            }
        }

        /**
         * testGetNextLayoutId
         */
        public function testGetRowsByUserId()
        {
            $user = User::getByUserName('billy');
            Yii::app()->user->userModel = $user;
            $rows = Dashboard::getRowsByUserId($user->id);
            $this->assertEquals(3, count($rows));
            for ($i = 1; $i <= 3; $i++)
            {
                $this->assertEquals("Dashboard $i", $rows[$i - 1]['name']);
                $this->assertEquals($i,             $rows[$i - 1]['layoutId']);
            }
        }

        /**
         * testGetRowsByUserId
         */
        public function testGetRowsByNonexistentUserId()
        {
            $rows = Dashboard::getRowsByUserId(123123);
            $this->assertEquals(0, count($rows));
        }

        /**
         * testGetRowsByUserId
         */
        public function testDeleteDashboardAndRelatedPortlets()
        {
            Yii::app()->user->userModel = User::getByUsername('billy');
            $dashboardCount = count(Dashboard::getAll());
            $this->assertTrue($dashboardCount > 0);
            $user = User::getByUserName('billy');
            Yii::app()->user->userModel = $user;
            $dashboard = new Dashboard();
            $dashboard->name       = "Dashboard TESTING";
            $dashboard->layoutId   = 3;
            $dashboard->owner      = $user;
            $dashboard->layoutType = '100';
            $dashboard->isDefault  = false;
            $this->assertTrue($dashboard->save());
            $this->assertEquals(count(Portlet::getAll()), 0);
            $this->assertEquals(count(Dashboard::getAll()), ($dashboardCount + 1));
            for ($i = 1; $i <= 3; $i++)
            {
                $portlet = new Portlet();
                $portlet->column    = 1;
                $portlet->position  = 1 + $i;
                $portlet->layoutId  = 'TEST' . $dashboard->layoutId;
                $portlet->collapsed = false;
                $portlet->viewType  = 'TasksMyList';
                $portlet->user      = $user;
                $this->assertTrue($portlet->save());
            }
            $this->assertEquals(count(Portlet::getAll()), 3);
            $portlets = Portlet::getByLayoutIdAndUserSortedById('TEST' . $dashboard->layoutId, $user->id);
            foreach ($portlets as $portlet)
            {
                $portlet->delete();
            }
            $dashboard->delete();
            $this->assertEquals(count(Portlet::getAll()), 0);
            $this->assertEquals(count(Dashboard::getAll()), ($dashboardCount));
        }

        /**
         * testGetNextLayoutId
         */

        public function testCreateDashboardFromPost()
        {
            $user = User::getByUserName('billy');
            Yii::app()->user->userModel = $user;
            $dashboard = new Dashboard();
            $dashboard->owner    = $user;
            $dashboard->layoutId = Dashboard::getNextLayoutId();
            $fakePost = array(
                'name'       => 'abc123',
                'layoutType' => '50,50', // Not Coding Standard
            );
            $dashboard->setAttributes($fakePost);
            $dashboard->validate();
            $this->assertEquals(array(), $dashboard->getErrors());
            $this->assertTrue($dashboard->save());
        }
    }
?>
