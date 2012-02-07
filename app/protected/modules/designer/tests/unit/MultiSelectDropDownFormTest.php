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

    class MultiSelectDropDownFormTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setup()
        {
            parent::setup();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testSetAndGetMultiSelectDropDownAttribute()
        {
            //Create a test multiple values custom field.
            $values = array(
                'Reading',
                'Writing',
                'Singing',
                'Surfing',
            );
            $labels = array('fr' => array('Reading fr', 'Writing fr', 'Singing fr', 'Surfing fr'),
                            'de' => array('Reading de', 'Writing de', 'Singing de', 'Surfing de'),
            );
            $hobbiesFieldData = CustomFieldData::getByName('Hobbies');
            $hobbiesFieldData->serializedData = serialize($values);
            $this->assertTrue($hobbiesFieldData->save());

            $attributeForm = new MultiSelectDropDownAttributeForm();
            $attributeForm->attributeName    = 'testHobbies';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Hobbies 2 de',
                'en' => 'Test Hobbies 2 en',
                'es' => 'Test Hobbies 2 es',
                'fr' => 'Test Hobbies 2 fr',
                'it' => 'Test Hobbies 2 it',
            );
            $attributeForm->isAudited             = true;
            $attributeForm->isRequired            = true;
            $attributeForm->defaultValueOrder     = 1;
            $attributeForm->customFieldDataData   = $values;
            $attributeForm->customFieldDataName   = 'Hobbies';
            $attributeForm->customFieldDataLabels = $labels;

            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter                         = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $account       = new Account();
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'testHobbies');
            $this->assertEquals('MultiSelectDropDown', $attributeForm->getAttributeTypeName());
            $this->assertEquals('testHobbies',         $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Hobbies 2 de',
                'en' => 'Test Hobbies 2 en',
                'es' => 'Test Hobbies 2 es',
                'fr' => 'Test Hobbies 2 fr',
                'it' => 'Test Hobbies 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,                    $attributeForm->isAudited);
            $this->assertEquals(true,                    $attributeForm->isRequired);
            $this->assertEquals('Writing',               $attributeForm->defaultValue);
            $this->assertEquals(1,                       $attributeForm->defaultValueOrder);
            $this->assertEquals('Hobbies',               $attributeForm->customFieldDataName);
            $this->assertEquals($values,                 $attributeForm->customFieldDataData);
            $this->assertEquals($labels,                 $attributeForm->customFieldDataLabels);
        }

         /**
         * @depends testSetAndGetMultiSelectDropDownAttribute
         */
        public function testSearchForMultiSelectDropDownAttributePlacedForAccountsModule()
        {
            //Test that the multiple select attribute can query properly for search.
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Create an account to test searching multiple fields on for search.
            $account                  = new Account();
            $this->assertEquals(1, $account->testHobbies->values->count());
            $account->name            = 'my test account';
            $account->owner           = Yii::app()->user->userModel;
            $customFieldValue2        = new CustomFieldValue();
            $customFieldValue2->value = 'Reading';
            $account->testHobbies->values->add($customFieldValue2);
            $this->assertTrue($account->save());
            $accountId                = $account->id;
            $account                  = Account::getById($accountId);
            $this->assertEquals(2, $account->testHobbies->values->count());
            $this->assertContains('Writing',                  $account->testHobbies->values);
            $this->assertContains('Reading',                  $account->testHobbies->values);

            //Create a second account with different hobbies
            $account                  = new Account();
            //Remove the default value of 'Writing';
            $account->testHobbies->values->removeByIndex(0);
            $account->name            = 'my test account2';
            $account->owner           = Yii::app()->user->userModel;
            $customFieldValue1        = new CustomFieldValue();
            $customFieldValue1->value = 'Singing';
            $account->testHobbies->values->add($customFieldValue1);
            $customFieldValue2        = new CustomFieldValue();
            $customFieldValue2->value = 'Surfing';
            $account->testHobbies->values->add($customFieldValue2);
            $this->assertTrue($account->save());

            $accountId                = $account->id;
            $account                  = Account::getById($accountId);
            $this->assertEquals(2, $account->testHobbies->values->count());
            $this->assertContains('Singing',                  $account->testHobbies->values);
            $this->assertContains('Surfing',                  $account->testHobbies->values);

            //Searching with a custom field that is not blank should not produce an errors.
            $searchPostData      = array('name'        => 'my test account',
                                         'officePhone' => '',
                                         'testHobbies' => array('values' => array(0 => '')),
                                         'officeFax'   => '');

            $modifiedSearchPostData = SearchUtil::getSearchAttributesFromSearchArray($searchPostData);

            $this->assertEquals(array('name'        => 'my test account',
                                      'officePhone' => null,
                                      'testHobbies' => array('values' => array()),
                                      'officeFax'   => null), $modifiedSearchPostData);

            $account             = new Account(false);
            $searchForm          = new AccountsSearchForm($account);
            $metadataAdapter     = new SearchDataProviderMetadataAdapter($searchForm, $super->id, $modifiedSearchPostData);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            //Run search and make sure the data returned matches how many total accounts are available.
            $dataProvider        = new RedBeanModelDataProvider('Account', null, false, $searchAttributeData);
            $data                = $dataProvider->getData();
            $this->assertEquals(2, count($data));
        }

        /**
         * @depends testSearchForMultiSelectDropDownAttributePlacedForAccountsModule
         */
        public function testMultiSelectDropDownAttributeValuesAfterCreateAndEditPlacedForAccountsModule()
        {
            //Test that the multiple select attribute can query properly for search.
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Create an account to test searching multiple fields on for search.
            $account                  = new Account();
            $this->assertEquals(1, $account->testHobbies->values->count());
            $account->testHobbies->values->removeAll();
            $this->assertEquals(0, $account->testHobbies->values->count());
            $account->name            = 'MyTestAccount';
            $account->owner           = Yii::app()->user->userModel;
            $customFieldValue1        = new CustomFieldValue();
            $customFieldValue1->value = 'Reading';
            $account->testHobbies->values->add($customFieldValue1);
            $customFieldValue2        = new CustomFieldValue();
            $customFieldValue2->value = 'Writing';
            $account->testHobbies->values->add($customFieldValue2);
            $this->assertTrue($account->save());
            $accountId                = $account->id;
            $account->forget();
            unset($account);

            $account                  = Account::getById($accountId);
            $this->assertEquals(2, $account->testHobbies->values->count());
            $this->assertContains('Reading',                  $account->testHobbies->values);
            $this->assertContains('Writing',                  $account->testHobbies->values);
            $account->forget();
            unset($account);

            $account = Account::getById($accountId);
            $customFieldValue3        = new CustomFieldValue();
            $customFieldValue3->value = 'Writing';
            $account->testHobbies->values->add($customFieldValue3);
            $this->assertEquals(3, $account->testHobbies->values->count());
            $this->assertContains('Reading',                  $account->testHobbies->values);
            $this->assertContains('Writing',                  $account->testHobbies->values);
            $this->assertNotContains('Surfing',               $account->testHobbies->values);
            $this->assertNotContains('Gardening',             $account->testHobbies->values);
        }
    }
?>