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
    * Designer Module Walkthrough of contacts.
    * Walkthrough for the super user of all possible controller actions.
    * Since this is a super user, he should have access to all controller actions
    * without any exceptions being thrown.
    * This also test the creation of the customfileds, addition of custom fields to all the layouts including the search
    * views.
    * This also test creation, search, edit and delete of the contact based on the custom fields.
    */
    class ContactsDesignerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Currency::makeBaseCurrency();
            //Create a contact for testing.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
        }

        public function testSuperUserContactDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Conatct Modules Menu.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/modulesMenu');

            //Load AttributesList for Conatct module.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');

            //Load ModuleLayoutsList for Conatct module.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');

            //Load ModuleEdit view for each applicable module.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleEdit');

            //Now validate save with failed validation.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'ContactsModuleForm' => $this->createModuleEditBadValidationPostData()));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.

            //Now validate save with successful validation.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'ContactsModuleForm' => $this->createModuleEditGoodValidationPostData('con new name')));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertEquals('[]', $content);

            //Now save successfully.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));
            $this->setPostArray(array('save' => 'Save',
                'ContactsModuleForm' => $this->createModuleEditGoodValidationPostData('con new name')));
            $this->runControllerWithRedirectExceptionAndGetContent('designer/default/moduleEdit');

            //Now confirm everything did in fact save correctly.
            $this->assertEquals('Con New Name',  ContactsModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Con New Names', ContactsModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('con new name',  ContactsModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('con new names', ContactsModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            //Load LayoutEdit for each applicable module and applicable layout
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsModalListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsModalSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsMassEditView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
        }

        /**
         * @depends testSuperUserContactDefaultControllerActions
         */
        public function testSuperUserCustomFieldsWalkthroughForContactsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeCreate');

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('ContactsModule', 'checkbox');
            $this->createCurrencyValueCustomFieldByModule       ('ContactsModule', 'currency');
            $this->createDateCustomFieldByModule                ('ContactsModule', 'date');
            $this->createDateTimeCustomFieldByModule            ('ContactsModule', 'datetime');
            $this->createDecimalCustomFieldByModule             ('ContactsModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('ContactsModule', 'picklist');
            $this->createDependentDropDownCustomFieldByModule   ('ContactsModule', 'countrypicklist');
            $this->createDependentDropDownCustomFieldByModule   ('ContactsModule', 'statepicklist');
            $this->createDependentDropDownCustomFieldByModule   ('ContactsModule', 'citypicklist');
            $this->createIntegerCustomFieldByModule             ('ContactsModule', 'integer');
            $this->createMultiSelectDropDownCustomFieldByModule ('ContactsModule', 'multiselect');
            $this->createTagCloudCustomFieldByModule            ('ContactsModule', 'tagcloud');
            $this->createCalculatedNumberCustomFieldByModule    ('ContactsModule', 'calculatednumber');
            $this->createDropDownDependencyCustomFieldByModule  ('ContactsModule', 'dropdowndependency');
            $this->createPhoneCustomFieldByModule               ('ContactsModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('ContactsModule', 'radio');
            $this->createTextCustomFieldByModule                ('ContactsModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('ContactsModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('ContactsModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForContactsModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForContactsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to ContactEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactEditAndDetailsView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to ContactsSearchView.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsSearchView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactsSearchViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to ContactsListView.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsListView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to ContactsRelatedListView.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsRelatedListView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to ContactsMassEditView.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule',
                                     'viewClassName'   => 'ContactsMassEditView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactsMassEditViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }

        /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForContactsModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForContactsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superContactId = self::getModelIdByModelNameAndName ('Contact', 'superContact superContactson');
            //Load create, edit, and details views.
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/create');
            $this->setGetArray(array('id' => $superContactId));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/edit');
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/list');
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/modalList');
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/massEdit');
        }

        /**
         * @depends testLayoutsLoadOkAfterCustomFieldsPlacedForContactsModule
         */
        public function testSuperUserModifyContactStatesDefaultValueItemsInDropDown()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //test existing ContactState changes to labels.
            $extraPostData = array( 'startingStateOrder'  => '4',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'contactStatesData' => array(
                                                'New', 'In ProgressD', 'RecycledC', 'QualifiedA', 'CustomerF', 'YRE'
                                    ),
                                    'contactStatesDataExistingValues' => array(
                                                'New', 'In Progress', 'Recycled', 'Qualified', 'Customer', 'YRE'
                                    )
                                    );
            $this->createCustomAttributeWalkthroughSequence('ContactsModule', 'state', 'ContactState',
                $extraPostData, 'state');
            $compareData = array(
                'New',
                'In ProgressD',
                'RecycledC',
                'QualifiedA',
                'CustomerF',
                'YRE'
            );
            $this->assertEquals($compareData, ContactsUtil::getContactStateDataKeyedByOrder());

            //todo: test that the changed labels, updated the existing data if any existed.

            //Removing ContactStates items
            $extraPostData = array( 'startingStateOrder'  => '2',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'contactStatesData' => array(
                                                'New', 'RecycledC', 'QualifiedA'
                                    ));
            $this->createCustomAttributeWalkthroughSequence('ContactsModule', 'state', 'ContactState',
                $extraPostData, 'state');
            $compareData = array(
                'New',
                'RecycledC',
                'QualifiedA',
            );
            $this->assertEquals($compareData, ContactsUtil::getContactStateDataKeyedByOrder());

            //Adding ContactStates items
            $extraPostData = array( 'startingStateOrder'  => '2',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'contactStatesData' => array(
                                                'New', 'RecycledC', 'QualifiedA', 'NewItem', 'NewItem2'
                                    ));
            $this->createCustomAttributeWalkthroughSequence('ContactsModule', 'state', 'ContactState',
                $extraPostData, 'state');
            $compareData = array(
                'New',
                'RecycledC',
                'QualifiedA',
                'NewItem',
                'NewItem2'
            );
            $this->assertEquals($compareData, ContactsUtil::getContactStateDataKeyedByOrder());

            //Changing order of ContactStates items
            $extraPostData = array( 'startingStateOrder'  => '2',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'contactStatesData' => array(
                                                'New', 'NewItem2', 'RecycledC', 'QualifiedA', 'NewItem'
                                    ));
            $this->createCustomAttributeWalkthroughSequence('ContactsModule', 'state', 'ContactState',
                $extraPostData, 'state');
            $compareData = array(
                'New',
                'NewItem2',
                'RecycledC',
                'QualifiedA',
                'NewItem',
            );
            $this->assertEquals($compareData, ContactsUtil::getContactStateDataKeyedByOrder());

            //test trying to save 2 ContactStates with the same name (QualifiedA is twice)
            $extraPostData = array( 'startingStateOrder'  => '2',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'contactStatesData' => array(
                                                'New', 'NewItem2', 'QualifiedA', 'QualifiedA', 'NewItem'
                                    ));
            $this->setGetArray(array(   'moduleClassName'       => 'ContactsModule',
                                        'attributeTypeName'     => 'ContactState',
                                        'attributeName'         => 'state'));
            $this->setPostArray(array(   'ajax'                     => 'edit-form',
                                        'ContactStateAttributeForm' => array_merge(array(
                                            'attributeLabels'       => $this->createAttributeLabelGoodValidationPostData('state'),
                                            'attributeName'         => 'state',
                                        ), $extraPostData)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/attributeEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.

            //test trying to save 0 ContactStates
            $extraPostData = array( 'startingStateOrder'  => '2',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'contactStatesData' => array());
            $this->setPostArray(array(   'ajax'                 => 'edit-form',
                                        'ContactStateAttributeForm' => array_merge(array(
                                            'attributeLabels' => $this->createAttributeLabelGoodValidationPostData('state'),
                                            'attributeName'     => 'state',
                                        ), $extraPostData)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/attributeEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.

            //test trying to save contact states that are shorter than the minimum length.
            $extraPostData = array( 'startingStateOrder'  => '2',
                                    'isAudited'           => '1',
                                    'isRequired'          => '1',
                                    'contactStatesData' => array('NA', ' NB', 'NC'));
            $this->setPostArray(array(   'ajax'                 => 'edit-form',
                                        'ContactStateAttributeForm' => array_merge(array(
                                            'attributeLabels' => $this->createAttributeLabelGoodValidationPostData('state'),
                                            'attributeName'     => 'state',
                                        ), $extraPostData)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/attributeEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.
        }

        /**
         * @depends testSuperUserModifyContactStatesDefaultValueItemsInDropDown
         */
        public function testCreateAContactUserAfterTheCustomFieldsArePlacedForContactsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the super account id and the super user id.
            $accountId   = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superUserId = $super->id;

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Retrieve the Contact State (Status) Id based on the name.
            $contactState   = ContactState::getByName('QualifiedA');
            $contactStateId = $contactState[0]->id;

            //Create a new contact based on the custom fields.
            $this->resetGetArray();
            $this->setPostArray(array('Contact' => array(
                                'title'                             => array('value' => 'Mr.'),
                                'firstName'                         => 'Sarah',
                                'lastName'                          => 'Williams',
                                'state'                             => array('id' => $contactStateId),
                                'jobTitle'                          => 'Sales Director',
                                'account'                           => array('id' => $accountId),
                                'department'                        => 'Sales',
                                'officePhone'                       => '739-741-3005',
                                'source'                            => array('value' => 'Self-Generated'),
                                'mobilePhone'                       => '285-301-8232',
                                'officeFax'                         => '255-455-1914',
                                'primaryEmail'                      => array('emailAddress' => 'info@myNewContact.com',
                                                                             'optOut' => '1',
                                                                             'isInvalid' => '0'),
                                'secondaryEmail'                    => array('emailAddress' => '',
                                                                              'optOut' => '0',
                                                                              'isInvalid' => '0'),
                                'primaryAddress'                    => array('street1' => '6466 South Madison Creek',
                                                                              'street2' => '',
                                                                              'city' => 'Chicago',
                                                                              'state' => 'IL',
                                                                              'postalCode' => '60652',
                                                                              'country' => 'USA'),
                                'secondaryAddress'                  => array('street1' => '26217 West Third Lane',
                                                                             'street2' => '',
                                                                             'city' => 'New York',
                                                                             'state' => 'NY',
                                                                             'postalCode' => '10169',
                                                                             'country' => 'USA'),
                                'owner'                             => array('id' => $superUserId),
                                'explicitReadWriteModelPermissions' => array('type' => null),
                                'description'                       => 'This is a Description',
                                'checkbox'                          => '1',
                                'currency'                          => array('value'   => 45,
                                                                             'currency' => array(
                                                                             'id' => $baseCurrency->id)),
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
            $this->runControllerWithRedirectExceptionAndGetUrl('contacts/default/create');

            //Check the details if they are saved properly for the custom fields.
            $contactId     = self::getModelIdByModelNameAndName ('Contact', 'Sarah Williams');
            $contact       = Contact::getById($contactId);
            //Retrieve the permission of the contact.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($contact);
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables  = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($contact->title->value                   , 'Mr.');
            $this->assertEquals($contact->firstName                      , 'Sarah');
            $this->assertEquals($contact->lastName                       , 'Williams');
            $this->assertEquals($contact->state->id                      , $contactStateId);
            $this->assertEquals($contact->jobTitle                       , 'Sales Director');
            $this->assertEquals($contact->account->id                    , $accountId);
            $this->assertEquals($contact->department                     , 'Sales');
            $this->assertEquals($contact->officePhone                    , '739-741-3005');
            $this->assertEquals($contact->source->value                  , 'Self-Generated');
            $this->assertEquals($contact->mobilePhone                    , '285-301-8232');
            $this->assertEquals($contact->officeFax                      , '255-455-1914');
            $this->assertEquals($contact->primaryEmail->emailAddress     , 'info@myNewContact.com');
            $this->assertEquals($contact->primaryEmail->optOut           , '1');
            $this->assertEquals($contact->primaryEmail->isInvalid        , '0');
            $this->assertEquals($contact->secondaryEmail->emailAddress   , '');
            $this->assertEquals($contact->secondaryEmail->optOut         , '0');
            $this->assertEquals($contact->secondaryEmail->isInvalid      , '0');
            $this->assertEquals($contact->primaryAddress->street1        , '6466 South Madison Creek');
            $this->assertEquals($contact->primaryAddress->street2        , '');
            $this->assertEquals($contact->primaryAddress->city           , 'Chicago');
            $this->assertEquals($contact->primaryAddress->state          , 'IL');
            $this->assertEquals($contact->primaryAddress->postalCode     , '60652');
            $this->assertEquals($contact->primaryAddress->country        , 'USA');
            $this->assertEquals($contact->secondaryAddress->street1      , '26217 West Third Lane');
            $this->assertEquals($contact->secondaryAddress->street2      , '');
            $this->assertEquals($contact->secondaryAddress->city         , 'New York');
            $this->assertEquals($contact->secondaryAddress->state        , 'NY');
            $this->assertEquals($contact->secondaryAddress->postalCode   , '10169');
            $this->assertEquals($contact->secondaryAddress->country      , 'USA');
            $this->assertEquals($contact->owner->id                      , $superUserId);
            $this->assertEquals(0                                        , count($readWritePermitables));
            $this->assertEquals(0                                        , count($readOnlyPermitables));
            $this->assertEquals($contact->description                    , 'This is a Description');
            $this->assertEquals($contact->checkbox                       , '1');
            $this->assertEquals($contact->currency->value                , 45);
            $this->assertEquals($contact->currency->currency->id         , $baseCurrency->id);
            $this->assertEquals($contact->date                           , $dateAssert);
            $this->assertEquals($contact->datetime                       , $datetimeAssert);
            $this->assertEquals($contact->decimal                        , '123');
            $this->assertEquals($contact->picklist->value                , 'a');
            $this->assertEquals($contact->integer                        , 12);
            $this->assertEquals($contact->phone                          , '259-784-2169');
            $this->assertEquals($contact->radio->value                   , 'd');
            $this->assertEquals($contact->text                           , 'This is a test Text');
            $this->assertEquals($contact->textarea                       , 'This is a test TextArea');
            $this->assertEquals($contact->url                            , 'http://wwww.abc.com');
            $this->assertEquals($contact->countrypicklist->value         , 'bbbb');
            $this->assertEquals($contact->statepicklist->value           , 'bbb1');
            $this->assertEquals($contact->citypicklist->value            , 'bb1');
            $this->assertContains('ff'                                   , $contact->multiselect->values);
            $this->assertContains('rr'                                   , $contact->multiselect->values);
            $this->assertContains('writing'                              , $contact->tagcloud->values);
            $this->assertContains('gardening'                            , $contact->tagcloud->values);
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calculatednumber', 'Contact');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $contact);
            $this->assertEquals(135                                      , $testCalculatedValue);
        }

        /**
         * @depends testCreateAContactUserAfterTheCustomFieldsArePlacedForContactsModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForContactsModuleAfterCreatingTheContact()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the super account id and the super user id.
            $accountId   = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superUserId = $super->id;

            //Retrieve the Contact State (Status) Id based on the name.
            $contactState   = ContactState::getByName('QualifiedA');
            $contactStateId = $contactState[0]->id;

            //Search a created contact using the customfields.
            $this->resetPostArray();
            $this->setGetArray(array('ContactsSearchForm' => array(
                                        'fullName'           => 'Sarah Williams',
                                        'officePhone'        => '739-741-3005',
                                        'anyPostalCode'      => '10169',
                                        'anyCountry'         => 'USA',
                                        'anyInvalidEmail'    => array('value' => '0'),
                                        'anyEmail'           => 'info@myNewContact.com',
                                        'anyOptOutEmail'     => array('value' => '1'),
                                        'ownedItemsOnly'     => '1',
                                        'anyStreet'          => '26217 West Third Lane',
                                        'anyCity'            => 'New York',
                                        'anyState'           => 'NY',
                                        'state'              => array('id' => $contactStateId),
                                        'owner'              => array('id' => $superUserId),
                                        'firstName'          => 'Sarah',
                                        'lastName'           => 'Williams',
                                        'jobTitle'           => 'Sales Director',
                                        'officeFax'          => '255-455-1914',
                                        'title'              => array('value' => 'Mr.'),
                                        'source'             => array('value' => 'Self-Generated'),
                                        'account'            => array('id' => $accountId),
                                        'decimal'            => '123',
                                        'integer'            => '12',
                                        'phone'              => '259-784-2169',
                                        'text'               => 'This is a test Text',
                                        'textarea'           => 'This is a test TextArea',
                                        'url'                => 'http://wwww.abc.com',
                                        'checkbox'           => array('value'  => '1'),
                                        'currency'           => array('value'  => 45),
                                        'picklist'           => array('value'  => 'a'),
                                        'multiselect'        => array('values' => array('ff', 'rr')),
                                        'tagcloud'           => array('values' => array('writing', 'gardening')),
                                        'countrypicklist'    => array('value'  => 'bbbb'),
                                        'statepicklist'      => array('value'  => 'bbb1'),
                                        'citypicklist'       => array('value'  => 'bb1'),
                                        'radio'              => array('value'  => 'd'),
                                        'date__Date'         => array('type'   => 'Today'),
                                        'datetime__DateTime' => array('type'   => 'Today')),
                                    'ajax' =>  'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default');

            //Check if the contact name exits after the search is performed on the basis of the
            //custom fields added to the contacts module.
            $this->assertTrue(strpos($content, "Displaying 1-1 of 1 result(s).") > 0);
            $this->assertTrue(strpos($content, "Sarah Williams") > 0);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForContactsModuleAfterCreatingTheContact
         */
        public function testEditOfTheContactForTheTagCloudFieldAfterRemovingAllTagsPlacedForContactsModule()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;

            //Retrieve the super account id and the super user id.
            $accountId   = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superUserId = $super->id;

            //Retrieve the contact id.
            $contact   = Contact::getByName('Sarah Williams');
            $contactId = $contact[0]->id;
            $this->assertEquals(2, $contact[0]->tagcloud->values->count());

            //Retrieve the Contact State (Status) Id based on the name.
            $contactState   = ContactState::getByName('RecycledC');
            $contactStateId = $contactState[0]->id;

            //Edit and save the contact.
            $this->setGetArray(array('id' => $contactId));
            $this->setPostArray(array('Contact' => array(
                            'title'                             => array('value' => 'Mrs.'),
                            'firstName'                         => 'Sarah',
                            'lastName'                          => 'Williams Edit',
                            'jobTitle'                          => 'Sales Director Edit',
                            'department'                        => 'Sales Edit',
                            'officePhone'                       => '739-742-3005',
                            'source'                            => array('value' => 'Inbound Call'),
                            'mobilePhone'                       => '285-300-8232',
                            'officeFax'                         => '255-454-1914',
                            'state'                             => array('id' => $contactStateId),
                            'owner'                             => array('id' => $superUserId),
                            'account'                           => array('id' => $accountId),
                            'primaryEmail'                      => array('emailAddress' => 'info@myNewContact.com',
                                                                         'optOut' => '0',
                                                                         'isInvalid' => '0'),
                            'secondaryEmail'                    => array('emailAddress' => 'info@myNewContactEdit.com',
                                                                         'optOut' => '0',
                                                                         'isInvalid' => '0'),
                            'primaryAddress'                    => array('street1' => '26378 South Arlington Ave',
                                                                         'street2' => '',
                                                                         'city' => 'San Jose',
                                                                         'state' => 'CA',
                                                                         'postalCode' => '95131',
                                                                         'country' => 'USA'),
                            'secondaryAddress'                  => array('street1' => '18693 West Spring Center',
                                                                         'street2' => '',
                                                                         'city' => 'Philadelphia',
                                                                         'state' => 'PA',
                                                                         'postalCode' => '19102',
                                                                         'country' => 'USA'),
                            'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
                            'description'                       => 'This is a Edit Description',
                            'checkbox'                          => '0',
                            'currency'                          => array('value'    => 40,
                                                                         'currency' => array(
                                                                         'id' => $baseCurrency->id)),
                            'date'                              => $date,
                            'datetime'                          => $datetime,
                            'decimal'                           => '12',
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
                            'url'                               => 'http://wwww.abc-edit.com'),
                                'save' => 'Save'));
            $this->runControllerWithRedirectExceptionAndGetUrl('contacts/default/edit');

            //Check the details if they are saved properly for the custom fields after the edit.
            $contact  = Contact::getById($contactId);
            //Retrieve the permission of the contact.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($contact);
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables  = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($contact->title->value                   , 'Mrs.');
            $this->assertEquals($contact->firstName                      , 'Sarah');
            $this->assertEquals($contact->lastName                       , 'Williams Edit');
            $this->assertEquals($contact->state->id                      , $contactStateId);
            $this->assertEquals($contact->jobTitle                       , 'Sales Director Edit');
            $this->assertEquals($contact->department                     , 'Sales Edit');
            $this->assertEquals($contact->officePhone                    , '739-742-3005');
            $this->assertEquals($contact->source->value                  , 'Inbound Call');
            $this->assertEquals($contact->mobilePhone                    , '285-300-8232');
            $this->assertEquals($contact->officeFax                      , '255-454-1914');
            $this->assertEquals($contact->primaryEmail->emailAddress     , 'info@myNewContact.com');
            $this->assertEquals($contact->primaryEmail->optOut           , '0');
            $this->assertEquals($contact->primaryEmail->isInvalid        , '0');
            $this->assertEquals($contact->secondaryEmail->emailAddress   , 'info@myNewContactEdit.com');
            $this->assertEquals($contact->secondaryEmail->optOut         , '0');
            $this->assertEquals($contact->secondaryEmail->isInvalid      , '0');
            $this->assertEquals($contact->primaryAddress->street1        , '26378 South Arlington Ave');
            $this->assertEquals($contact->primaryAddress->street2        , '');
            $this->assertEquals($contact->primaryAddress->city           , 'San Jose');
            $this->assertEquals($contact->primaryAddress->state          , 'CA');
            $this->assertEquals($contact->primaryAddress->postalCode     , '95131');
            $this->assertEquals($contact->primaryAddress->country        , 'USA');
            $this->assertEquals($contact->secondaryAddress->street1      , '18693 West Spring Center');
            $this->assertEquals($contact->secondaryAddress->street2      , '');
            $this->assertEquals($contact->secondaryAddress->city         , 'Philadelphia');
            $this->assertEquals($contact->secondaryAddress->state        , 'PA');
            $this->assertEquals($contact->secondaryAddress->postalCode   , '19102');
            $this->assertEquals($contact->secondaryAddress->country      , 'USA');
            $this->assertEquals(1                                        , count($readWritePermitables));
            $this->assertEquals(0                                        , count($readOnlyPermitables));
            $this->assertEquals($contact->description                    , 'This is a Edit Description');
            $this->assertEquals($contact->checkbox                       , '0');
            $this->assertEquals($contact->currency->value                ,  40);
            $this->assertEquals($contact->currency->currency->id         , $baseCurrency->id);
            $this->assertEquals($contact->date                           , $dateAssert);
            $this->assertEquals($contact->datetime                       , $datetimeAssert);
            $this->assertEquals($contact->decimal                        , '12');
            $this->assertEquals($contact->picklist->value                , 'b');
            $this->assertEquals($contact->integer                        ,  11);
            $this->assertEquals($contact->phone                          , '259-784-2069');
            $this->assertEquals($contact->radio->value                   , 'e');
            $this->assertEquals($contact->text                           , 'This is a test Edit Text');
            $this->assertEquals($contact->textarea                       , 'This is a test Edit TextArea');
            $this->assertEquals($contact->url                            , 'http://wwww.abc-edit.com');
            $this->assertEquals($contact->countrypicklist->value         , 'aaaa');
            $this->assertEquals($contact->statepicklist->value           , 'aaa1');
            $this->assertEquals($contact->citypicklist->value            , 'ab1');
            $this->assertContains('gg'                                   , $contact->multiselect->values);
            $this->assertContains('hh'                                   , $contact->multiselect->values);
            $this->assertEquals(0                                        , $contact->tagcloud->values->count());
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calculatednumber', 'Contact');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $contact);
            $this->assertEquals(23                                       , $testCalculatedValue);
        }

        /**
         * @depends testEditOfTheContactForTheTagCloudFieldAfterRemovingAllTagsPlacedForContactsModule
         */
        public function testEditOfTheContactForTheCustomFieldsPlacedForContactsModule()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;

            //Retrieve the super account id and the super user id.
            $accountId   = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superUserId = $super->id;

            //Retrieve the contact id.
            $contact     = Contact::getByName ('Sarah Williams');
            $contactId   = $contact[0]->id;

            //Retrieve the Contact State (Status) Id based on the name.
            $contactState   = ContactState::getByName('RecycledC');
            $contactStateId = $contactState[0]->id;

            //Edit and save the contact.
            $this->setGetArray(array('id' => $contactId));
            $this->setPostArray(array('Contact' => array(
                            'title'                             => array('value' => 'Mrs.'),
                            'firstName'                         => 'Sarah',
                            'lastName'                          => 'Williams Edit',
                            'jobTitle'                          => 'Sales Director Edit',
                            'department'                        => 'Sales Edit',
                            'officePhone'                       => '739-742-3005',
                            'source'                            => array('value' => 'Inbound Call'),
                            'mobilePhone'                       => '285-300-8232',
                            'officeFax'                         => '255-454-1914',
                            'state'                             => array('id' => $contactStateId),
                            'owner'                             => array('id' => $superUserId),
                            'account'                           => array('id' => $accountId),
                            'primaryEmail'                      => array('emailAddress' => 'info@myNewContact.com',
                                                                         'optOut' => '0',
                                                                         'isInvalid' => '0'),
                            'secondaryEmail'                    => array('emailAddress' => 'info@myNewContactEdit.com',
                                                                         'optOut' => '0',
                                                                         'isInvalid' => '0'),
                            'primaryAddress'                    => array('street1' => '26378 South Arlington Ave',
                                                                         'street2' => '',
                                                                         'city' => 'San Jose',
                                                                         'state' => 'CA',
                                                                         'postalCode' => '95131',
                                                                         'country' => 'USA'),
                            'secondaryAddress'                  => array('street1' => '18693 West Spring Center',
                                                                         'street2' => '',
                                                                         'city' => 'Philadelphia',
                                                                         'state' => 'PA',
                                                                         'postalCode' => '19102',
                                                                         'country' => 'USA'),
                            'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
                            'description'                       => 'This is a Edit Description',
                            'checkbox'                          => '0',
                            'currency'                          => array('value'    => 40,
                                                                         'currency' => array(
                                                                         'id' => $baseCurrency->id)),
                            'date'                              => $date,
                            'datetime'                          => $datetime,
                            'decimal'                           => '12',
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
                            'url'                               => 'http://wwww.abc-edit.com'),
                                'save' => 'Save'));
            $this->runControllerWithRedirectExceptionAndGetUrl('contacts/default/edit');

            //Check the details if they are saved properly for the custom fields after the edit.
            $contact  = Contact::getById($contactId);
            //Retrieve the permission of the contact.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($contact);
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables  = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($contact->title->value                   , 'Mrs.');
            $this->assertEquals($contact->firstName                      , 'Sarah');
            $this->assertEquals($contact->lastName                       , 'Williams Edit');
            $this->assertEquals($contact->state->id                      , $contactStateId);
            $this->assertEquals($contact->jobTitle                       , 'Sales Director Edit');
            $this->assertEquals($contact->department                     , 'Sales Edit');
            $this->assertEquals($contact->officePhone                    , '739-742-3005');
            $this->assertEquals($contact->source->value                  , 'Inbound Call');
            $this->assertEquals($contact->mobilePhone                    , '285-300-8232');
            $this->assertEquals($contact->officeFax                      , '255-454-1914');
            $this->assertEquals($contact->primaryEmail->emailAddress     , 'info@myNewContact.com');
            $this->assertEquals($contact->primaryEmail->optOut           , '0');
            $this->assertEquals($contact->primaryEmail->isInvalid        , '0');
            $this->assertEquals($contact->secondaryEmail->emailAddress   , 'info@myNewContactEdit.com');
            $this->assertEquals($contact->secondaryEmail->optOut         , '0');
            $this->assertEquals($contact->secondaryEmail->isInvalid      , '0');
            $this->assertEquals($contact->primaryAddress->street1        , '26378 South Arlington Ave');
            $this->assertEquals($contact->primaryAddress->street2        , '');
            $this->assertEquals($contact->primaryAddress->city           , 'San Jose');
            $this->assertEquals($contact->primaryAddress->state          , 'CA');
            $this->assertEquals($contact->primaryAddress->postalCode     , '95131');
            $this->assertEquals($contact->primaryAddress->country        , 'USA');
            $this->assertEquals($contact->secondaryAddress->street1      , '18693 West Spring Center');
            $this->assertEquals($contact->secondaryAddress->street2      , '');
            $this->assertEquals($contact->secondaryAddress->city         , 'Philadelphia');
            $this->assertEquals($contact->secondaryAddress->state        , 'PA');
            $this->assertEquals($contact->secondaryAddress->postalCode   , '19102');
            $this->assertEquals($contact->secondaryAddress->country      , 'USA');
            $this->assertEquals(1                                        , count($readWritePermitables));
            $this->assertEquals(0                                        , count($readOnlyPermitables));
            $this->assertEquals($contact->description                    , 'This is a Edit Description');
            $this->assertEquals($contact->checkbox                       , '0');
            $this->assertEquals($contact->currency->value                ,  40);
            $this->assertEquals($contact->currency->currency->id         , $baseCurrency->id);
            $this->assertEquals($contact->date                           , $dateAssert);
            $this->assertEquals($contact->datetime                       , $datetimeAssert);
            $this->assertEquals($contact->decimal                        , '12');
            $this->assertEquals($contact->picklist->value                , 'b');
            $this->assertEquals($contact->integer                        ,  11);
            $this->assertEquals($contact->phone                          , '259-784-2069');
            $this->assertEquals($contact->radio->value                   , 'e');
            $this->assertEquals($contact->text                           , 'This is a test Edit Text');
            $this->assertEquals($contact->textarea                       , 'This is a test Edit TextArea');
            $this->assertEquals($contact->url                            , 'http://wwww.abc-edit.com');
            $this->assertEquals($contact->countrypicklist->value         , 'aaaa');
            $this->assertEquals($contact->statepicklist->value           , 'aaa1');
            $this->assertEquals($contact->citypicklist->value            , 'ab1');
            $this->assertContains('gg'                                   , $contact->multiselect->values);
            $this->assertContains('hh'                                   , $contact->multiselect->values);
            $this->assertContains('reading'                              , $contact->tagcloud->values);
            $this->assertContains('surfing'                              , $contact->tagcloud->values);
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calculatednumber', 'Contact');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $contact);
            $this->assertEquals(23                                       , $testCalculatedValue);
        }

        /**
         * @depends testEditOfTheContactForTheCustomFieldsPlacedForContactsModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForContactsModuleAfterEditingTheContact()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the super account id and the super user id.
            $accountId   = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superUserId = $super->id;

            //Retrieve the Contact State (Status) Id based on the name.
            $contactState   = ContactState::getByName('RecycledC');
            $contactStateId = $contactState[0]->id;

            //Search a created contact using the customfields.
            $this->resetPostArray();
            $this->setGetArray(array(
                    'ContactsSearchForm' => ContactsDesignerWalkthroughHelperUtil::fetchContactsSearchFormGetData(
                                                                         $contactStateId, $superUserId, $accountId),
                    'ajax'               => 'list-view')
            );
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default');

            //Check if the contact name exits after the search is performed on the basis of the
            //custom fields added to the contacts module.
            $this->assertTrue(strpos($content, "Displaying 1-1 of 1 result(s).") > 0);
            $this->assertTrue(strpos($content, "Sarah Williams Edit") > 0);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForContactsModuleAfterEditingTheContact
         */
        public function testDeleteOfTheContactForTheCustomFieldsPlacedForContactsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the contact id from the recently edited contact.
            $contactId     = self::getModelIdByModelNameAndName ('Contact', 'Sarah Williams Edit');

            //Set the contact id so as to delete the contact.
            $this->setGetArray(array('id' => $contactId));
            $this->runControllerWithRedirectExceptionAndGetUrl('contacts/default/delete');

            //Check whether the contact is deleted.
            $contact     = Contact::getByName('Sarah Williams Edit');
            $this->assertEquals(0, count($contact));
        }

        /**
         * @depends testDeleteOfTheContactForTheCustomFieldsPlacedForContactsModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForContactsModuleAfterDeletingTheContact()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the super account id and the super user id.
            $accountId   = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superUserId = $super->id;

            //Retrieve the Contact State (Status) Id based on the name.
            $contactState   = ContactState::getByName('RecycledC');
            $contactStateId = $contactState[0]->id;

            //Search a created contact using the customfields.
            $this->resetPostArray();
            $this->setGetArray(array(
                    'ContactsSearchForm' => ContactsDesignerWalkthroughHelperUtil::fetchContactsSearchFormGetData(
                                                                         $contactStateId, $superUserId, $accountId),
                    'ajax'               => 'list-view')
            );
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default');

            //Assert that the edit contact does not exits after the search.
            $this->assertTrue(strpos($content, "No results found.") > 0);
            $this->assertFalse(strpos($content, "26378 South Arlington Ave") > 0);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForContactsModuleAfterDeletingTheContact
         */
        public function testTypeAheadWorksForTheTagCloudFieldPlacedForContactsModule()
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
         * @depends testTypeAheadWorksForTheTagCloudFieldPlacedForContactsModule
         */
        public function testLabelLocalizationForTheTagCloudFieldPlacedForContactsModule()
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