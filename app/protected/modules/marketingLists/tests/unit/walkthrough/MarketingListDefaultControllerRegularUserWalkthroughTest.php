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

    class MarketingListDefaultControllerRegularUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $user;

        protected static $listOwnedBySuper;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            UserTestHelper::createBasicUser('nobody');

            static::$listOwnedBySuper = MarketingListTestHelper::createMarketingListByName('MarketingListName',
                                                                                            'MarketingList Description');
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            Yii::app()->user->userModel = $this->user;
        }

        public function testRegularUserAllDefaultControllerActions()
        {
            $marketingList = MarketingListTestHelper::createMarketingListByName('MarketingListName 01',
                                                                                'MarketingListDescription 01');
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default');
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/index');
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/list');
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/create');
            $this->setGetArray(array('id' => $marketingList->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/edit');
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/details');
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/getInfoToCopyToCampaign');
            $this->setGetArray(array('term' => 'inexistant'));
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/autoComplete');
            $this->setGetArray(array(
                'modalTransferInformation'   => array(
                    'sourceIdFieldId'    =>  'Campaign_marketingList_id',
                    'sourceNameFieldId'  =>  'Campaign_marketingList_name',
                    'modalId'            =>  'modalContainer-edit-form',
                )
            ));
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/modalList');
            $this->resetGetArray();

            $this->user->setRight('MarketingListsModule', MarketingListsModule::getAccessRight());
            $this->assertTrue($this->user->save());
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->setGetArray(array('term' => 'inexistant'));
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/autoComplete');
            $this->setGetArray(array('id' => $marketingList->id));
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/getInfoToCopyToCampaign');
            $this->setGetArray(array(
                'modalTransferInformation'   => array(
                    'sourceIdFieldId'    =>  'Campaign_marketingList_id',
                    'sourceNameFieldId'  =>  'Campaign_marketingList_name',
                    'modalId'            =>  'modalContainer-edit-form',
                )
            ));
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/modalList');

            $this->setGetArray(array('id' => $marketingList->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/details');
            $this->assertTrue(strpos($content, '<p>To manage Marketing Lists you must have access to either contacts' .
                ' or leads. Contact the CRM administrator about this issue.</p>') !== false);
            $this->resetGetArray();

            $this->user->setRight('MarketingListsModule', MarketingListsModule::getCreateRight());
            $this->assertTrue($this->user->save());
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/create');
            $this->assertTrue(strpos($content, '<p>To manage Marketing Lists you must have access to either contacts' .
                ' or leads. Contact the CRM administrator about this issue.</p>') !== false);

            $this->user->setRight('ContactsModule', ContactsModule::getAccessRight());
            $this->user->setRight('LeadsModule', LeadsModule::getAccessRight());
            $this->assertTrue($this->user->save());
            $this->setGetArray(array('id' => $marketingList->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/details');
            $this->assertTrue(strpos($content, '<p>To manage Marketing Lists you must have access to either contacts' .
                ' or leads. Contact the CRM administrator about this issue.</p>') === false);
            $this->resetGetArray();

            $this->user->setRight('MarketingListsModule', MarketingListsModule::getCreateRight());
            $this->assertTrue($this->user->save());
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/create');
            $this->assertTrue(strpos($content, '<p>To manage Marketing Lists you must have access to either contacts' .
                ' or leads. Contact the CRM administrator about this issue.</p>') === false);

            $this->setGetArray(array('id' => $marketingList->id));
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/edit');

            $this->user->setRight('MarketingListsModule', MarketingListsModule::getDeleteRight());
            $this->assertTrue($this->user->save());
            $this->runControllerWithRedirectExceptionAndGetUrl('marketingLists/default/delete');

            $this->setGetArray(array('id' => static::$listOwnedBySuper->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/edit');
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/details');
            $this->runControllerShouldResultInAccessFailureAndGetContent('marketingLists/default/delete');
        }
    }
?>