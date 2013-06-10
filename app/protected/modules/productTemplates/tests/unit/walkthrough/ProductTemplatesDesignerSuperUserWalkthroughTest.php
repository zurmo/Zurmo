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
    * Designer Module Walkthrough of productTemplates.
    * Walkthrough for the super user of all possible controller actions.
    * Since this is a super user, he should have access to all controller actions
    * without any exceptions being thrown.
    * This also test the creation of the customfileds, addition of custom fields to all the layouts including the search
    * views.
    * This also test creation search, edit and delete of the ProductTemplate based on the custom fields.
    */
    class ProductTemplatesDesignerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static $activateDefaultLanguages = true;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Currency::makeBaseCurrency();

            //Create a Product Template for testing.
            ProductTemplateTestHelper::createProductTemplateByName('superProductTemplate', $super);
        }

         public function testSuperUserProductTemplateDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Product Modules Menu.
            $this->setGetArray(array('moduleClassName' => 'ProductTemplatesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/modulesMenu');

            //Load AttributesList for ProductTemplate module.
            $this->setGetArray(array('moduleClassName' => 'ProductTemplatesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');

            //Load ModuleLayoutsList for ProductTemplate module.
            $this->setGetArray(array('moduleClassName' => 'ProductTemplatesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');

            //Now confirm everything did in fact save correctly.
            $this->assertEquals('Catalog Item',  ProductTemplatesModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Catalog Items', ProductTemplatesModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('catalog item',  ProductTemplatesModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('catalog items', ProductTemplatesModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            //Load LayoutEdit for each applicable module and applicable layout
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'ProductTemplatesModule',
                                     'viewClassName'   => 'ProductTemplatesListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ProductTemplatesModule',
                                     'viewClassName'   => 'ProductTemplatesModalListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ProductTemplatesModule',
                                     'viewClassName'   => 'ProductTemplatesModalSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ProductTemplatesModule',
                                     'viewClassName'   => 'ProductTemplateEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
        }

        /**
         * @depends testSuperUserProductTemplateDefaultControllerActions
         */
        public function testSuperUserCustomFieldsWalkthroughForProductTemplatesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->setGetArray(array('moduleClassName' => 'ProductTemplatesModule'));

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('ProductTemplatesModule', 'checkbox');
            $this->createCurrencyValueCustomFieldByModule       ('ProductTemplatesModule', 'currency');
            $this->createDateCustomFieldByModule                ('ProductTemplatesModule', 'date');
            $this->createDateTimeCustomFieldByModule            ('ProductTemplatesModule', 'datetime');
            $this->createDecimalCustomFieldByModule             ('ProductTemplatesModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('ProductTemplatesModule', 'picklist');
            $this->createDependentDropDownCustomFieldByModule   ('ProductTemplatesModule', 'countrylist');
            $this->createDependentDropDownCustomFieldByModule   ('ProductTemplatesModule', 'statelist');
            $this->createDependentDropDownCustomFieldByModule   ('ProductTemplatesModule', 'citylist');
            $this->createIntegerCustomFieldByModule             ('ProductTemplatesModule', 'integer');
            $this->createMultiSelectDropDownCustomFieldByModule ('ProductTemplatesModule', 'multiselect');
            $this->createTagCloudCustomFieldByModule            ('ProductTemplatesModule', 'tagcloud');
            $this->createCalculatedNumberCustomFieldByModule    ('ProductTemplatesModule', 'calcnumber');
            $this->createDropDownDependencyCustomFieldByModule  ('ProductTemplatesModule', 'dropdowndep');
            $this->createPhoneCustomFieldByModule               ('ProductTemplatesModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('ProductTemplatesModule', 'radio');
            $this->createTextCustomFieldByModule                ('ProductTemplatesModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('ProductTemplatesModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('ProductTemplatesModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForProductTemplatesModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForProductTemplatesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to ProductEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'ProductTemplatesModule',
                                     'viewClassName'   => 'ProductTemplateEditAndDetailsView'));
            $layout = ProductTemplatesDesignerWalkthroughHelperUtil::getProductTemplateEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to ProductTemplatesSearchView.
            $this->setGetArray(array('moduleClassName' => 'ProductTemplatesModule',
                                     'viewClassName'   => 'ProductTemplatesSearchView'));
            $layout = ProductTemplatesDesignerWalkthroughHelperUtil::getProductTemplatesSearchViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to ProductTemplatesListView.
            $this->setGetArray(array('moduleClassName' => 'ProductTemplatesModule',
                                     'viewClassName'   => 'ProductTemplatesListView'));
            $layout = ProductTemplatesDesignerWalkthroughHelperUtil::getProductTemplatesListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }

        /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForProductTemplatesModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForProductTemplatesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superProductTemplateId = self::getModelIdByModelNameAndName ('ProductTemplate', 'superProductTemplate');
            //Load create, edit, and details views.
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/create');
            $this->setGetArray(array('id' => $superProductTemplateId));
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/edit');
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/list');
        }

        /**
         * @depends testLayoutsLoadOkAfterCustomFieldsPlacedForProductTemplatesModule
         */
        public function testCreateAProductTemplateAfterTheCustomFieldsArePlacedForProductTemplatesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Create a new product based on the custom fields.
            $this->resetGetArray();
            $this->setPostArray(array('ProductTemplate' => array(
                            'name'                              => 'myNewProductTemplate',
                            'type'                              => ProductTemplate::TYPE_PRODUCT,
                            'description'                       => 'Test Description',
                            'sellPrice'                         => array ('currency' => array ('id' => $baseCurrency->id), 'value' => 200),
                            'cost'                              => array ('currency' => array ('id' => $baseCurrency->id), 'value' => 200),
                            'listPrice'                         => array ('currency' => array ('id' => $baseCurrency->id), 'value' => 200),
                            'priceFrequency'                    => 2,
                            'status'                            => ProductTemplate::STATUS_ACTIVE,
                            'sellPriceFormula'                  => array ( 'type' => SellPriceFormula::TYPE_EDITABLE ),
                            'checkboxCstm'                      => '1',
                            'currencyCstm'                      => array('value'    => 45,
                                                                         'currency' => array('id' => $baseCurrency->id)),
                            'dateCstm'                          => $date,
                            'datetimeCstm'                      => $datetime,
                            'decimalCstm'                       => '123',
                            'picklistCstm'                      => array('value' => 'a'),
                            'multiselectCstm'                   => array('values' => array('ff', 'rr')),
                            'tagcloudCstm'                      => array('values' => array('writing', 'gardening')),
                            'countrylistCstm'                   => array('value'  => 'bbbb'),
                            'statelistCstm'                     => array('value'  => 'bbb1'),
                            'citylistCstm'                      => array('value'  => 'bb1'),
                            'integerCstm'                       => '12',
                            'phoneCstm'                         => '259-784-2169',
                            'radioCstm'                         => array('value' => 'd'),
                            'textCstm'                          => 'This is a test Text',
                            'textareaCstm'                      => 'This is a test TextArea',
                            'urlCstm'                           => 'http://wwww.abc.com'
                            )));
            $this->runControllerWithRedirectExceptionAndGetUrl('productTemplates/default/create');

            //Check the details if they are saved properly for the custom fields.
            $productTemplateId = self::getModelIdByModelNameAndName('ProductTemplate', 'myNewProductTemplate');
            $productTemplate   = ProductTemplate::getById($productTemplateId);

            $this->assertEquals($productTemplate->name                       , 'myNewProductTemplate');
            $this->assertEquals($productTemplate->sellPrice->value           , 200.00);
            $this->assertEquals($productTemplate->cost->value                , 200.00);
            $this->assertEquals($productTemplate->listPrice->value           , 200.00);
            $this->assertEquals($productTemplate->description                , 'Test Description');
            $this->assertEquals($productTemplate->type                       , ProductTemplate::TYPE_PRODUCT);
            $this->assertEquals($productTemplate->status                     , ProductTemplate::STATUS_ACTIVE);
            $this->assertEquals($productTemplate->priceFrequency             , 2);
            $this->assertEquals($productTemplate->sellPriceFormula->type     , SellPriceFormula::TYPE_EDITABLE);
            $this->assertEquals($productTemplate->checkboxCstm               , '1');
            $this->assertEquals($productTemplate->currencyCstm->value        , 45);
            $this->assertEquals($productTemplate->currencyCstm->currency->id , $baseCurrency->id);
            $this->assertEquals($productTemplate->dateCstm                   , $dateAssert);
            $this->assertEquals($productTemplate->datetimeCstm               , $datetimeAssert);
            $this->assertEquals($productTemplate->decimalCstm                , '123');
            $this->assertEquals($productTemplate->picklistCstm->value        , 'a');
            $this->assertEquals($productTemplate->integerCstm                , 12);
            $this->assertEquals($productTemplate->phoneCstm                  , '259-784-2169');
            $this->assertEquals($productTemplate->radioCstm->value           , 'd');
            $this->assertEquals($productTemplate->textCstm                   , 'This is a test Text');
            $this->assertEquals($productTemplate->textareaCstm               , 'This is a test TextArea');
            $this->assertEquals($productTemplate->urlCstm                    , 'http://wwww.abc.com');
            $this->assertEquals($productTemplate->countrylistCstm->value     , 'bbbb');
            $this->assertEquals($productTemplate->statelistCstm->value       , 'bbb1');
            $this->assertEquals($productTemplate->citylistCstm->value        , 'bb1');
            $this->assertContains('ff'                                       , $productTemplate->multiselectCstm->values);
            $this->assertContains('rr'                                       , $productTemplate->multiselectCstm->values);
            $this->assertContains('writing'                                  , $productTemplate->tagcloudCstm->values);
            $this->assertContains('gardening'                                , $productTemplate->tagcloudCstm->values);
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calcnumber', 'ProductTemplate');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat($metadata->getFormula(), $productTemplate);
            $this->assertEquals(1476                                     , intval(str_replace(',', "", $testCalculatedValue))); // Not Coding Standard
        }

        /**
         * @depends testCreateAProductTemplateAfterTheCustomFieldsArePlacedForProductTemplatesModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForProductTemplatesModuleAfterCreatingTheProductTemplate()
        {
            $super              = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $baseCurrency       = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
            //Search a created product using the customfield.
            $this->resetPostArray();
            $this->setGetArray(array('ProductTemplatesSearchForm' => array(
                                                'name'                      => 'myNewProductTemplate',
                                                'type'                      => ProductTemplate::TYPE_PRODUCT,
                                                'description'               => 'Test Description',
                                                'sellPrice'                 => array ('value' => 200),
                                                'cost'                      => array ('value' => 200),
                                                'listPrice'                 => array ('value' => 200),
                                                'priceFrequency'            => 2,
                                                'status'                    => ProductTemplate::STATUS_ACTIVE,
                                                'sellPriceFormula'          => array ( 'type' => SellPriceFormula::TYPE_EDITABLE ),
                                                'decimalCstm'               => '123',
                                                'integerCstm'               => '12',
                                                'phoneCstm'                 => '259-784-2169',
                                                'textCstm'                  => 'This is a test Text',
                                                'textareaCstm'              => 'This is a test TextArea',
                                                'urlCstm'                   => 'http://wwww.abc.com',
                                                'checkboxCstm'              => array('value'  =>  '1'),
                                                'currencyCstm'              => array('value'  =>  45),
                                                'picklistCstm'              => array('value'  =>  'a'),
                                                'multiselectCstm'           => array('values' => array('ff', 'rr')),
                                                'tagcloudCstm'              => array('values' => array('writing', 'gardening')),
                                                'countrylistCstm'           => array('value'  => 'bbbb'),
                                                'statelistCstm'             => array('value'  => 'bbb1'),
                                                'citylistCstm'              => array('value'  => 'bb1'),
                                                'radioCstm'                 => array('value'  =>  'd'),
                                                'dateCstm__Date'            => array('type'   =>  'Today'),
                                                'datetimeCstm__DateTime'    => array('type'   =>  'Today')),
                                                'ajax'                      =>  'list-view'));
            //TODO Ask Jason
            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default');
            $this->assertTrue(strpos($content, "myNewProductTemplate") > 0);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForProductTemplatesModuleAfterCreatingTheProductTemplate
         */
        public function testEditOfTheProductTemplateForTheTagCloudFieldAfterRemovingAllTagsPlacedForProductTemplatesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date               = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert         = date('Y-m-d');
            $datetime           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert     = date('Y-m-d H:i:')."00";
            $baseCurrency       = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            $superUserId        = $super->id;
            $productTemplate    = ProductTemplate::getByName('myNewProductTemplate');
            $productTemplateId  = $productTemplate[0]->id;
            $this->assertEquals(2, $productTemplate[0]->tagcloudCstm->values->count());

            //Edit a new ProductTemplate based on the custom fields.
            $this->setGetArray(array('id' => $productTemplateId));
            $this->setPostArray(array('ProductTemplate' => array(
                            'name'                              => 'myEditProductTemplate',
                            'type'                              => ProductTemplate::TYPE_PRODUCT,
                            'description'                       => 'Test Description',
                            'sellPrice'                         => array ('currency' => array ('id' => $baseCurrency->id), 'value' => 200 ),
                            'cost'                              => array ('currency' => array ('id' => $baseCurrency->id), 'value' => 200 ),
                            'listPrice'                         => array ('currency' => array ('id' => $baseCurrency->id), 'value' => 200 ),
                            'priceFrequency'                    => 2,
                            'status'                            => ProductTemplate::STATUS_ACTIVE,
                            'sellPriceFormula'                  => array ( 'type' => SellPriceFormula::TYPE_EDITABLE ),
                            'checkboxCstm'                      => '0',
                            'currencyCstm'                      => array('value'       => 40,
                                                                         'currency'    => array(
                                                                             'id' => $baseCurrency->id)),
                            'decimalCstm'                       => '12',
                            'dateCstm'                          => $date,
                            'datetimeCstm'                      => $datetime,
                            'picklistCstm'                      => array('value'  => 'b'),
                            'multiselectCstm'                   => array('values' =>  array('gg', 'hh')),
                            'tagcloudCstm'                      => array('values' =>  array()),
                            'countrylistCstm'                   => array('value'  => 'aaaa'),
                            'statelistCstm'                     => array('value'  => 'aaa1'),
                            'citylistCstm'                      => array('value'  => 'ab1'),
                            'integerCstm'                       => '11',
                            'phoneCstm'                         => '259-784-2069',
                            'radioCstm'                         => array('value' => 'e'),
                            'textCstm'                          => 'This is a test Edit Text',
                            'textareaCstm'                      => 'This is a test Edit TextArea',
                            'urlCstm'                           => 'http://wwww.abc-edit.com')));
            $this->runControllerWithRedirectExceptionAndGetUrl('productTemplates/default/edit');

            //Check the details if they are saved properly for the custom fields.
            $productTemplateId = self::getModelIdByModelNameAndName('ProductTemplate', 'myEditProductTemplate');
            $productTemplate   = ProductTemplate::getById($productTemplateId);

            $this->assertEquals($productTemplate->name                       , 'myEditProductTemplate');
            $this->assertEquals($productTemplate->sellPrice->value           , 200.00);
            $this->assertEquals($productTemplate->cost->value                , 200.00);
            $this->assertEquals($productTemplate->listPrice->value           , 200.00);
            $this->assertEquals($productTemplate->description                , 'Test Description');
            $this->assertEquals($productTemplate->type                       , ProductTemplate::TYPE_PRODUCT);
            $this->assertEquals($productTemplate->status                     , ProductTemplate::STATUS_ACTIVE);
            $this->assertEquals($productTemplate->priceFrequency             , 2);
            $this->assertEquals($productTemplate->sellPriceFormula->type     , SellPriceFormula::TYPE_EDITABLE);
            $this->assertEquals($productTemplate->checkboxCstm               , '0');
            $this->assertEquals($productTemplate->currencyCstm->value        , 40);
            $this->assertEquals($productTemplate->currencyCstm->currency->id , $baseCurrency->id);
            $this->assertEquals($productTemplate->dateCstm                   , $dateAssert);
            $this->assertEquals($productTemplate->datetimeCstm               , $datetimeAssert);
            $this->assertEquals($productTemplate->decimalCstm                , '12');
            $this->assertEquals($productTemplate->picklistCstm->value        , 'b');
            $this->assertEquals($productTemplate->integerCstm                , 11);
            $this->assertEquals($productTemplate->phoneCstm                  , '259-784-2069');
            $this->assertEquals($productTemplate->radioCstm->value           , 'e');
            $this->assertEquals($productTemplate->textCstm                   , 'This is a test Edit Text');
            $this->assertEquals($productTemplate->textareaCstm               , 'This is a test Edit TextArea');
            $this->assertEquals($productTemplate->urlCstm                    , 'http://wwww.abc-edit.com');
            $this->assertEquals($productTemplate->dateCstm                   , $dateAssert);
            $this->assertEquals($productTemplate->datetimeCstm               , $datetimeAssert);
            $this->assertEquals($productTemplate->countrylistCstm->value     , 'aaaa');
            $this->assertEquals($productTemplate->statelistCstm->value       , 'aaa1');
            $this->assertEquals($productTemplate->citylistCstm->value        , 'ab1');
            $this->assertContains('gg'                                   , $productTemplate->multiselectCstm->values);
            $this->assertContains('hh'                                   , $productTemplate->multiselectCstm->values);
            $this->assertEquals(0                                        , $productTemplate->tagcloudCstm->values->count());
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calcnumber', 'ProductTemplate');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat($metadata->getFormula(), $productTemplate);
            $this->assertEquals(132                                     , intval(str_replace(',', "", $testCalculatedValue))); // Not Coding Standard
        }

        /**
         * @depends testEditOfTheProductTemplateForTheTagCloudFieldAfterRemovingAllTagsPlacedForProductTemplatesModule
         */
        public function testEditOfTheProductTemplateForTheCustomFieldsPlacedForProductTemplatesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Retrieve the account id, the super user id and opportunity Id.
            $superUserId                      = $super->id;
            $productTemplate                  = ProductTemplate::getByName('myEditProductTemplate');
            $productTemplateId                = $productTemplate[0]->id;

            //Edit a new ProductTemplate based on the custom fields.
            $this->setGetArray(array('id' => $productTemplateId));
            $this->setPostArray(array('ProductTemplate' => array(
                            'name'                              => 'myEditProductTemplate',
                            'type'                              => ProductTemplate::TYPE_PRODUCT,
                            'description'                       => 'Test Description',
                            'sellPrice'                         => array ('currency' => array ('id' => $baseCurrency->id), 'value' => 200 ),
                            'cost'                              => array ('currency' => array ('id' => $baseCurrency->id), 'value' => 200 ),
                            'listPrice'                         => array ('currency' => array ('id' => $baseCurrency->id), 'value' => 200 ),
                            'priceFrequency'                    => 2,
                            'status'                            => ProductTemplate::STATUS_ACTIVE,
                            'sellPriceFormula'                  => array ( 'type' => SellPriceFormula::TYPE_EDITABLE ),
                            'checkboxCstm'                      => '0',
                            'currencyCstm'                      => array('value'   => 40,
                                                                         'currency' => array(
                                                                         'id' => $baseCurrency->id)),
                            'decimalCstm'                       => '12',
                            'dateCstm'                          => $date,
                            'datetimeCstm'                      => $datetime,
                            'picklistCstm'                      => array('value'  => 'b'),
                            'multiselectCstm'                   => array('values' =>  array('gg', 'hh')),
                            'tagcloudCstm'                      => array('values' =>  array('reading', 'surfing')),
                            'countrylistCstm'                   => array('value'  => 'aaaa'),
                            'statelistCstm'                     => array('value'  => 'aaa1'),
                            'citylistCstm'                      => array('value'  => 'ab1'),
                            'integerCstm'                       => '11',
                            'phoneCstm'                         => '259-784-2069',
                            'radioCstm'                         => array('value' => 'e'),
                            'textCstm'                          => 'This is a test Edit Text',
                            'textareaCstm'                      => 'This is a test Edit TextArea',
                            'urlCstm'                           => 'http://wwww.abc-edit.com')));
            $this->runControllerWithRedirectExceptionAndGetUrl('productTemplates/default/edit');

            //Check the details if they are saved properly for the custom fields.
            $productTemplateId = self::getModelIdByModelNameAndName('ProductTemplate', 'myEditProductTemplate');
            $productTemplate   = ProductTemplate::getById($productTemplateId);

            $this->assertEquals($productTemplate->name                       , 'myEditProductTemplate');
            $this->assertEquals($productTemplate->sellPrice->value           , 200.00);
            $this->assertEquals($productTemplate->cost->value                , 200.00);
            $this->assertEquals($productTemplate->listPrice->value           , 200.00);
            $this->assertEquals($productTemplate->description                , 'Test Description');
            $this->assertEquals($productTemplate->type                       , ProductTemplate::TYPE_PRODUCT);
            $this->assertEquals($productTemplate->status                     , ProductTemplate::STATUS_ACTIVE);
            $this->assertEquals($productTemplate->priceFrequency             , 2);
            $this->assertEquals($productTemplate->sellPriceFormula->type     , SellPriceFormula::TYPE_EDITABLE);
            $this->assertEquals($productTemplate->checkboxCstm               , '0');
            $this->assertEquals($productTemplate->currencyCstm->value        , 40);
            $this->assertEquals($productTemplate->currencyCstm->currency->id , $baseCurrency->id);
            $this->assertEquals($productTemplate->dateCstm                   , $dateAssert);
            $this->assertEquals($productTemplate->datetimeCstm               , $datetimeAssert);
            $this->assertEquals($productTemplate->decimalCstm                , '12');
            $this->assertEquals($productTemplate->picklistCstm->value        , 'b');
            $this->assertEquals($productTemplate->integerCstm                , 11);
            $this->assertEquals($productTemplate->phoneCstm                  , '259-784-2069');
            $this->assertEquals($productTemplate->radioCstm->value           , 'e');
            $this->assertEquals($productTemplate->textCstm                   , 'This is a test Edit Text');
            $this->assertEquals($productTemplate->textareaCstm               , 'This is a test Edit TextArea');
            $this->assertEquals($productTemplate->urlCstm                    , 'http://wwww.abc-edit.com');
            $this->assertEquals($productTemplate->dateCstm                   , $dateAssert);
            $this->assertEquals($productTemplate->datetimeCstm               , $datetimeAssert);
            $this->assertEquals($productTemplate->countrylistCstm->value     , 'aaaa');
            $this->assertEquals($productTemplate->statelistCstm->value       , 'aaa1');
            $this->assertEquals($productTemplate->citylistCstm->value        , 'ab1');
            $this->assertContains('gg'                                   , $productTemplate->multiselectCstm->values);
            $this->assertContains('hh'                                   , $productTemplate->multiselectCstm->values);
            $this->assertContains('reading'                              , $productTemplate->tagcloudCstm->values);
            $this->assertContains('surfing'                              , $productTemplate->tagcloudCstm->values);
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calcnumber', 'ProductTemplate');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat($metadata->getFormula(), $productTemplate);
            $this->assertEquals(132                                     , intval(str_replace(',', "", $testCalculatedValue))); // Not Coding Standard
        }

        /**
         * @depends testEditOfTheProductTemplateForTheCustomFieldsPlacedForProductTemplatesModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForProductTemplatesModuleAfterEditingTheProductTemplate()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Search a created ProductTemplate using the customfields.
            $this->resetPostArray();
            $this->setGetArray(array(
                        'ProductTemplatesSearchForm' =>
                            ProductTemplatesDesignerWalkthroughHelperUtil::fetchProductTemplatesSearchFormGetData(),
                            'ajax'                   =>  'list-view')
            );
            //TODO Ask Jason
            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default');
            $this->assertTrue(strpos($content, "myEditProductTemplate") > 0);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForProductTemplatesModuleAfterEditingTheProductTemplate
         */
        public function testDeleteOfTheProductTemplateUserForTheCustomFieldsPlacedForProductTemplatesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Get the product template id from the recently edited product template.
            $productTemplateId = self::getModelIdByModelNameAndName('ProductTemplate', 'myEditProductTemplate');

            //Set the opportunity id so as to delete the opportunity.
            $this->setGetArray(array('id' => $productTemplateId));
            $this->runControllerWithRedirectExceptionAndGetUrl('productTemplates/default/delete');

            //Check wether the product template is deleted.
            $productTemplate = ProductTemplate::getByName('myEditProductTemplate');
            $this->assertEquals(0, count($productTemplate));
        }

        /**
         * @depends testDeleteOfTheProductTemplateUserForTheCustomFieldsPlacedForProductTemplatesModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForProductTemplatesModuleAfterDeletingTheProductTemplate()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Search a created ProductTemplate using the customfields.
            $this->resetPostArray();
            $this->setGetArray(array(
                        'ProductTemplatesSearchForm' =>
                            ProductTemplatesDesignerWalkthroughHelperUtil::fetchProductTemplatesSearchFormGetData(),
                            'ajax'                    =>  'list-view')
            );

            //TODO Ask Jason
            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default');

            //Assert that the edit ProductTemplate does not exits after the search.
            $this->assertTrue(strpos($content, "No results found.") > 0);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForProductTemplatesModuleAfterDeletingTheProductTemplate
         */
        public function testTypeAheadWorksForTheTagCloudFieldPlacedForProductTemplatesModule()
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
         * @depends testTypeAheadWorksForTheTagCloudFieldPlacedForProductTemplatesModule
         */
        public function testLabelLocalizationForTheTagCloudFieldPlacedForProductTemplatesModule()
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