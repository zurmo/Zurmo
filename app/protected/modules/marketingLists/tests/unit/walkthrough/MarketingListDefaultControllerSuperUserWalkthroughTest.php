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

    class MarketingListDefaultControllerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $this->user;
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            // Test all default controller actions that do not require any POST/GET variables to be passed.
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/create');

            MarketingListTestHelper::createMarketingListByName('MarketingListName', 'MarketingList Description',
                'first', 'first@zurmo.com');
            MarketingListTestHelper::createMarketingListByName('MarketingListName2', 'MarketingList Description2',
                'second', 'second@zurmo.com');

            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/create');
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserListAction()
        {
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') !== false);
            $this->assertTrue(strpos($content, 'MarketingListName') !== false);
            $this->assertTrue(strpos($content, 'MarketingListName2') !== false);
            $this->assertEquals(2, substr_count($content, 'MarketingListName'));
            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('ajax' => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') === false);
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserListSearchAction()
        {
            StickyReportUtil::clearDataByKey('MarketingListsSearchForm');
            $this->setGetArray(array(
                'MarketingListsSearchForm' => array(
                    'anyMixedAttributesScope'    => array('All'),
                    'anyMixedAttributes'         => 'xyz',
                ) ,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertTrue(strpos($content, 'No results found.') !== false);

            StickyReportUtil::clearDataByKey('MarketingListsSearchForm');
            $this->setGetArray(array(
                'MarketingListsSearchForm' => array(
                    'anyMixedAttributesScope'    => array('All'),
                    'anyMixedAttributes'         => 'Marketing',
                ) ,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertTrue(strpos($content, '2 result(s)') !== false);
            $this->assertEquals(2, substr_count($content, 'MarketingListName'));
            $this->assertTrue(strpos($content, 'Clark Kent') !== false);

            StickyReportUtil::clearDataByKey('MarketingListsSearchForm');
            $this->setGetArray(array(
                'MarketingListsSearchForm' => array(
                    'anyMixedAttributesScope'    => array('All'),
                    'anyMixedAttributes'         => 'Marketing',
                    'selectedListAttributes'     => array('name', 'createdByUser', 'fromAddress', 'fromName'),
                ) ,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertTrue(strpos($content, '2 result(s)') !== false);
            $this->assertEquals(2, substr_count($content, 'MarketingListName'));
            $this->assertTrue(strpos($content, 'Clark Kent') !== false);
            $this->assertEquals(2, substr_count($content, 'Clark Kent'));
            $this->assertTrue(strpos($content, '@zurmo.com') !== false);
            $this->assertEquals(4, substr_count($content, '@zurmo.com'));
            $this->assertEquals(2, substr_count($content, 'first@zurmo.com'));
            $this->assertEquals(2, substr_count($content, 'second@zurmo.com'));

            StickyReportUtil::clearDataByKey('MarketingListsSearchForm');
            $this->setGetArray(array(
                'clearingSearch'            =>  1,
                'MarketingListsSearchForm'  => array(
                    'anyMixedAttributesScope'    => array('All'),
                    'anyMixedAttributes'         => '',
                    'selectedListAttributes'     => array('name', 'createdByUser', 'fromAddress', 'fromName'),
                    'dynamicClauses'             => array(array(
                        'attributeIndexOrDerivedType'   => 'fromAddress',
                        'structurePosition'             => 1,
                        'fromAddress'                   => 'second@zurmo.com',
                    )),
                    'dynamicStructure'          => '1',
                ) ,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertTrue(strpos($content, '1 result(s)') !== false);
            $this->assertEquals(1, substr_count($content, 'MarketingListName2'));
            $this->assertTrue(strpos($content, 'Clark Kent') !== false);
            $this->assertEquals(1, substr_count($content, 'Clark Kent'));
            $this->assertTrue(strpos($content, '@zurmo.com') !== false);
            $this->assertEquals(2, substr_count($content, '@zurmo.com'));
            $this->assertEquals(2, substr_count($content, 'second@zurmo.com'));

            StickyReportUtil::clearDataByKey('MarketingListsSearchForm');
            $this->setGetArray(array(
                'clearingSearch'            =>  1,
                'MarketingListsSearchForm'  =>  array(
                    'anyMixedAttributesScope'    => array('All'),
                    'anyMixedAttributes'         => '',
                    'selectedListAttributes'     => array('name', 'createdByUser', 'fromAddress', 'fromName'),
                    'dynamicClauses'             => array(array(
                        'attributeIndexOrDerivedType'   => 'fromName',
                        'structurePosition'             => 1,
                        'fromName'                   => 'first',
                    )),
                    'dynamicStructure'          => '1',
                ) ,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertTrue(strpos($content, '1 result(s)') !== false);
            $this->assertEquals(1, substr_count($content, 'MarketingListName'));
            $this->assertTrue(strpos($content, 'Clark Kent') !== false);
            $this->assertEquals(1, substr_count($content, 'Clark Kent'));
            $this->assertTrue(strpos($content, '@zurmo.com') !== false);
            $this->assertEquals(2, substr_count($content, '@zurmo.com'));
            $this->assertEquals(2, substr_count($content, 'first@zurmo.com'));
        }

        /**
         * @depends testSuperUserListAction
         */
        public function testSuperUserCreateAction()
        {
            // TODO: @Shoaibi: Low: Add test with different permissions
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/create');
            $this->assertTrue(strpos($content, 'Create Marketing List') !== false);
            $this->assertTrue(strpos($content, '<label for="MarketingList_name" class="required">Name ' .
                                                                '<span class="required">*</span></label>') !== false);
            $this->assertTrue(strpos($content, '<label for="MarketingList_description">Description</label>') !== false);
            $this->assertTrue(strpos($content, '<label for="MarketingList_fromName">From Name</label>') !== false);
            $this->assertTrue(strpos($content, '<label for="MarketingList_fromAddress">From Address</label>') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Cancel</span>') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Save</span>') !== false);

            $this->resetGetArray();
            $this->setPostArray(array('MarketingList' => array(
                'name'          => '',
                'description'   => '',
                'fromName'      => '',
                'fromAddress'   => '',
                )));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/create');
            $this->assertTrue(strpos($content, 'class="errorMessage">Name cannot be blank.</div>') !== false);
            $this->assertTrue(strpos($content, '<input id="MarketingList_name" name="MarketingList[name]" type="text"' .
                                                                ' maxlength="64" value="" class="error"') !== false);
            $this->assertTrue(strpos($content, '<label class="error required" for="MarketingList_name">Name ' .
                                                                 '<span class="required">*</span></label>') !== false);
            $this->resetGetArray();
            $this->setPostArray(array('MarketingList' => array(
                'name'            => 'New MarketingListName using Create',
                'description'     => 'New MarketingList Description using Create',
                'fromName'        => 'Zurmo Sales',
                'fromAddress'     => 'sales@zurmo.com',
                )));
            $redirectUrl    = $this->runControllerWithRedirectExceptionAndGetUrl('marketingLists/default/create');
            $marketingList = MarketingList::getByName('New MarketingListName using Create');
            $this->assertEquals(1, count($marketingList));
            $this->assertTrue  ($marketingList[0]->id > 0);
            $this->assertEquals('sales@zurmo.com', $marketingList[0]->fromAddress);
            $this->assertEquals('Zurmo Sales', $marketingList[0]->fromName);
            $this->assertEquals('New MarketingList Description using Create', $marketingList[0]->description);
            $this->assertTrue  ($marketingList[0]->owner == $this->user);
            $compareRedirectUrl = Yii::app()->createUrl('marketingLists/default/details', array('id' => $marketingList[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $marketingList = MarketingList::getAll();
            $this->assertEquals(3, count($marketingList));
        }

        public function testSuperUserDetailsAction()
        {
            $marketingListId = self::getModelIdByModelNameAndName ('MarketingList', 'MarketingListName2');
            $this->setGetArray(array('id' => $marketingListId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/details');
            $this->assertTrue(strpos($content, 'MarketingListName2') !== false);
            $this->assertEquals(3, substr_count($content, 'MarketingListName2'));
            $this->assertTrue(strpos($content, '<span>Details</span></a>') !== false);
            $this->assertTrue(strpos($content, '<strong class="marketing-list-subscribers-stats">' .
                                                                                    '0 Subscribed</strong>') !== false);
            $this->assertTrue(strpos($content, '<strong class="marketing-list-unsubscribers-stats">' .
                                                                                    '0 Unsubscribed</strong>') !== false);
            $this->assertTrue(strpos($content, 'MarketingList Description2') !== false);
            $this->assertTrue(strpos($content, '<span>Options</span></a>') !== false);
            $this->assertTrue(strpos($content, '<span>Edit</span></a></li>') !== false);
            $this->assertTrue(strpos($content, '<span>Delete</span></a></li>') !== false);
            $this->assertTrue(strpos($content, '<h3>Contacts/Leads</h3>') !== false);
            $this->assertTrue(strpos($content, '<span>Add Contact/Lead</span></a>') !== false);
            $this->assertTrue(strpos($content, 'From Contacts/Leads</label>') !== false);
            $this->assertTrue(strpos($content, 'From Report</label>') !== false);
            $this->assertTrue(strpos($content, '<span>Subscribe</span></a>') !== false);
            $this->assertTrue(strpos($content, '<span>Unsubscribe</span></a>') !== false);
            $this->assertTrue(strpos($content, '<span>Delete</span></a>') !== false);
        }

        /**
         * @depends testSuperUserCreateAction
         */
        public function testSuperUserEditAction()
        {
            $marketingListId = self::getModelIdByModelNameAndName ('MarketingList', 'New MarketingListName using Create');
            $this->setGetArray(array('id' => $marketingListId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/edit');
            $this->assertTrue(strpos($content, 'New MarketingListName using Create') !== false);
            $this->assertEquals(2, substr_count($content, 'New MarketingListName using Create'));
            $this->assertTrue(strpos($content, 'New MarketingList Description using Create') !== false);
            $this->assertTrue(strpos($content, 'Zurmo Sales') !== false);
            $this->assertTrue(strpos($content, 'sales@zurmo.com') !== false);
            $this->assertTrue(strpos($content, 'Create Marketing List') === false);

            $this->setPostArray(array('MarketingList' => array(
                'name'            => 'New MarketingListName',
                'description'     => 'New MarketingList Description',
                'fromName'        => 'Zurmo Support',
                'fromAddress'     => 'support@zurmo.com',
            )));
            $redirectUrl    = $this->runControllerWithRedirectExceptionAndGetUrl('marketingLists/default/edit');
            $marketingList = MarketingList::getByName('New MarketingListName');
            $this->assertEquals(1, count($marketingList));
            $this->assertTrue  ($marketingList[0]->id > 0);
            $this->assertEquals('support@zurmo.com', $marketingList[0]->fromAddress);
            $this->assertEquals('Zurmo Support', $marketingList[0]->fromName);
            $this->assertEquals('New MarketingList Description', $marketingList[0]->description);
            $compareRedirectUrl = Yii::app()->createUrl('marketingLists/default/details', array('id' => $marketingList[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $marketingList = MarketingList::getAll();
            $this->assertEquals(3, count($marketingList));
        }

        /**
         * @depends testSuperUserEditAction
         */
        public function testSuperUserDeleteAction()
        {
            $marketingListId = self::getModelIdByModelNameAndName ('MarketingList', 'New MarketingListName');

            // Delete a marketingList.
            $this->setGetArray(array('id' => $marketingListId));
            $this->resetPostArray();
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('marketingLists/default/delete');
            $compareRedirectUrl = Yii::app()->createUrl('marketingLists/default/index');
            $this->assertEquals($redirectUrl, $compareRedirectUrl);
            $marketingLists = MarketingList::getAll();
            $this->assertEquals(2, count($marketingLists));
        }

        public function testMarketingListDashboardGroupByActions()
        {
            $portlets = Portlet::getAll();
            foreach ($portlets as $portlet)
            {
                if ($portlet->viewType = 'MarketingListOverallMetrics')
                {
                    $marketingListPortlet = $portlet;
                }
            }
            $marketingLists = MarketingList::getAll();

            $this->setGetArray(array(
                        'portletId'         => $portlet->id,
                        'uniqueLayoutId'    => 'MarketingListDetailsAndRelationsViewLeftBottomView',
                        'portletParams'     => array('relationModelId'  => $marketingLists[0]->id,
                                                     'relationModuleId' => 'marketingLists',
                            ),
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

        public function testAutoComplete()
        {
            $this->setGetArray(array('term' => 'inexistant'));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/autoComplete');
            $decodedContent     = CJSON::decode($content);
            $this->assertNotEmpty($decodedContent);
            $this->assertArrayHasKey(0, $decodedContent);
            $decodedContent     = $decodedContent[0];
            $this->assertArrayHasKey('id', $decodedContent);
            $this->assertArrayHasKey('value', $decodedContent);
            $this->assertArrayHasKey('label', $decodedContent);
            $this->assertNull($decodedContent['id']);
            $this->assertNull($decodedContent['value']);
            $this->assertNotNull($decodedContent['label']);
            $this->assertEquals('No results found', $decodedContent['label']);

            $this->setGetArray(array('term' => 'Mark'));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/autoComplete');
            $decodedContent     = CJSON::decode($content);
            $this->assertNotEmpty($decodedContent);
            $this->assertArrayHasKey(0, $decodedContent);
            $this->assertArrayHasKey(1, $decodedContent);
            $result1     = $decodedContent[0];
            $result2     = $decodedContent[1];

            $this->assertArrayHasKey('id', $result1);
            $this->assertArrayHasKey('value', $result1);
            $this->assertArrayHasKey('label', $result1);
            $this->assertNotNull($result1['id']);
            $this->assertEquals($result1['value'], $result1['label']);
            $this->assertNotNull($result1['label']);
            $this->assertEquals('MarketingListName', $result1['label']);

            $this->assertArrayHasKey('id', $result2);
            $this->assertArrayHasKey('value', $result2);
            $this->assertArrayHasKey('label', $result2);
            $this->assertNotNull($result2['id']);
            $this->assertEquals($result2['value'], $result2['label']);
            $this->assertNotNull($result2['label']);
            $this->assertEquals('MarketingListName2', $result2['label']);
        }

        public function testGetInfoToCopyToCampaign()
        {
            $marketingListId    = self::getModelIdByModelNameAndName('MarketingList', 'MarketingListName');
            $marketingList      = MarketingList::getById($marketingListId);
            $this->setGetArray(array('id' => $marketingListId));
            $content            = $this->runControllerWithNoExceptionsAndGetContent(
                                                                    'marketingLists/default/getInfoToCopyToCampaign');
            $decodedContent     = CJSON::decode($content);
            $this->assertNotEmpty($decodedContent);
            $this->assertArrayHasKey('fromName', $decodedContent);
            $this->assertArrayHasKey('fromAddress', $decodedContent);
            $this->assertEquals($marketingList->fromName, $decodedContent['fromName']);
            $this->assertEquals($marketingList->fromAddress, $decodedContent['fromAddress']);
        }

        public function testModalList()
        {
            $this->setGetArray(array(
               'modalTransferInformation'   => array(
                   'sourceIdFieldId'    =>  'Campaign_marketingList_id',
                   'sourceNameFieldId'  =>  'Campaign_marketingList_name',
                   'modalId'            =>  'modalContainer-edit-form',
               )
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/modalList');
            $this->assertTrue(strpos($content, '<div id="ModalView">') !== false);
            $this->assertTrue(strpos($content, '<div id="MarketingListsModalSearchAndListView" ' .
                                                'class="ModalSearchAndListView GridView">') !== false);
            $this->assertTrue(strpos($content, '<div id="MarketingListsModalSearchView" class="SearchView ModelView' .
                                                ' ConfigurableMetadataView MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="wide form">') !== false);
            $this->assertTrue(strpos($content, '<form id="search-formmodal" method="post">') !== false);
            $this->assertTrue(strpos($content, '</div><div class="search-view-0"') !== false);
            $this->assertTrue(strpos($content, '<table><tr><th></th><td colspan="3">') !== false);
            $this->assertTrue(strpos($content, '<select class="ignore-style ignore-clearform" id="MarketingListsSearch' .
                                                'Form_anyMixedAttributesScope" multiple="multiple" ' .
                                                'style="display:none;" size="4" name="MarketingListsSearchForm' .
                                                '[anyMixedAttributesScope][]">') !== false);
            $this->assertTrue(strpos($content, '<option value="All" selected="selected">All</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="name">Name</option>') !== false);
            $this->assertTrue(strpos($content, '<input class="input-hint anyMixedAttributes-input" ' .
                                                'onfocus="$(this).select();" size="80" id="MarketingListsSearchForm' .
                                                '_anyMixedAttributes" name="MarketingListsSearchForm' .
                                                '[anyMixedAttributes]" type="text"') !== false);
            $this->assertTrue(strpos($content, '</div><div class="search-form-tools">') !== false);
            $this->assertTrue(strpos($content, '<a id="clear-search-linkmodal" style="display:none;" href="#">' .
                                                'Clear</a>') !== false);
            $this->assertTrue(strpos($content, '<input id="clearingSearch-search-formmodal" type="hidden" ' .
                                                'name="clearingSearch"') !== false);
            $this->assertTrue(strpos($content, '</div></form>') !== false);
            $this->assertTrue(strpos($content, '<div id="modalContainer-search-formmodal"></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="MarketingListsModalListView" class="ModalListView ListView ' .
                                                'ModelView ConfigurableMetadataView MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="cgrid-view type-marketingLists" id="list-viewmodal">') !== false);
            $this->assertTrue(strpos($content, '<div class="summary">1-2 of 2 result(s).</div>') !== false);
            $this->assertTrue(strpos($content, '<table class="items">') !== false);
            $this->assertTrue(strpos($content, '<th id="list-viewmodal_c0">') !== false);
            $this->assertTrue(strpos($content, '<a class="sort-link" href="') !== false);
            $this->assertTrue(strpos($content, 'marketingLists/default/modalList?modalTransferInformation%5BsourceId' . // Not Coding Standard
                                                'FieldId%5D=Campaign_marketingList_id&amp;modalTransferInformation%5B' . // Not Coding Standard
                                                'sourceNameFieldId%5D=Campaign_marketingList_name&amp;modalTransfer' .  // Not Coding Standard
                                                'Information%5BmodalId%5D=modalContainer-edit-form&amp;MarketingList' . // Not Coding Standard
                                                '_sort=name">Name</a></th></tr>') !== false);                           // Not Coding Standard
            $this->assertTrue(strpos($content, '<tr class="odd">') !== false);
            $this->assertTrue(strpos($content, 'MarketingListName</a></td></tr>') !== false);
            $this->assertTrue(strpos($content, '<tr class="even">') !== false);
            $this->assertTrue(strpos($content, 'MarketingListName2</a></td></tr>') !== false);
            $this->assertTrue(strpos($content, '<div class="pager horizontal">') !== false);
            $this->assertTrue(strpos($content, '<li class="refresh hidden">') !== false);
            $this->assertTrue(strpos($content, 'marketingLists/default/modalList?modalTransferInformation%5Bsource'.    // Not Coding Standard
                                                'IdFieldId%5D=Campaign_marketingList_id&amp;modalTransferInformation'.  // Not Coding Standard
                                                '%5BsourceNameFieldId%5D=Campaign_marketingList_name&amp;modal' .       // Not Coding Standard
                                                'TransferInformation%5BmodalId%5D=modalContainer-edit-form">' .         // Not Coding Standard
                                                'refresh</a></li></ul>') !== false);
            $this->assertTrue(strpos($content, '</div><div class="list-preloader">') !== false);
            $this->assertTrue(strpos($content, '<span class="z-spinner"></span></div>') !== false);
        }
    }
?>