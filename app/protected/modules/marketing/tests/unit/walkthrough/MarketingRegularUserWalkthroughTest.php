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
     * Marketing Module Regular User Walkthrough.
     */
    class MarketingRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $billy = User::getByUsername('billy');
            $billy->setRight('MarketingModule', MarketingModule::RIGHT_ACCESS_MARKETING);
            assert($billy->save()); // Not Coding Standard
        }

        public function testAllDefaultControllerActionsForBilly()
        {
            $billy      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('billy');
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketing/default');
            $this->assertContains('MarketingOverallMetricsView', $content);
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketing/default/index');
            $this->assertContains('MarketingOverallMetricsView', $content);
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketing/default/dashboardDetails');
            $this->assertContains('MarketingOverallMetricsView', $content);
        }

        public function testDashboardGroupByActionsForBilly()
        {
            $billy      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('billy');
            $portets    = Portlet::getAll();
            $this->assertCount(1, $portets);
            $this->setGetArray(array(
                        'portletId'         => $portets[0]->id,
                        'uniqueLayoutId'    => 'MarketingDashboard',
                    ));
            $this->setPostArray(array(
                        'MarketingOverallMetricsForm' => array('groupBy' => MarketingOverallMetricsForm::GROUPING_TYPE_DAY)
                    ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/modalConfigSave');
            $this->setPostArray(array(
                        'MarketingOverallMetricsForm' => array('groupBy' => MarketingOverallMetricsForm::GROUPING_TYPE_MONTH)
                    ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/modalConfigSave');
            $this->setPostArray(array(
                        'MarketingOverallMetricsForm' => array('groupBy' => MarketingOverallMetricsForm::GROUPING_TYPE_WEEK)
                    ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/modalConfigSave');
        }

        public function testAllDefaultControllerActionsForNobody()
        {
            $billy      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            $content    = $this->runControllerWithExitExceptionAndGetContent('marketing/default');
            $this->assertNotContains('MarketingOverallMetricsView', $content);
            $content    = $this->runControllerWithExitExceptionAndGetContent('marketing/default/index');
            $this->assertNotContains('MarketingOverallMetricsView', $content);
            $content    = $this->runControllerWithExitExceptionAndGetContent('marketing/default/dashboardDetails');
            $this->assertNotContains('MarketingOverallMetricsView', $content);
        }
    }
?>

