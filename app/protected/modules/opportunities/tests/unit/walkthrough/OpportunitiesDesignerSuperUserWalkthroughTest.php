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
    * Designer Module Walkthrough of Opportunities.
    * Walkthrough for the super user of all possible controller actions.
    * Since this is a super user, he should have access to all controller actions
    * without any exceptions being thrown.
    * This also test the creation of the customfileds, addition of custom fields to all the layouts including the search
    * views.
    * This also test creation search, edit and delete of the Opportunity based on the custom fields.
    */
    class OpportunitiesDesignerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Currency::makeBaseCurrency();

            //Create a account for testing.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);

            //Create a Opportunity for testing.
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp', $super, $account);
        }

         public function testSuperUserOpportunityDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Opportunity Modules Menu.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/modulesMenu');

            //Load AttributesList for Opportunity module.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');

            //Load ModuleLayoutsList for Opportunity module.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');

            //Load ModuleEdit view for each applicable module.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleEdit');

            //Now validate save with failed validation.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'OpportunitiesModuleForm' => $this->createModuleEditBadValidationPostData()));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.

            //Now validate save with successful validation.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'OpportunitiesModuleForm' => $this->createModuleEditGoodValidationPostData('opp new name')));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertEquals('[]', $content);

            //Now save successfully.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $this->setPostArray(array('save' => 'Save',
                'OpportunitiesModuleForm' => $this->createModuleEditGoodValidationPostData('opp new name')));
            $this->runControllerWithRedirectExceptionAndGetContent('designer/default/moduleEdit');

            //Now confirm everything did in fact save correctly.
            $this->assertEquals('Opp New Name',  OpportunitiesModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Opp New Names', OpportunitiesModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('opp new name',  OpportunitiesModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('opp new names', OpportunitiesModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            //Load LayoutEdit for each applicable module and applicable layout
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesModalListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesModalSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesMassEditView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunityEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
        }

        /**
         * @depends testSuperUserOpportunityDefaultControllerActions
         */
        public function testSuperUserCustomFieldsWalkthroughForOpportunitiesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeCreate');

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('OpportunitiesModule', 'checkbox');
            $this->createCurrencyValueCustomFieldByModule       ('OpportunitiesModule', 'currency');
            $this->createDateCustomFieldByModule                ('OpportunitiesModule', 'date');
            $this->createDateTimeCustomFieldByModule            ('OpportunitiesModule', 'datetime');
            $this->createDecimalCustomFieldByModule             ('OpportunitiesModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('OpportunitiesModule', 'picklist');
            $this->createDependentDropDownCustomFieldByModule   ('OpportunitiesModule', 'countrypicklist');
            $this->createDependentDropDownCustomFieldByModule   ('OpportunitiesModule', 'statepicklist');
            $this->createDependentDropDownCustomFieldByModule   ('OpportunitiesModule', 'citypicklist');
            $this->createIntegerCustomFieldByModule             ('OpportunitiesModule', 'integer');
            $this->createMultiSelectDropDownCustomFieldByModule ('OpportunitiesModule', 'multiselect');
            $this->createTagCloudCustomFieldByModule            ('OpportunitiesModule', 'tagcloud');
            $this->createCalculatedNumberCustomFieldByModule    ('OpportunitiesModule', 'calculatednumber');
            $this->createDropDownDependencyCustomFieldByModule  ('OpportunitiesModule', 'dropdowndependency');
            $this->createPhoneCustomFieldByModule               ('OpportunitiesModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('OpportunitiesModule', 'radio');
            $this->createTextCustomFieldByModule                ('OpportunitiesModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('OpportunitiesModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('OpportunitiesModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForOpportunitiesModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForOpportunitiesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to OpportunityEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunityEditAndDetailsView'));
            $layout = OpportunitiesDesignerWalkthroughHelperUtil::getOpportunityEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to OpportunitiesSearchView.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesSearchView'));
            $layout = OpportunitiesDesignerWalkthroughHelperUtil::getOpportunitiesSearchViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to OpportunitiesListView.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesListView'));
            $layout = OpportunitiesDesignerWalkthroughHelperUtil::getOpportunitiesListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to OpportunitiesRelatedListView.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesRelatedListView'));
            $layout = OpportunitiesDesignerWalkthroughHelperUtil::getOpportunitiesListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to OpportunitiesMassEditView.
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule',
                                     'viewClassName'   => 'OpportunitiesMassEditView'));
            $layout = OpportunitiesDesignerWalkthroughHelperUtil::getOpportunitiesMassEditViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }

        /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForOpportunitiesModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForOpportunitiesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superOpportunityId = self::getModelIdByModelNameAndName ('Opportunity', 'superOpp');
            //Load create, edit, and details views.
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/create');
            $this->setGetArray(array('id' => $superOpportunityId));
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/edit');
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/list');
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/modalList');
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/massEdit');
        }

        /**
         * @depends testLayoutsLoadOkAfterCustomFieldsPlacedForOpportunitiesModule
         */
        public function testCreateAnOpportunityAfterTheCustomFieldsArePlacedForOpportunitiesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Retrieve the account id and the super account id.
            $accountId   = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superUserId = $super->id;

            //Create a new Opportunity based on the custom fields.
            $this->resetGetArray();
            $this->setPostArray(array('Opportunity' => array(
                            'name'                              => 'myNewOpportunity',
                            'amount'                            => array('value' => 298000,
                                                                         'currency' => array('id' => $baseCurrency->id)),
                            'account'                           => array('id' => $accountId),
                            'probability'                       => '1',
                            'closeDate'                         => $date,
                            'stage'                             => array('value' => 'Prospecting'),
                            'source'                            => array('value' => 'Self-Generated'),
                            'description'                       => 'This is the Description',
                            'owner'                             => array('id' => $superUserId),
                            'explicitReadWriteModelPermissions' => array('type' => null),
                            'checkbox'                          => '1',
                            'currency'                          => array('value'    => 45,
                                                                         'currency' => array('id' => $baseCurrency->id)),
                            'date'                              => $date,
                            'datetime'                          => $datetime,
                            'decimal'                           => '123',
                            'picklist'                          => array('value' => 'a'),
                            'multiselect'                       => array('values' => array('ff', 'rr')),
                            'tagcloud'                          => array('values' => array('writing', 'gardening')),
                            'countrypicklist'                   => array('value'  => 'bbbb'),
                            'statepicklist'                     => array('value'  => 'bbb1'),
                            'citypicklist'                      => array('value'  => 'bb1'),
                            'integer'                           => '12',
                            'phone'                             => '259-784-2169',
                            'radio'                             => array('value' => 'd'),
                            'text'                              => 'This is a test Text',
                            'textarea'                          => 'This is a test TextArea',
                            'url'                               => 'http://wwww.abc.com')));
            $this->runControllerWithRedirectExceptionAndGetUrl('opportunities/default/create');

            //Check the details if they are saved properly for the custom fields.
            $opportunityId = self::getModelIdByModelNameAndName('Opportunity', 'myNewOpportunity');
            $opportunity   = Opportunity::getById($opportunityId);

            //Retrieve the permission of the opportunity.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($opportunity);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($opportunity->name                       , 'myNewOpportunity');
            $this->assertEquals($opportunity->amount->value              , '298000');
            $this->assertEquals($opportunity->amount->currency->id       , $baseCurrency->id);
            $this->assertEquals($opportunity->account->id                , $accountId);
            $this->assertEquals($opportunity->probability                , '1');
            $this->assertEquals($opportunity->stage->value               , 'Prospecting');
            $this->assertEquals($opportunity->source->value              , 'Self-Generated');
            $this->assertEquals($opportunity->description                , 'This is the Description');
            $this->assertEquals($opportunity->owner->id                  , $superUserId);
            $this->assertEquals(0                                        , count($readWritePermitables));
            $this->assertEquals(0                                        , count($readOnlyPermitables));
            $this->assertEquals($opportunity->checkbox                   , '1');
            $this->assertEquals($opportunity->currency->value            , 45);
            $this->assertEquals($opportunity->currency->currency->id     , $baseCurrency->id);
            $this->assertEquals($opportunity->date                       , $dateAssert);
            $this->assertEquals($opportunity->datetime                   , $datetimeAssert);
            $this->assertEquals($opportunity->decimal                    , '123');
            $this->assertEquals($opportunity->picklist->value            , 'a');
            $this->assertEquals($opportunity->integer                    , 12);
            $this->assertEquals($opportunity->phone                      , '259-784-2169');
            $this->assertEquals($opportunity->radio->value               , 'd');
            $this->assertEquals($opportunity->text                       , 'This is a test Text');
            $this->assertEquals($opportunity->textarea                   , 'This is a test TextArea');
            $this->assertEquals($opportunity->url                        , 'http://wwww.abc.com');
            $this->assertEquals($opportunity->countrypicklist->value     , 'bbbb');
            $this->assertEquals($opportunity->statepicklist->value       , 'bbb1');
            $this->assertEquals($opportunity->citypicklist->value        , 'bb1');
            $this->assertContains('ff'                                   , $opportunity->multiselect->values);
            $this->assertContains('rr'                                   , $opportunity->multiselect->values);
            $this->assertContains('writing'                              , $opportunity->tagcloud->values);
            $this->assertContains('gardening'                            , $opportunity->tagcloud->values);
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calculatednumber', 'Opportunity');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $opportunity);
            $this->assertEquals(1476                                     , $testCalculatedValue);
        }

        /**
         * @depends testCreateAnOpportunityAfterTheCustomFieldsArePlacedForOpportunitiesModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForOpportunitiesModuleAfterCreatingTheOpportunity()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the account id and the super user id.
            $accountId      = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superUserId    = $super->id;
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Search a created opportunity using the customfield.
            $this->resetPostArray();
            $this->setGetArray(array('OpportunitiesSearchForm' => array(
                                                'name'               => 'myNewOpportunity',
                                                'owner'              => array('id' => $superUserId),
                                                'ownedItemsOnly'     => '1',
                                                'account'            => array('id' => $accountId),
                                                'amount'             => array('value' => '298000',
                                                                             'currency' => array(
                                                                             'id' => $baseCurrency->id)),
                                                'closeDate__Date'    => array('value' => 'Today'),
                                                'stage'              => array('value' => 'Prospecting'),
                                                'source'             => array('value' => 'Self-Generated'),
                                                'probability'        => '1',
                                                'decimal'            => '123',
                                                'integer'            => '12',
                                                'phone'              => '259-784-2169',
                                                'text'               => 'This is a test Text',
                                                'textarea'           => 'This is a test TextArea',
                                                'url'                => 'http://wwww.abc.com',
                                                'checkbox'           => array('value'  =>  '1'),
                                                'currency'           => array('value'  =>  45),
                                                'picklist'           => array('value'  =>  'a'),
                                                'multiselect'        => array('values' => array('ff', 'rr')),
                                                'tagcloud'           => array('values' => array('writing', 'gardening')),
                                                'countrypicklist'    => array('value'  => 'bbbb'),
                                                'statepicklist'      => array('value'  => 'bbb1'),
                                                'citypicklist'       => array('value'  => 'bb1'),
                                                'radio'              => array('value'  =>  'd'),
                                                'date__Date'         => array('type'   =>  'Today'),
                                                'datetime__DateTime' => array('type'   =>  'Today')),
                                     'ajax' =>  'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default');

            //Check if the opportunity name exits after the search is performed on the basis of the
            //custom fields added to the opportunities module.
            $this->assertTrue(strpos($content, "Displaying 1-1 of 1 result(s).") > 0);
            $this->assertTrue(strpos($content, "myNewOpportunity") > 0);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForOpportunitiesModuleAfterCreatingTheOpportunity
         */
        public function testEditOfTheOpportunityForTheTagCloudFieldAfterRemovingAllTagsPlacedForOpportunitiesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Retrieve the account id, the super user id and opportunity Id.
            $accountId                        = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superUserId                      = $super->id;
            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;
            $opportunity   = Opportunity::getByName('myNewOpportunity');
            $opportunityId = $opportunity[0]->id;
            $this->assertEquals(2, $opportunity[0]->tagcloud->values->count());

            //Edit a new Opportunity based on the custom fields.
            $this->setGetArray(array('id' => $opportunityId));
            $this->setPostArray(array('Opportunity' => array(
                            'name'                              => 'myEditOpportunity',
                            'amount'                            => array('value' => 288000,
                                                                         'currency' => array(
                                                                         'id' => $baseCurrency->id)),
                            'account'                           => array('id' => $accountId),
                            'probability'                       => '2',
                            'closeDate'                         => $date,
                            'stage'                             => array('value' => 'Qualification'),
                            'source'                            => array('value' => 'Inbound Call'),
                            'description'                       => 'This is the Edit Description',
                            'owner'                             => array('id' => $superUserId),
                            'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
                            'checkbox'                          => '0',
                            'currency'                          => array('value'   => 40,
                                                                         'currency' => array(
                                                                         'id' => $baseCurrency->id)),
                            'decimal'                           => '12',
                            'date'                              => $date,
                            'datetime'                          => $datetime,
                            'picklist'                          => array('value'  => 'b'),
                            'multiselect'                       => array('values' =>  array('gg', 'hh')),
                            'tagcloud'                          => array('values' =>  array()),
                            'countrypicklist'                   => array('value'  => 'aaaa'),
                            'statepicklist'                     => array('value'  => 'aaa1'),
                            'citypicklist'                      => array('value'  => 'ab1'),
                            'integer'                           => '11',
                            'phone'                             => '259-784-2069',
                            'radio'                             => array('value' => 'e'),
                            'text'                              => 'This is a test Edit Text',
                            'textarea'                          => 'This is a test Edit TextArea',
                            'url'                               => 'http://wwww.abc-edit.com')));
            $this->runControllerWithRedirectExceptionAndGetUrl('opportunities/default/edit');

            //Check the details if they are saved properly for the custom fields.
            $opportunityId = self::getModelIdByModelNameAndName('Opportunity', 'myEditOpportunity');
            $opportunity   = Opportunity::getById($opportunityId);

            //Retrieve the permission of the opportunity.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($opportunity);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($opportunity->name                       , 'myEditOpportunity');
            $this->assertEquals($opportunity->amount->value              , '288000');
            $this->assertEquals($opportunity->amount->currency->id       , $baseCurrency->id);
            $this->assertEquals($opportunity->account->id                , $accountId);
            $this->assertEquals($opportunity->probability                , '2');
            $this->assertEquals($opportunity->stage->value               , 'Qualification');
            $this->assertEquals($opportunity->source->value              , 'Inbound Call');
            $this->assertEquals($opportunity->description                , 'This is the Edit Description');
            $this->assertEquals($opportunity->owner->id                  , $superUserId);
            $this->assertEquals(1                                        , count($readWritePermitables));
            $this->assertEquals(0                                        , count($readOnlyPermitables));
            $this->assertEquals($opportunity->checkbox                   , '0');
            $this->assertEquals($opportunity->currency->value            , 40);
            $this->assertEquals($opportunity->currency->currency->id     , $baseCurrency->id);
            $this->assertEquals($opportunity->date                       , $dateAssert);
            $this->assertEquals($opportunity->datetime                   , $datetimeAssert);
            $this->assertEquals($opportunity->decimal                    , '12');
            $this->assertEquals($opportunity->picklist->value            , 'b');
            $this->assertEquals($opportunity->integer                    , 11);
            $this->assertEquals($opportunity->phone                      , '259-784-2069');
            $this->assertEquals($opportunity->radio->value               , 'e');
            $this->assertEquals($opportunity->text                       , 'This is a test Edit Text');
            $this->assertEquals($opportunity->textarea                   , 'This is a test Edit TextArea');
            $this->assertEquals($opportunity->url                        , 'http://wwww.abc-edit.com');
            $this->assertEquals($opportunity->date                       , $dateAssert);
            $this->assertEquals($opportunity->datetime                   , $datetimeAssert);
            $this->assertEquals($opportunity->countrypicklist->value     , 'aaaa');
            $this->assertEquals($opportunity->statepicklist->value       , 'aaa1');
            $this->assertEquals($opportunity->citypicklist->value        , 'ab1');
            $this->assertContains('gg'                                   , $opportunity->multiselect->values);
            $this->assertContains('hh'                                   , $opportunity->multiselect->values);
            $this->assertEquals(0                                        , $opportunity->tagcloud->values->count());
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calculatednumber', 'Opportunity');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $opportunity);
            $this->assertEquals(132                                      , $testCalculatedValue);
        }

        /**
         * @depends testEditOfTheOpportunityForTheTagCloudFieldAfterRemovingAllTagsPlacedForOpportunitiesModule
         */
        public function testEditOfTheOpportunityForTheCustomFieldsPlacedForOpportunitiesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Retrieve the account id, the super user id and opportunity Id.
            $accountId                        = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superUserId                      = $super->id;
            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;
            $opportunity                      = Opportunity::getByName('myEditOpportunity');
            $opportunityId                    = $opportunity[0]->id;

            //Edit a new Opportunity based on the custom fields.
            $this->setGetArray(array('id' => $opportunityId));
            $this->setPostArray(array('Opportunity' => array(
                            'name'                              => 'myEditOpportunity',
                            'amount'                            => array('value' => 288000,
                                                                         'currency' => array(
                                                                         'id' => $baseCurrency->id)),
                            'account'                           => array('id' => $accountId),
                            'probability'                       => '2',
                            'closeDate'                         => $date,
                            'stage'                             => array('value' => 'Qualification'),
                            'source'                            => array('value' => 'Inbound Call'),
                            'description'                       => 'This is the Edit Description',
                            'owner'                             => array('id' => $superUserId),
                            'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
                            'checkbox'                          => '0',
                            'currency'                          => array('value'   => 40,
                                                                         'currency' => array(
                                                                         'id' => $baseCurrency->id)),
                            'decimal'                           => '12',
                            'date'                              => $date,
                            'datetime'                          => $datetime,
                            'picklist'                          => array('value'  => 'b'),
                            'multiselect'                       => array('values' =>  array('gg', 'hh')),
                            'tagcloud'                          => array('values' =>  array('reading', 'surfing')),
                            'countrypicklist'                   => array('value'  => 'aaaa'),
                            'statepicklist'                     => array('value'  => 'aaa1'),
                            'citypicklist'                      => array('value'  => 'ab1'),
                            'integer'                           => '11',
                            'phone'                             => '259-784-2069',
                            'radio'                             => array('value' => 'e'),
                            'text'                              => 'This is a test Edit Text',
                            'textarea'                          => 'This is a test Edit TextArea',
                            'url'                               => 'http://wwww.abc-edit.com')));
            $this->runControllerWithRedirectExceptionAndGetUrl('opportunities/default/edit');

            //Check the details if they are saved properly for the custom fields.
            $opportunityId = self::getModelIdByModelNameAndName('Opportunity', 'myEditOpportunity');
            $opportunity   = Opportunity::getById($opportunityId);

            //Retrieve the permission of the opportunity.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($opportunity);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($opportunity->name                       , 'myEditOpportunity');
            $this->assertEquals($opportunity->amount->value              , '288000');
            $this->assertEquals($opportunity->amount->currency->id       , $baseCurrency->id);
            $this->assertEquals($opportunity->account->id                , $accountId);
            $this->assertEquals($opportunity->probability                , '2');
            $this->assertEquals($opportunity->stage->value               , 'Qualification');
            $this->assertEquals($opportunity->source->value              , 'Inbound Call');
            $this->assertEquals($opportunity->description                , 'This is the Edit Description');
            $this->assertEquals($opportunity->owner->id                  , $superUserId);
            $this->assertEquals(1                                        , count($readWritePermitables));
            $this->assertEquals(0                                        , count($readOnlyPermitables));
            $this->assertEquals($opportunity->checkbox                   , '0');
            $this->assertEquals($opportunity->currency->value            , 40);
            $this->assertEquals($opportunity->currency->currency->id     , $baseCurrency->id);
            $this->assertEquals($opportunity->date                       , $dateAssert);
            $this->assertEquals($opportunity->datetime                   , $datetimeAssert);
            $this->assertEquals($opportunity->decimal                    , '12');
            $this->assertEquals($opportunity->picklist->value            , 'b');
            $this->assertEquals($opportunity->integer                    , 11);
            $this->assertEquals($opportunity->phone                      , '259-784-2069');
            $this->assertEquals($opportunity->radio->value               , 'e');
            $this->assertEquals($opportunity->text                       , 'This is a test Edit Text');
            $this->assertEquals($opportunity->textarea                   , 'This is a test Edit TextArea');
            $this->assertEquals($opportunity->url                        , 'http://wwww.abc-edit.com');
            $this->assertEquals($opportunity->date                       , $dateAssert);
            $this->assertEquals($opportunity->datetime                   , $datetimeAssert);
            $this->assertEquals($opportunity->countrypicklist->value     , 'aaaa');
            $this->assertEquals($opportunity->statepicklist->value       , 'aaa1');
            $this->assertEquals($opportunity->citypicklist->value        , 'ab1');
            $this->assertContains('gg'                                   , $opportunity->multiselect->values);
            $this->assertContains('hh'                                   , $opportunity->multiselect->values);
            $this->assertContains('reading'                              , $opportunity->tagcloud->values);
            $this->assertContains('surfing'                              , $opportunity->tagcloud->values);
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calculatednumber', 'Opportunity');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $opportunity);
            $this->assertEquals(132                                      , $testCalculatedValue);
        }

        /**
         * @depends testEditOfTheOpportunityForTheCustomFieldsPlacedForOpportunitiesModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForOpportunitiesModuleAfterEditingTheOpportunity()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the account id, the super user id and opportunity Id.
            $accountId      = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superUserId    = $super->id;
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Search a created Opportunity using the customfields.
            $this->resetPostArray();
            $this->setGetArray(array(
                        'OpportunitiesSearchForm' =>
                            OpportunitiesDesignerWalkthroughHelperUtil::fetchOpportunitiesSearchFormGetData($accountId,
                                                                                      $superUserId, $baseCurrency->id),
                        'ajax'                    =>  'list-view')
            );
            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default');

            //Assert that the edit Opportunity exits after the edit and is diaplayed on the search page.
            $this->assertTrue(strpos($content, "Displaying 1-1 of 1 result(s).") > 0);
            $this->assertTrue(strpos($content, "myEditOpportunity") > 0);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForOpportunitiesModuleAfterEditingTheOpportunity
         */
        public function testDeleteOfTheOpportunityUserForTheCustomFieldsPlacedForOpportunitiesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Get the opportunity id from the recently edited opportunity.
            $opportunityId = self::getModelIdByModelNameAndName('Opportunity', 'myEditOpportunity');

            //Set the opportunity id so as to delete the opportunity.
            $this->setGetArray(array('id' => $opportunityId));
            $this->runControllerWithRedirectExceptionAndGetUrl('opportunities/default/delete');

            //Check wether the opportunity is deleted.
            $opportunity = Opportunity::getByName('myEditOpportunity');
            $this->assertEquals(0, count($opportunity));
        }

        /**
         * @depends testDeleteOfTheOpportunityUserForTheCustomFieldsPlacedForOpportunitiesModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForOpportunitiesModuleAfterDeletingTheOpportunity()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the account id, the super user id and opportunity Id.
            $accountId      = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superUserId    = $super->id;
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Search a created Opportunity using the customfields.
            $this->resetPostArray();
            $this->setGetArray(array(
                        'OpportunitiesSearchForm' =>
                            OpportunitiesDesignerWalkthroughHelperUtil::fetchOpportunitiesSearchFormGetData($accountId,
                                                                                      $superUserId, $baseCurrency->id),
                        'ajax'                    =>  'list-view')
            );
            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default');

            //Assert that the edit Opportunity does not exits after the search.
            $this->assertTrue(strpos($content, "No results found.") > 0);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForOpportunitiesModuleAfterDeletingTheOpportunity
         */
        public function testTypeAheadWorksForTheTagCloudFieldPlacedForOpportunitiesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Search a list item by typing in tag cloud attribute.
            $this->resetPostArray();
            $this->setGetArray(array('name' => 'tagcloud',
                                     'term' => 'rea'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/autoCompleteCustomFieldData');

            //Check if the returned content contains the expected vlaue
            $this->assertTrue(strpos($content, "reading") > 0);
        }

        /**
         * @depends testTypeAheadWorksForTheTagCloudFieldPlacedForOpportunitiesModule
         */
        public function testLabelLocalizationForTheTagCloudFieldPlacedForOpportunitiesModule()
        {
            Yii::app()->user->userModel =  User::getByUsername('super');
            $languageHelper = new ZurmoLanguageHelper();
            $languageHelper->load();
            $this->assertEquals('en', $languageHelper->getForCurrentUser());
            Yii::app()->user->userModel->language = 'fr';
            $this->assertTrue(Yii::app()->user->userModel->save());
            $languageHelper->setActive('fr');
            $this->assertEquals('fr', Yii::app()->user->getState('language'));

            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Search a list item by typing in tag cloud attribute.
            $this->resetPostArray();
            $this->setGetArray(array('name' => 'tagcloud',
                                     'term' => 'surf'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/autoCompleteCustomFieldData');

            //Check if the returned content contains the expected vlaue
            $this->assertTrue(strpos($content, "surfing fr") > 0);
        }
    }
?>