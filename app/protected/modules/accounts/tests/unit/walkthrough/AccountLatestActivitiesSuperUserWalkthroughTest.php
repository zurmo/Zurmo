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
     * Account Latest Activities Super User Walkthrough.
     * Walkthrough for the super user of all possible latest activity controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class AccountLatestActivitiesSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            //Set the current user as the super user.
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $accounts = Account::getAll();
            $this->assertEquals(1, count($accounts));
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');

            //Load Details view to generate the portlets.
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //Find the LatestActivity portlet.
            $portletToUse = null;
            $portlets     = Portlet::getAll();
            foreach($portlets as $portlet)
            {
                if($portlet->viewType == 'AccountNoteInlineEditAndLatestActivtiesForPortlet')
                {
                    $portletToUse = $portlet;
                    break;
                }
            }
            $this->assertNotNull($portletToUse);
            $this->assertEquals('AccountNoteInlineEditAndLatestActivtiesForPortletView', get_class($portletToUse->getView()));

            //Load the portlet details for latest activity for viewType LatestActivitiesView::VIEW_TYPE_LISTVIEW
            $getData = array('id' => $superAccountId,
                             'portletId' => 2,
                             'uniqueLayoutId' => 'AccountDetailsAndRelationsView_2',
                             'LatestActivitiesConfigurationForm' => array(
                                'filteredByModelName' => 'all',
                                'rollup' => false,
                                'viewType' => LatestActivitiesView::VIEW_TYPE_LISTVIEW,
                             ));
            $this->setGetArray($getData);
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');

            //Now add roll up
            $getData['LatestActivitiesConfigurationForm']['rollup'] = true;
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
            //Now filter by meeting, task, and note
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'Meeting';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'Note';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'Task';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
            //Now do the same thing with filtering but turn off rollup.
            $getData['LatestActivitiesConfigurationForm']['rollup'] = true;
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'Meeting';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'Note';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'Task';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');

            //Repeat everything above but do the summary view.
            //Load rollup off with SUMMARY view.
            $getData['LatestActivitiesConfigurationForm']['rollup'] = false;
            $getData['LatestActivitiesConfigurationForm']['viewType'] = LatestActivitiesView::VIEW_TYPE_SUMMARY;
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'all';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
            //Turn rollup on
            $getData['LatestActivitiesConfigurationForm']['rollup'] = true;
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
            //Now filter by meeting, task, and note
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'Meeting';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'Note';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'Task';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
            //Now do the same thing with filtering but turn off rollup.
            $getData['LatestActivitiesConfigurationForm']['rollup'] = true;
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'Meeting';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'Note';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'Task';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
        }
    }
?>