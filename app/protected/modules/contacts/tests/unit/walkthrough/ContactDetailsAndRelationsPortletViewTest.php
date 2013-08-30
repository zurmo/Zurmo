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

    class ContactDetailsAndRelationsPortletViewTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            ContactTestHelper::createContactByNameForOwner('superContact', $super);

            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);

            //Setup test marketingList
            MarketingListTestHelper::createMarketingListByName('MarketingListName',
                                                               'MarketingList Description',
                                                               'first',
                                                               'first@zurmo.com');
        }

        public function testAdditionOfPortletsInEmptyRightPanel()
        {
            $super            = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superContactId   = self::getModelIdByModelNameAndName ('Contact', 'superContact superContactson');
            $marketingListId  = self::getModelIdByModelNameAndName ('MarketingList', 'MarketingListName');
            $contacts         = Contact::getAll();
            $this->assertEquals(1, count($contacts));
            //Load Model Detail Views
            $this->setGetArray(array('id' => $superContactId, 'lockPortlets' => '0'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/details');

            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                                    'ContactDetailsAndRelationsView', $super->id, array());

            $this->assertEquals (3, count($portlets[1]));
            $this->assertFalse  (array_key_exists(3, $portlets) );
            $this->assertEquals (3, count($portlets[2]));
            foreach ($portlets[2] as $position => $portlet)
            {
                $portlet->delete();
            }
            $this->setGetArray(array(
                                        'modelId'        => $superContactId,
                                        'uniqueLayoutId' => 'ContactDetailsAndRelationsView',
                                        'portletType'    => 'MarketingListsForContactRelatedList',
                                        'redirect'       => '0'
                                    ));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('contacts/defaultPortlet/add', true);
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                                    'ContactDetailsAndRelationsView', $super->id, array());
            $this->assertEquals (1, count($portlets[2]));

            //Test subscribe to list link
            $portlet = $portlets[2][1];
            $this->setGetArray(array('portletId'               => $portlet->id,
                                     'relationAttributeName'   => 'contact',
                                     'relationModelId'         => $superContactId,
                                     'relationModuleId'        => 'contacts',
                                     'uniqueLayoutId'          => $portlet->getUniquePortletPageId(),
                                     'relationModelClassName'  => null,
                                    ));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/selectFromRelatedList');

            //Test subscribe a marketing list
            $this->setGetArray(array('modelId'                 => $marketingListId,
                                     'portletId'               => $portlet->id,
                                     'relationAttributeName'   => 'contact',
                                     'relationModelId'         => $superContactId,
                                     'relationModuleId'        => 'contacts',
                                     'uniqueLayoutId'          => $portlet->getUniquePortletPageId(),
                                     'relationModelClassName'  => null,
                                    ));
            $this->resetPostArray();
            $content = $this->runControllerWithRedirectExceptionAndGetContent(
                            'marketingLists/defaultPortlet/selectFromRelatedListSave');
        }
    }
?>
