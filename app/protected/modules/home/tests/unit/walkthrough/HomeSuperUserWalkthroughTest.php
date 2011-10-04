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

    /**
     * Home Module Walkthrough.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class HomeSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            ContactsModule::loadStartingData();
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            //Set the current user as the super user.
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('home/default');
            $this->runControllerWithNoExceptionsAndGetContent('home/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('home/default/createDashboard');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $superDashboard = Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            $this->setGetArray(array('id' => $superDashboard->id));
            $this->runControllerWithNoExceptionsAndGetContent('home/default/editDashboard');
            //Save dashboard.
            $superDashboard = Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            $this->assertEquals('Dashboard', $superDashboard->name);
            $this->setPostArray(array('Dashboard' => array('name' => '456765421')));
            $this->runControllerWithRedirectExceptionAndGetContent('home/default/editDashboard');
            $superDashboard = Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            $this->assertEquals('456765421', $superDashboard->name);
            //Test having a failed validation on the dashboard during save.
            $this->setGetArray (array('id'      => $superDashboard->id));
            $this->setPostArray(array('Dashboard' => array('name' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('home/default/editDashboard');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);

            //Load Model Detail Views
            $this->setGetArray(array('id' => -1));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/default/dashboardDetails');
            $this->setGetArray(array('id' => $superDashboard->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/default/dashboardDetails');

            //Add second dashboard for use in deletion tests.
            $secondDashboard   = DashboardTestHelper::createDashboardByNameForOwner('Dashboard2', $super);
            $this->assertTrue($secondDashboard->isDefault == 0);
            $this->assertFalse($secondDashboard->isDefault === 0); //Just to prove it does not evaluate to this.
            //Attempt to delete the default dashboard and have it through an exception.
            $dashboards = Dashboard::getRowsByUserId($super->id);
            $this->assertEquals(2, count($dashboards));
            $this->setGetArray(array('dashboardId' => $superDashboard->id));
            $this->resetPostArray();
            $this->runControllerWithNotSupportedExceptionAndGetContent('home/default/deleteDashboard');

            //Delete dashboard that you can delete.
            $dashboards = Dashboard::getRowsByUserId($super->id);
            $this->assertEquals(2, count($dashboards));
            $this->setGetArray(array('dashboardId' => $secondDashboard->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('home/default/deleteDashboard');
            $dashboards = Dashboard::getRowsByUserId($super->id);
            $this->assertEquals(1, count($dashboards));

            //Add a dashboard via the create dashboard action.
            $this->assertEquals(1, count(Dashboard::getAll()));
            $this->resetGetArray();
            $this->setPostArray(array('Dashboard' => array(
                'name'    => 'myTestDashboard',
                'layoutType' => '50,50'))); // Not Coding Standard
            $this->runControllerWithRedirectExceptionAndGetContent('home/default/createDashboard');
            $dashboards = Dashboard::getAll();
            $this->assertEquals(2, count($dashboards));
            $this->assertEquals('myTestDashboard', $dashboards[1]->name);
            $this->assertEquals($super, $dashboards[1]->owner);
            $this->assertEquals('50,50', $dashboards[1]->layoutType); // Not Coding Standard

            //Portlet Controller Actions
            $uniqueLayoutId = 'HomeDashboard' . $superDashboard->layoutId;
            $this->setGetArray(array(
                'dashboardId'    => $superDashboard->id,
                'uniqueLayoutId' => $uniqueLayoutId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/addList');

            //Add AccountsMyList Portlet to dashboard
            $this->setGetArray(array(
                'dashboardId'    => $superDashboard->id,
                'portletType'    => 'AccountsMyList',
                'uniqueLayoutId' => $uniqueLayoutId));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('home/defaultPortlet/add');

            //Save a layout change. Collapse all portlets
            //At this point portlets for this view should be created because we have already loaded the 'details' page in a request above.
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition($uniqueLayoutId, $super->id, array());
            $this->assertEquals(6, count($portlets[1]));
            $this->assertEquals(3, count($portlets[2]));
            $portletPostData = array();
            $portletCount = 0;
            foreach ($portlets as $column => $columnPortlets)
            {
                foreach ($columnPortlets as $position => $portlet)
                {
                    $this->assertEquals('0', $portlet->collapsed);
                    $portletPostData['HomeDashboard1_' . $portlet->id] = array(
                        'collapsed' => 'true',
                        'column'    => 0,
                        'id'        => 'HomeDashboard1_' . $portlet->id,
                        'position'  => $portletCount,
                    );
                    $portletCount++;
                }
            }
            //There should have been a total of 3 portlets. Checking positions as 4 will confirm this.
            $this->assertEquals(9, $portletCount);
            $this->resetGetArray();
            $this->setPostArray(array(
                'portletLayoutConfiguration' => array(
                    'portlets' => $portletPostData,
                    'uniqueLayoutId' => $uniqueLayoutId,
                )
            ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/saveLayout', true);
            //Now test that all the portlets are collapsed.
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                            $uniqueLayoutId, $super->id, array());
            $this->assertEquals (9, count($portlets[1])         );
            $this->assertFalse  (array_key_exists(8, $portlets) );
            foreach ($portlets as $column => $columns)
            {
                foreach ($columns as $position => $positionPortlets)
                {
                    $this->assertEquals('1', $positionPortlets->collapsed);
                }
            }

            //Load up modal config edit view.
            $this->assertTrue($portlets[1][1]->id > 0);
            $this->assertEquals('AccountsMyList', $portlets[1][1]->viewType);
            $this->setGetArray(array(
                'portletId'    => $portlets[1][1]->id,
                'uniqueLayoutId' => $uniqueLayoutId,
            ));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/modalConfigEdit');

            //Now validate the form.
            $this->setGetArray(array(
                'portletId'    => $portlets[1][1]->id,
                'uniqueLayoutId' => $uniqueLayoutId,
            ));
            $this->setPostArray(array(
                'ajax'    => 'modal-edit-form',
                'AccountsSearchForm' => array()));
            $this->runControllerWithExitExceptionAndGetContent('home/defaultPortlet/modalConfigEdit');

            //save changes to the portlet title
            $this->setGetArray(array(
                'portletId'      => $portlets[1][1]->id,
                'uniqueLayoutId' => $uniqueLayoutId,
            ));
            $this->setPostArray(array(
                'MyListForm'         => array('title' => 'something new'),
                'AccountsSearchForm' => array()));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/modalConfigSave');
            //Now confirm the title change was successful.
            $portlet = Portlet::getById($portlets[1][1]->id);
            $this->assertEquals('something new', $portlet->getView()->getTitle());

            //Refresh a portlet modally.
            $this->setGetArray(array(
                'portletId'    => $portlets[1][1]->id,
                'uniqueLayoutId' => $uniqueLayoutId,
                'redirectUrl' => 'home/default'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/modalRefresh');
            //Load Home Dashboard View again to make sure everything is ok after the layout change.
            $this->resetGetArray();
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('home/default');
        }
    }
?>