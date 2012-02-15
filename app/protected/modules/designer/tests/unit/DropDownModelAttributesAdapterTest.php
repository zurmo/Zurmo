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

    class DropDownModelAttributesAdapterTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            self::makeDropDownAttributeUsingAdapter('aaa', 'a', new Account());
            self::makeDropDownAttributeUsingAdapter('bbb', 'b', new Account());
            self::makeDropDownAttributeUsingAdapter('ccc', 'c', new Account());
        }

        public static function makeDropDownAttributeUsingAdapter($namePrefix, $valuesPrefix, RedBeanModel $modelTouse)
        {
            $values = array(
                $valuesPrefix . '1',
                $valuesPrefix . '2',
                $valuesPrefix . '3',
                $valuesPrefix . '4',
            );
            $labels = array('fr' => array($valuesPrefix . '1 fr', $valuesPrefix . '1 fr', $valuesPrefix . '1 fr', $valuesPrefix . '1 fr'),
                            'de' => array($valuesPrefix . '1 de', $valuesPrefix . '1 de', $valuesPrefix . '1 de', $valuesPrefix . '1 de'),
            );
            $airplanesFieldData = CustomFieldData::getByName($namePrefix . 'TheData');
            $airplanesFieldData->serializedData = serialize($values);
            $saved = $airplanesFieldData->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }

            $attributeForm = new DropDownAttributeForm();
            $attributeForm->attributeName       = $namePrefix;
            $attributeForm->attributeLabels  = array(
                'de' => $namePrefix . ' de',
                'en' => $namePrefix . ' en',
                'es' => $namePrefix . ' es',
                'fr' => $namePrefix . ' fr',
                'it' => $namePrefix . ' it',
            );
            $attributeForm->isAudited             = false;
            $attributeForm->isRequired            = false;
            $attributeForm->defaultValueOrder     = null;
            $attributeForm->customFieldDataData   = $values;
            $attributeForm->customFieldDataName   = $namePrefix . 'TheData';
            $attributeForm->customFieldDataLabels = $labels;

            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName($modelTouse);
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                throw new NotSupportedException();
            }
        }

        public function testUsingTheAdapterAsAWrapperToUpdateValueInMappingByOldAndNewValue()
        {
            $account = new Account();

            //First create a dependency
            $mappingData = array(array('attributeName' => 'aaa'),
                                 array('attributeName' => 'bbb',
                                        'valuesToParentValues' =>
                                         array('b1' => 'a1',
                                               'b2' => 'a2',
                                               'b3' => 'a3',
                                               'b4' => 'a4'
                                         )),
                                 array('attributeName' => 'ccc',
                                        'valuesToParentValues' =>
                                         array('c1' => 'b1',
                                               'c2' => 'b2',
                                               'c3' => 'b3',
                                               'c4' => 'b4'
                                         )));
            $metadata = new DropDownDependencyDerivedAttributeMetadata();
            $metadata->setScenario('nonAutoBuild');
            $metadata->name               = 'aName';
            $metadata->modelClassName     = 'Account';
            $metadata->serializedMetadata = serialize(array('attributeLabels' => array('a' => 'b'),
                                                            'mappingData' => $mappingData));
            $this->assertTrue($metadata->save());

            //Change b3 to b3New
            $attributeForm                                    = AttributesFormFactory::
                                                                createAttributeFormByAttributeName($account, 'bbb');
            $attributeForm->customFieldDataDataExistingValues = array('b1', 'b2', 'b3', 'b4');
            $attributeForm->customFieldDataData               = array('b1', 'b2', 'b3New', 'b4');
            $modelAttributesAdapterClassName                  = $attributeForm::
                                                                getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter                                          = new $modelAttributesAdapterClassName($account);
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            //Confirm b3 values changed correctly to b3New
            $metadata         = DropDownDependencyDerivedAttributeMetadata::getById($metadata->id);
            $unserializedData = unserialize($metadata->serializedMetadata);

            $compareData      = array(array('attributeName' => 'aaa'),
                                 array('attributeName' => 'bbb',
                                        'valuesToParentValues' =>
                                         array('b1' => 'a1',
                                               'b2' => 'a2',
                                               'b3New' => 'a3',
                                               'b4' => 'a4'
                                         )),
                                 array('attributeName' => 'ccc',
                                        'valuesToParentValues' =>
                                         array('c1' => 'b1',
                                               'c2' => 'b2',
                                               'c3' => 'b3New',
                                               'c4' => 'b4'
                                         )));
            $this->assertEquals(array('a' => 'b'), $unserializedData['attributeLabels']);
            $this->assertEquals($compareData, $unserializedData['mappingData']);

            //Now change a value for a dropdown that is the first level of the dependency. This will only change the
            //value when it is a parent value.
            $attributeForm                                    = AttributesFormFactory::
                                                                createAttributeFormByAttributeName($account, 'aaa');
            $attributeForm->customFieldDataDataExistingValues = array('a1', 'a2', 'a3', 'a4');
            $attributeForm->customFieldDataData               = array('a1', 'a2New', 'a3', 'a4');
            $modelAttributesAdapterClassName                  = $attributeForm::
                                                                getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter                                          = new $modelAttributesAdapterClassName($account);
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            //Confirm a2 values changed correctly to a2New
            $metadata         = DropDownDependencyDerivedAttributeMetadata::getById($metadata->id);
            $unserializedData = unserialize($metadata->serializedMetadata);

            $compareData      = array(array('attributeName' => 'aaa'),
                                 array('attributeName' => 'bbb',
                                        'valuesToParentValues' =>
                                         array('b1' => 'a1',
                                               'b2' => 'a2New',
                                               'b3New' => 'a3',
                                               'b4' => 'a4'
                                         )),
                                 array('attributeName' => 'ccc',
                                        'valuesToParentValues' =>
                                         array('c1' => 'b1',
                                               'c2' => 'b2',
                                               'c3' => 'b3New',
                                               'c4' => 'b4'
                                         )));
            $this->assertEquals(array('a' => 'b'), $unserializedData['attributeLabels']);
            $this->assertEquals($compareData, $unserializedData['mappingData']);

            //Now change 2 values at once.
            $attributeForm                                    = AttributesFormFactory::
                                                                createAttributeFormByAttributeName($account, 'ccc');
            $attributeForm->customFieldDataDataExistingValues = array('c1', 'c2', 'c3', 'c4');
            $attributeForm->customFieldDataData               = array('c1New', 'c2New', 'c3', 'c4');
            $modelAttributesAdapterClassName                  = $attributeForm::
                                                                getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter                                          = new $modelAttributesAdapterClassName($account);
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            //Confirm c1, c2 both changed.
            $metadata         = DropDownDependencyDerivedAttributeMetadata::getById($metadata->id);
            $unserializedData = unserialize($metadata->serializedMetadata);

            $compareData      = array(array('attributeName' => 'aaa'),
                                 array('attributeName' => 'bbb',
                                        'valuesToParentValues' =>
                                         array('b1' => 'a1',
                                               'b2' => 'a2New',
                                               'b3New' => 'a3',
                                               'b4' => 'a4'
                                         )),
                                 array('attributeName' => 'ccc',
                                        'valuesToParentValues' =>
                                         array('c1New' => 'b1',
                                               'c2New' => 'b2',
                                               'c3' => 'b3New',
                                               'c4' => 'b4'
                                         )));
            $this->assertEquals(array('a' => 'b'), $unserializedData['attributeLabels']);
            $this->assertEquals($compareData, $unserializedData['mappingData']);
        }

        /**
         * @depends testUsingTheAdapterAsAWrapperToUpdateValueInMappingByOldAndNewValue
         */
        public function testUsingTheAdapterAsAWrapperToResolveValuesInMappingWhenValueWasRemoved()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $account = new Account();

            //Remove a1
            $attributeForm                                    = AttributesFormFactory::
                                                                createAttributeFormByAttributeName($account, 'aaa');
            $attributeForm->customFieldDataData               = array('a2New', 'a3', 'a4');
            $modelAttributesAdapterClassName                  = $attributeForm::
                                                                getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter                                          = new $modelAttributesAdapterClassName($account);
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            //Confirm a1 has been removed from the mapping.
            $metadata         = DropDownDependencyDerivedAttributeMetadata::
                                getByNameAndModelClassName('aName', 'Account');
            $unserializedData = unserialize($metadata->serializedMetadata);

            $compareData      = array(array('attributeName' => 'aaa'),
                                 array('attributeName' => 'bbb',
                                        'valuesToParentValues' =>
                                         array('b1' => null,
                                               'b2' => 'a2New',
                                               'b3New' => 'a3',
                                               'b4' => 'a4'
                                         )),
                                 array('attributeName' => 'ccc',
                                        'valuesToParentValues' =>
                                         array('c1New' => 'b1',
                                               'c2New' => 'b2',
                                               'c3' => 'b3New',
                                               'c4' => 'b4'
                                         )));
            $this->assertEquals(array('a' => 'b'), $unserializedData['attributeLabels']);
            $this->assertEquals($compareData, $unserializedData['mappingData']);

            //Remove b4
            $attributeForm                                    = AttributesFormFactory::
                                                                createAttributeFormByAttributeName($account, 'bbb');
            $attributeForm->customFieldDataData               = array('b1', 'b2', 'b3New');
            $modelAttributesAdapterClassName                  = $attributeForm::
                                                                getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter                                          = new $modelAttributesAdapterClassName($account);
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            //Confirm b4 has been removed from the mapping.
            $metadata         = DropDownDependencyDerivedAttributeMetadata::
                                getByNameAndModelClassName('aName', 'Account');
            $unserializedData = unserialize($metadata->serializedMetadata);

            $compareData      = array(array('attributeName' => 'aaa'),
                                 array('attributeName' => 'bbb',
                                        'valuesToParentValues' =>
                                         array('b1' => null,
                                               'b2' => 'a2New',
                                               'b3New' => 'a3',
                                         )),
                                 array('attributeName' => 'ccc',
                                        'valuesToParentValues' =>
                                         array('c1New' => 'b1',
                                               'c2New' => 'b2',
                                               'c3' => 'b3New',
                                               'c4' => null
                                         )));
            $this->assertEquals(array('a' => 'b'), $unserializedData['attributeLabels']);
            $this->assertEquals($compareData, $unserializedData['mappingData']);
        }
    }
?>