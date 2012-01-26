<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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

    class DesignerTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testSetMetadataFromLayout()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $layout = array('panels' =>
                array(
                    array(
                        'rows' => array(
                            array(
                                'cells' => array(
                                    array('element' => 'id'),
                                ),
                            ),
                            array(
                                'cells' => array(
                                    array('element' => 'name'),
                                )
                            )
                        )
                    )
                )
            );
            $compareMetadata = array('panels' =>
                array(
                    array(
                        'rows' => array(
                            array(
                                'cells' => array(
                                    array('elements' => array(
                                        array('attributeName' => 'id', 'type' => 'Text')
                                        )
                                    ),
                                ),
                            ),
                            array(
                                'cells' => array(
                                    array('elements' => array(
                                        array('attributeName' => 'name', 'type' => 'Text', 'isLink' => true)
                                        )
                                    ),
                                )
                            )
                        )
                    )
                )
            );
            $model = new Account();
            $modelAttributesAdapter = new ModelAttributesAdapter($model);
            $editableMetadata = AccountsListView::getMetadata();
            $this->assertNotEquals($editableMetadata['global']['panels'], $layout);
            $attributesLayoutAdapter = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter(
                $modelAttributesAdapter->getAttributes(),
                new ListViewDesignerRules(),
                $editableMetadata
            );
            $adapter = new LayoutMetadataAdapter('AccountsListView',
                'AccountsModule',
                $editableMetadata,
                new ListViewDesignerRules(),
                $attributesLayoutAdapter->getPlaceableLayoutAttributes(),
                $attributesLayoutAdapter->getRequiredDerivedLayoutAttributeTypes()
            );
            $this->assertTrue($adapter->setMetadataFromLayout($layout, array()));
            $editableMetadataNew = AccountsListView::getMetadata();
            $this->assertNotEquals($editableMetadataNew, $editableMetadata);
            $this->assertEquals($editableMetadataNew['global']['panels'], $compareMetadata['panels']);
            $this->assertNotEmpty($adapter->getMessage());
        }

        public function testGetModelAttributesAdapter()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $model = new Account();
            $modelAttributes = $model->getAttributes();
            $this->assertTrue(isset($modelAttributes['contacts']));
            $adapter = new ModelAttributesAdapter($model);
            $adaptedAttributes = $adapter->getAttributes();
            $this->assertNotEmpty($adaptedAttributes['createdDateTime']['attributeLabel']);
            $this->assertNotEmpty($adaptedAttributes['officePhone']   ['attributeLabel']);
            $this->assertFalse(isset($adaptedAttributes['contacts']));
            $this->assertTrue(isset($adaptedAttributes['id']));
            $this->assertTrue(isset($adaptedAttributes['id']['attributeLabel']));
            $this->assertEquals('Id', $adaptedAttributes['id']['attributeLabel']);
        }

        /**
         * @depends testGetModelAttributesAdapter
         */
        public function testSetMetadataFromLayoutWithOutPanels()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $layout = array('panels' => array());
            $model = new Account();
            $modelAttributesAdapter = new ModelAttributesAdapter($model);
            $editableMetadata = AccountsListView::getMetadata();
            $attributesLayoutAdapter = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter(
                $modelAttributesAdapter->getAttributes(),
                new ListViewDesignerRules(),
                $editableMetadata
            );
            $adapter = new LayoutMetadataAdapter('AccountsListView',
                'AccountsModule',
                $editableMetadata,
                new ListViewDesignerRules(),
                $attributesLayoutAdapter->getPlaceableLayoutAttributes(),
                $attributesLayoutAdapter->getRequiredDerivedLayoutAttributeTypes()
            );
            $this->assertFalse($adapter->setMetadataFromLayout($layout, array()));
            $this->assertNotEmpty($adapter->getMessage());
        }

        /**
         * @depends testGetModelAttributesAdapter
         */
        public function testSetMetadataFromLayoutWithOutRequiredField()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $layout = array('panels' =>
                array(
                    array(
                        'rows' => array(
                            array(
                                'cells' => array(
                                    array('element' => 'officePhone'),
                                ),
                            ),
                        )
                    )
                )
            );
            $model = new Account();
            $modelAttributesAdapter = new ModelAttributesAdapter($model);
            $editableMetadata = AccountEditAndDetailsView::getMetadata();
            $attributesLayoutAdapter = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter(
                $modelAttributesAdapter->getAttributes(),
                new EditAndDetailsViewDesignerRules(),
                $editableMetadata
            );
            $adapter = new LayoutMetadataAdapter('AccountEditAndDetailsView',
                'AccountsModule',
                $editableMetadata,
                new EditAndDetailsViewDesignerRules(),
                $attributesLayoutAdapter->getPlaceableLayoutAttributes(),
                $attributesLayoutAdapter->getRequiredDerivedLayoutAttributeTypes()
            );
            $this->assertFalse($adapter->setMetadataFromLayout($layout, array()));
            $this->assertNotEmpty($adapter->getMessage());
            $this->assertEquals($adapter->getMessage(), 'All required fields must be placed in this layout.');
        }

        /**
         * @depends testGetModelAttributesAdapter
         */
        public function testSetMetadataFromLayoutWithAndWithOutRequiredDerivedField()
        {
            $layout = array('panels' =>
                array(
                    array(
                        'rows' => array(
                            array(
                                'cells' => array(
                                    array('element' => 'username'),
                                ),
                            ),
                        )
                    )
                )
            );
            $layout2 = array('panels' =>
                array(
                    array(
                        'rows' => array(
                            array(
                                'cells' => array(
                                    array('element' => 'TitleFullName'),
                                ),
                            ),
                            array(
                                'cells' => array(
                                    array('element' => 'username'),
                                ),
                            ),
                        )
                    )
                )
            );
            $model = new User();
            $editableMetadata = UserCreateView::getMetadata();
            $modelAttributesAdapter = new ModelAttributesAdapter($model);
            $attributesLayoutAdapter = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter(
                $modelAttributesAdapter->getAttributes(),
                new EditAndDetailsViewDesignerRules(),
                $editableMetadata
            );
            $adapter = new LayoutMetadataAdapter('UserCreateView',
                'UsersModule',
                $editableMetadata,
                new EditAndDetailsViewDesignerRules(),
                $attributesLayoutAdapter->getPlaceableLayoutAttributes(),
                $attributesLayoutAdapter->getRequiredDerivedLayoutAttributeTypes()
            );
            $this->assertFalse($adapter->setMetadataFromLayout($layout, array()));
            $this->assertNotEmpty($adapter->getMessage());
            $this->assertEquals($adapter->getMessage(), 'All required fields must be placed in this layout.');
            $this->assertTrue($adapter->setMetadataFromLayout($layout2, array()));
        }

        /**
         * @depends testGetModelAttributesAdapter
         */
        public function testMakeLayoutAttributes()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $adapter = new ModelAttributesAdapter(new Account());
            $adaptedAttributes = $adapter->getAttributes();
            $metadata = array();
            $metadata['global']['panels'][] = array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'name', 'type' => 'Text'),
                                            ),
                                        ),
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'officePhone', 'type' => 'Phone'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'owner', 'type' => 'User'),
                                            ),
                                        ),
                                        array(
                                            'elements' => array(
                                                array('attributeName' => null, 'type' => 'Null'), // Not Coding Standard
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        );
            $attributeLayoutAdapter = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter(
                $adaptedAttributes,
                new DetailsViewDesignerRules(),
                $metadata
            );
            $designerLayoutAdapter  = $attributeLayoutAdapter->makeDesignerLayoutAttributes();
            $this->assertEquals(count($designerLayoutAdapter->get()), count($adaptedAttributes));
            $attributeName = $designerLayoutAdapter->getByAttributeNameAndType('name', 'Text');
            $this->assertFalse($attributeName['availableToSelect']);
            $this->assertEquals($attributeName['attributeIdPrefix'], 'name');
            $attributeFax = $designerLayoutAdapter->getByAttributeNameAndType('officeFax', 'Phone');
            $this->assertTrue($attributeFax['availableToSelect']);
            $this->assertEquals($attributeFax['attributeIdPrefix'], 'officeFax');
        }

        /**
         * @depends testGetModelAttributesAdapter
         */
        public function testFormatEditableMetadataForLayoutParsing()
        {
            $editableListViewMetadata = AccountsListView::getMetadata();
            $editableEditViewMetadata = AccountEditAndDetailsView::getMetadata();
            $listViewDesignerRules = new ListViewDesignerRules();
            $editViewDesignerRules = new EditAndDetailsViewDesignerRules();
            $formattedMetadata = $listViewDesignerRules->formatEditableMetadataForLayoutParsing($editableListViewMetadata);
            $this->assertEquals($formattedMetadata, $editableListViewMetadata);
            $this->assertNotEmpty($formattedMetadata['global']['panels'][0]['rows'][0]);
            $formattedMetadata = $editViewDesignerRules->formatEditableMetadataForLayoutParsing($editableEditViewMetadata);
            $this->assertNotEquals($formattedMetadata, $editableEditViewMetadata);
        }

        /**
         * @depends testGetModelAttributesAdapter
         */
        public function testGetStandardAttributes()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $account = new Account();
            $adapter = new ModelAttributesAdapter($account);
            $attributes = $adapter->getStandardAttributes();
            $this->assertEquals(19, count($attributes));

            $this->assertEquals('Name',     $attributes['name']    ['attributeLabel']);
            $this->assertEquals('Industry', $attributes['industry']['attributeLabel']);
            $this->assertEquals('Type',     $attributes['type']    ['attributeLabel']);

            $this->assertEquals('Text',     $attributes['name']    ['elementType']);
            $this->assertEquals('DropDown', $attributes['industry']['elementType']);
            $this->assertEquals('DropDown', $attributes['type']    ['elementType']);

            $this->assertTrue(!isset($attributes['notes']));
        }

        /**
         * @depends testGetStandardAttributes
         */
        public function testGetCustomAttributesWhenThereArentAny()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $account = new Account();
            $adapter = new ModelAttributesAdapter($account);
            $attributes = $adapter->getCustomAttributes();
            $this->assertEquals(0, count($attributes));
        }

        /**
         * @depends testGetModelAttributesAdapter
         */
        public function testMakeAttributeFormByAttributeName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $account = new Account();
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'name');
            $this->assertTrue($attributeForm instanceof TextAttributeForm);
            $this->assertEquals('name', $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Name',
                'en' => 'Name',
                'es' => 'Nombre',
                'fr' => 'Nom',
                'it' => 'Nome',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(64,     $attributeForm->maxLength);
            $this->assertEquals(true,   $attributeForm->isRequired);
            $this->assertEquals(true,   $attributeForm->isAudited);
            $this->assertEquals(null,   $attributeForm->defaultValue);
            $this->assertEquals(true,   $attributeForm->isAudited);

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'officePhone');
            $this->assertTrue($attributeForm instanceof PhoneAttributeForm);
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'description');
            $this->assertTrue($attributeForm instanceof TextAreaAttributeForm);
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'industry');
            $this->assertTrue($attributeForm instanceof DropDownAttributeForm);
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'owner');
            $this->assertTrue($attributeForm instanceof UserAttributeForm);
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'createdDateTime');
            $this->assertTrue($attributeForm instanceof DateTimeAttributeForm);
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'modifiedDateTime');
            $this->assertTrue($attributeForm instanceof DateTimeAttributeForm);
        }

        /**
         * @depends testMakeAttributeFormByAttributeName
         */
        public function testSetNewAttributeFromAttributeForm()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $values = array(
                'Automotive',
                'Adult Entertainment',
                'Financial Services',
                'Mercenaries & Armaments',
            );
            $industryFieldData = CustomFieldData::getByName('Industries');
            $industryFieldData->forget();
            unset($industryFieldData);
            $industryFieldData = CustomFieldData::getByName('Industries');
            $industryFieldData->serializedData = serialize($values);
            $this->assertTrue($industryFieldData->save());

            $account = new Account();
            $attributeForm = new DropDownAttributeForm($account, 'industry');
            $attributeForm->attributeLabels   = array(
                'de' => 'Industry de',
                'en' => 'Industry',
                'es' => 'Industry es',
                'fr' => 'Industry fr',
                'it' => 'Industry it',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = false;
            $this->assertEquals($values, $attributeForm->customFieldDataData);

            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $account = new Account();
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'industry');
            $this->assertEquals('industry', $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Industry de',
                'en' => 'Industry',
                'es' => 'Industry es',
                'fr' => 'Industry fr',
                'it' => 'Industry it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,      $attributeForm->isAudited);
            $this->assertEquals(false,      $attributeForm->isRequired);
            $this->assertEquals($values,    $attributeForm->customFieldDataData);
        }

        /**
         * @depends testMakeAttributeFormByAttributeName
         */
        public function testSetStandardDropDownAttributeFromAttributeForm()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $account = new Account();
            $values = unserialize($account->industry->data->serializedData);

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'industry');
            $this->assertTrue($attributeForm instanceof DropDownAttributeForm);
            $this->assertEquals($values, $attributeForm->customFieldDataData);

            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }
            // This needs to test some things.

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'industry');
            $this->assertEquals($values, $attributeForm->customFieldDataData);
        }

        /**
         * @depends testSetNewAttributeFromAttributeForm
         */
        public function testSetAndGetCustomAttributes()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $adapter = new ModelAttributesAdapter(new Account());
            $attributes = $adapter->getCustomAttributes();
            $this->assertEquals(0,     count($attributes));

            $attributeForm = new DropDownAttributeForm();
            $attributeForm->attributeName       = 'testText';
            $attributeForm->attributeLabels   = array(
                'de' => 'Test Text de',
                'en' => 'Test Text en',
                'es' => 'Test Text es',
                'fr' => 'Test Text fr',
                'it' => 'Test Text it',
            );
            $attributeForm->isAudited           = true;
            $attributeForm->isRequired          = false;
            $attributeForm->customFieldDataName = 'Industries';

            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $account = new Account();
            $this->assertTrue($account->isAttribute('testText'));

            $adapter = new ModelAttributesAdapter($account);
            $attributes = $adapter->getCustomAttributes();
            $this->assertEquals(1,     count($attributes));
            $this->assertEquals('Test Text en', $attributes['testText']['attributeLabel']);
            $this->assertEquals('DropDown',  $attributes['testText']['elementType']);
        }

        /**
         * @depends testSetAndGetCustomAttributes
         */
        public function testRemoveAttributeByName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            ModelMetadataUtil::removeAttribute('Account', 'officePhone');
            ModelMetadataUtil::removeAttribute('Account', 'doesNotExist');

            $account = new Account();
            $this->assertFalse($account->isAttribute('officePhone'));
            unset($account);

            $attributeForm = new TextAttributeForm();
            $attributeForm->attributeName = 'testText2';
            $attributeForm->attributeLabels   = array(
                'de' => 'Test Text2 de',
                'en' => 'Test Text2 en',
                'es' => 'Test Text2 es',
                'fr' => 'Test Text2 fr',
                'it' => 'Test Text2 it',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = false;
            $attributeForm->maxLength     = 60;

            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $account = new Account();
            $this->assertTrue ($account->isAttribute('testText2'));
            unset($account);

            $adapter->removeAttributeMetadata('testText2');

            $account = new Account();
            $this->assertFalse($account->isAttribute('testText2'));
            unset($account);
        }

        public function testModelAttributesAdapterIsStandardAttribute()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $attributeForm = new TextAttributeForm();
            $attributeForm->attributeName = 'testText2';
            $attributeForm->attributeLabels   = array(
                'de' => 'Test Text2 de',
                'en' => 'Test Text2 en',
                'es' => 'Test Text2 es',
                'fr' => 'Test Text2 fr',
                'it' => 'Test Text2 it',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = false;
            $attributeForm->maxLength     = 60;

            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $adapter = new ModelAttributesAdapter(new Account());
            $this->assertFalse($adapter->isStandardAttribute('testText2'));
            $this->assertTrue($adapter->isStandardAttribute('name'));
        }

        public function testAttributePropertyFormAdapterCanUpdateProperty()
        {
            $adapter = new AttributePropertyToDesignerFormAdapter();
            $this->assertTrue($adapter->canUpdateProperty('attributeLabels'));
            $this->assertTrue($adapter->canUpdateProperty('isRequired'));
            $this->assertTrue($adapter->canUpdateProperty('isAudited'));
            $adapter->setUpdateRequiredFieldStatus(false);
            $this->assertFalse($adapter->canUpdateProperty('isRequired'));
            $this->assertTrue($adapter->canUpdateProperty('isAudited'));
        }

        public function testGetRequiredDerivedLayoutAttributeTypes()
        {
            $adapter = new ModelAttributesAdapter(new User());
            $adaptedAttributes = $adapter->getAttributes();
            $metadata = array();
            $metadata['global']['panels'] = array();
            $metadata['global']['derivedAttributeTypes'] = array(
                'TitleFullName',
            );
            $attributeLayoutAdapter = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter(
                $adaptedAttributes,
                new DetailsViewDesignerRules(),
                $metadata
            );
            $requiredTypes = $attributeLayoutAdapter->getRequiredDerivedLayoutAttributeTypes();
            $compareTypes = array(
                'TitleFullName',
            );
            $this->assertEquals($compareTypes, $requiredTypes);
        }

        /**
         * There was a bug if you had an existing model, then created a custom drop down, it would not show
         * any values.  This was resolved by making sure cached models constructDerived.  This test should pass now
         * that the fix is implemented.
         */
        public function testExistingModelsShowCustomFieldDataCorrectlyWhenAttributeIsAddedAsDatabaseColumn()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Create account
            $account   = AccountTestHelper::createAccountByNameForOwner('test', $super);
            $accountId = $account->id;
            $account->forget();

            $originalMetadata = Account::getMetadata();
            $attributeLabels  = array('en' => 'newRelation');
            ModelMetadataUtil::addOrUpdateCustomFieldRelation('Account', 'newRelation', $attributeLabels,
                null, false, false, 'DropDown', 'Things', array('thing 1', 'thing 2'),
                                                          array('fr' => array('thing 1 fr', 'thing 2 fr')));
            $adapter  = new ModelAttributesAdapter(new Account());
            $adapter->resolveDatabaseSchemaForModel('Account');
            $metadata = Account::getMetadata();
            $this->assertNotEquals($originalMetadata, $metadata);

            $this->assertEquals($originalMetadata['Account']['rules'], $metadata['Account']['rules']);
            $newRelation = $metadata['Account']['relations']['newRelation'];
            $this->assertEquals(array(RedBeanModel::HAS_ONE,  'CustomField'), $newRelation);
            $this->assertEquals('Things', $metadata['Account']['customFields']['newRelation']);

            //on a new account, does the serialized data show correctly.
            $account = new Account();
            $this->assertEquals(array('thing 1', 'thing 2'), unserialize($account->newRelation->data->serializedData));

            ForgetAllCacheUtil::forgetAllCaches();

            //retrieve account and make sure the serialized data shows correctly.
            //This will not be cached.
            $account = Account::getById($accountId);
            $this->assertNotNull($account->industry->data->serializedData);
            $this->assertEquals(array('thing 1', 'thing 2'), unserialize($account->newRelation->data->serializedData));

            //This will pull from cached.  Clear the php cache first, which simulates a new page request without destroying
            //the persistent cache.
            RedBeanModelsCache::forgetAll(true);
            $account = Account::getById($accountId);

            //Test pulling a different CustomField first. This simulates caching the customField
            $this->assertEquals(array('thing 1', 'thing 2'), unserialize($account->newRelation->data->serializedData));
        }

        public function testStandardAttributeThatBecomesRequiredCanStillBeChangedToBeUnrequired()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $account                    = new Account();

            //Name for example, is required by default.
            $adapter       = new ModelAttributesAdapter($account);
            $this->assertTrue($adapter->isStandardAttributeRequiredByDefault('name'));

            //Industry is not required by default.
            $adapter       = new DropDownModelAttributesAdapter($account);
            $this->assertFalse($adapter->isStandardAttributeRequiredByDefault('industry'));

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'industry');
            $this->assertFalse($attributeForm->isRequired);
            $this->assertTrue($attributeForm->canUpdateAttributeProperty('isRequired'));

            //Now make industry required.
            $attributeForm->isRequired = true;

            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }
            RedBeanModelsCache::forgetAll();

            $account       = new Account();
            $adapter       = new DropDownModelAttributesAdapter($account);
            $this->assertFalse($adapter->isStandardAttributeRequiredByDefault('industry'));
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'industry');
            $this->assertTrue($attributeForm->isRequired);
            $this->assertTrue($attributeForm->canUpdateAttributeProperty('isRequired'));
        }

        public function testIsStandardAttributeRequiredByDefault()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            //Testing an attribute that is not on the specified model, but requires a casting up.
            $contact       = new Contact();
            $adapter       = new ModelAttributesAdapter($contact);
            $this->assertTrue($adapter->isStandardAttributeRequiredByDefault('lastName'));
        }
    }
?>
