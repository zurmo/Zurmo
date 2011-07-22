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

    class OpportunitiesChartWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
            public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner        ('superAccount',  $super);
            AccountTestHelper::createAccountByNameForOwner                   ('superAccount2', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner        ('superContact',  $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner        ('superContact2', $super, $account);
            OpportunityTestHelper::createOpportunityStagesIfDoesNotExist     ();
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp',      $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp2',     $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp3',     $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp4',     $super, $account);
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser                                  (Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
        }

        public function testCharts()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test OpportunitiesByStage
            $portlet = new Portlet();
            $portlet->column    = 1;
            $portlet->position  = 1;
            $portlet->layoutId  = 'TestLayout';
            $portlet->collapsed = false;
            $portlet->viewType  = 'OpportunitiesByStageChart';
            $portlet->user      = $super;
            $this->assertTrue($portlet->save());
            $this->setGetArray(array('chartLibraryName' => 'Fusion', 'portletId' => $portlet->id));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/makeChartXML');
            $this->assertFalse(strpos($content, '<graph') === false);
            $this->assertEquals(0, strpos($content, '<graph'));

            //Test OpportunitiesBySource
            $portlet = new Portlet();
            $portlet->column    = 1;
            $portlet->position  = 2;
            $portlet->layoutId  = 'TestLayout';
            $portlet->collapsed = false;
            $portlet->viewType  = 'OpportunitiesByStageChart';
            $portlet->user      = $super;
            $this->assertTrue($portlet->save());
            $this->setGetArray(array('chartLibraryName' => 'Fusion', 'portletId' => $portlet->id));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/makeChartXML');
            $this->assertFalse(strpos($content, '<graph') === false);
            $this->assertEquals(0, strpos($content, '<graph'));
        }
    }
?>

