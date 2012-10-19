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

    class ModelMetadataUtilTest extends ZurmoBaseTest
    {
        public function testGlobalMetadata()
        {
            $globalMetadata = new GlobalMetadata();
            $globalMetadata->className          = 'Whatever';
            $globalMetadata->serializedMetadata = serialize(array('stuff', 1));
            $this->assertTrue($globalMetadata->save());
            unset($globalMetadata);
            $globalMetadata = GlobalMetadata::getByClassName('Whatever');
            $this->assertEquals('a:2:{i:0;s:5:"stuff";i:1;i:1;}', $globalMetadata->serializedMetadata);
        }

        /**
         * @depends testGlobalMetadata
         */
        public function testGetModifySaveAndGetMetadata()
        {
            $a = new A();
            $this->assertTrue ($a->isAttribute('a'));
            $this->assertFalse($a->isAttribute('newMember'));
            unset($a);

            $originalMetadata = A::getMetadata();
            $metadata = A::getMetadata();
            $metadata['A']['members'][] = 'newMember';
            A::setMetadata($metadata);

            $this->assertNotEquals($originalMetadata, A::getMetadata());
            $this->assertEquals   ($metadata,         A::getMetadata());

            $a = new A();
            $this->assertTrue($a->isAttribute('a'));
            $this->assertTrue($a->isAttribute('newMember'));
        }

        /**
         * @depends testGetModifySaveAndGetMetadata
         */
        public function testAddNonRequiredMemberWithoutDefaultValue()
        {
            $originalMetadata = A::getMetadata();
            $attributeLabels  = array('en' => 'newMember2');
            ModelMetadataUtil::addOrUpdateMember('A', 'newMember2', $attributeLabels,
                null, null, null, null, null, false, false, 'Text', array());
            $metadata = A::getMetadata();
            $this->assertNotEquals($originalMetadata, $metadata);
            $this->assertEquals(count($originalMetadata['A']['members']) + 1, count($metadata['A']['members']));
            $membersCount = count($metadata['A']['members']);
            $newMember = $metadata['A']['members'][$membersCount - 1];
            $this->assertEquals('newMember2Cstm', $newMember);
            $this->assertEquals($originalMetadata['A']['rules'], $metadata['A']['rules']);
        }

        /**
         * @depends testAddNonRequiredMemberWithoutDefaultValue
         */
        public function testAddAndRemoveRequiredMemberWithDefaultValue()
        {
            $originalMetadata = A::getMetadata();
            $attributeLabels  = array('en' => 'newMember3');
            ModelMetadataUtil::addOrUpdateMember('A', 'newMember3', $attributeLabels, 3,
                null, null, null, null, true, true, 'Text', array());
            $metadata = A::getMetadata();
            $this->assertNotEquals($originalMetadata, $metadata);
            $this->assertEquals(count($originalMetadata['A']['rules']) + 2, count($metadata['A']['rules']));
            $rulesCount = count($metadata['A']['rules']);
            $newRule = $metadata['A']['rules'][$rulesCount - 2];
            $this->assertEquals(array('newMember3Cstm', 'default', 'value' => 3), $newRule);
            $newRule = $metadata['A']['rules'][$rulesCount - 1];
            $this->assertEquals(array('newMember3Cstm', 'required'), $newRule);
        }

        /**
         * @depends testAddAndRemoveRequiredMemberWithDefaultValue
         */
        public function testAddAndRemoveNonRequiredMemberWithMaxLength()
        {
            $originalMetadata = A::getMetadata();
            $attributeLabels  = array('en' => 'newMember4');
            ModelMetadataUtil::addOrUpdateMember('A', 'newMember4', $attributeLabels,
                null, 10, null, null, null, false, false, 'Text', array());
            $metadata = A::getMetadata();
            $this->assertNotEquals($originalMetadata, $metadata);
            $this->assertEquals(count($originalMetadata['A']['rules']) + 1, count($metadata['A']['rules']));
            $rulesCount = count($metadata['A']['rules']);
            $newRule = $metadata['A']['rules'][$rulesCount - 1];
            $this->assertEquals(array('newMember4Cstm', 'length', 'max' => 10), $newRule);
        }

        /**
         * @depends testAddAndRemoveNonRequiredMemberWithMaxLength
         */
        public function testAddAndModifyNonRequiredMemberWithMixedRule()
        {
            $originalMetadata = A::getMetadata();
            $attributeLabels  = array('en' => 'newMember5');
            $mixedRule        = array('someRule' , 'value' => 'someValue');
            ModelMetadataUtil::addOrUpdateMember('A', 'newMember5', $attributeLabels,
                null, 10, null, null, null, false, false, 'Text', array(), $mixedRule);
            $metadata = A::getMetadata();
            $this->assertNotEquals($originalMetadata, $metadata);
            $this->assertEquals(count($originalMetadata['A']['rules']) + 2, count($metadata['A']['rules']));
            $rulesCount = count($metadata['A']['rules']);
            $newRule = $metadata['A']['rules'][$rulesCount - 1];
            $this->assertEquals(array('newMember5Cstm', 'someRule', 'value' => 'someValue'), $newRule);
            //Update mixed rule for attribute.
            $mixedRule        = array('someRule' , 'value' => 'someValue2');
            ModelMetadataUtil::addOrUpdateMember('A', 'newMember5', $attributeLabels,
                null, 10, null, null, null, false, false, 'Text', array(), $mixedRule);
            $metadataUpdated = A::getMetadata();
            $this->assertNotEquals($originalMetadata, $metadata);
            $this->assertEquals(count($metadata['A']['rules']), count($metadataUpdated['A']['rules']));
            $newRule = $metadataUpdated['A']['rules'][$rulesCount - 1];
            $this->assertEquals(array('newMember5Cstm', 'someRule', 'value' => 'someValue2'), $newRule);
        }

        /**
         * @depends testAddAndModifyNonRequiredMemberWithMixedRule
         */
        public function testAddNonRequiredCustomFieldRelationWithoutDefaultValue()
        {
            $originalMetadata = A::getMetadata();
            $attributeLabels  = array('en' => 'newRelation');
            ModelMetadataUtil::addOrUpdateCustomFieldRelation('A', 'newRelation', $attributeLabels,
                null, false, false, 'DropDown', 'Things', array('thing 1', 'thing 2'),
                                                          array('fr' => array('thing 1 fr', 'thing 2 fr')));
            $metadata = A::getMetadata();
            $this->assertNotEquals($originalMetadata, $metadata);

            $this->assertEquals($originalMetadata['A']['rules'], $metadata['A']['rules']);

            $this->assertEquals(1, count($metadata['A']['relations']));
            $newRelation = $metadata['A']['relations']['newRelationCstm'];
            $this->assertEquals(array(RedBeanModel::HAS_ONE, 'OwnedCustomField', RedBeanModel::OWNED), $newRelation);
            $this->assertEquals(1, count($metadata['A']['customFields']));
            $this->assertEquals('Things', $metadata['A']['customFields']['newRelationCstm']);
        }

        /**
         * @depends testAddNonRequiredCustomFieldRelationWithoutDefaultValue
         */
        public function testAddRequiredCustomFieldRelationWithDefaultValue()
        {
            $thingCustomField = new CustomField();
            $thingCustomField->value = 'thing 1';

            $originalMetadata = A::getMetadata();
            $attributeLabels  = array('en' => 'newRelation2');
            ModelMetadataUtil::addOrUpdateCustomFieldRelation('A', 'newRelation2', $attributeLabels,
                $thingCustomField, true, false, 'DropDown', 'Things', array('thing 1', 'thing 2'));
            $metadata = A::getMetadata();
            $this->assertNotEquals($originalMetadata, $metadata);

            $this->assertEquals(count($originalMetadata['A']['rules']) + 2, count($metadata['A']['rules']));
            $rulesCount = count($metadata['A']['rules']);
            $newRule = $metadata['A']['rules'][$rulesCount - 2];
            $this->assertEquals('newRelation2Cstm',           $newRule[0]);
            $this->assertEquals('default',                $newRule[1]);
            $this->assertEquals($thingCustomField->value, $newRule['value']->value);
            $newRule = $metadata['A']['rules'][$rulesCount - 1];
            $this->assertEquals(array('newRelation2Cstm', 'required'), $newRule);

            $this->assertEquals(count($originalMetadata['A']['relations']) + 1, count($metadata['A']['relations']));
            $newRelation = $metadata['A']['relations']['newRelation2Cstm'];
            $this->assertEquals(array(RedBeanModel::HAS_ONE, 'OwnedCustomField', RedBeanModel::OWNED), $newRelation);
            $this->assertEquals(count($originalMetadata['A']['customFields']) + 1, count($metadata['A']['customFields']));
            $this->assertEquals('Things', $metadata['A']['customFields']['newRelation2Cstm']);
        }

        /**
         * @depends testAddRequiredCustomFieldRelationWithDefaultValue
         */
        public function testRemoveAttributes()
        {
            $originalMetadata = A::getMetadata();

            ModelMetadataUtil::removeAttribute('A', 'newMember');
            $metadata = A::getMetadata();
            $this->assertNotEquals($originalMetadata, $metadata);
            $this->assertEquals($originalMetadata['A']['rules'], $metadata['A']['rules']);

            ModelMetadataUtil::removeAttribute('A', 'newMember2Cstm');
            $metadata = A::getMetadata();
            $this->assertNotEquals($originalMetadata, $metadata);
            $this->assertEquals($originalMetadata['A']['rules'], $metadata['A']['rules']);

            ModelMetadataUtil::removeAttribute('A', 'newMember3Cstm');
            $metadata = A::getMetadata();
            $this->assertNotEquals($originalMetadata, $metadata);
            $this->assertEquals(count($originalMetadata['A']['rules']) - 2, count($metadata['A']['rules']));

            ModelMetadataUtil::removeAttribute('A', 'newMember4Cstm');
            $metadata = A::getMetadata();
            $this->assertNotEquals($originalMetadata, $metadata);
            $this->assertEquals(count($originalMetadata['A']['rules']) - 3, count($metadata['A']['rules']));

            ModelMetadataUtil::removeAttribute('A', 'newMember5Cstm');
            $metadata = A::getMetadata();
            $this->assertNotEquals($originalMetadata, $metadata);
            $this->assertEquals(count($originalMetadata['A']['rules']) - 5, count($metadata['A']['rules']));

            ModelMetadataUtil::removeAttribute('A', 'newRelationCstm');
            $metadata = A::getMetadata();
            $this->assertNotEquals($originalMetadata, $metadata);
            $this->assertEquals(count($originalMetadata['A']['rules']) - 5, count($metadata['A']['rules']));

            ModelMetadataUtil::removeAttribute('A', 'newRelation2Cstm');
            $metadata = A::getMetadata();
            $this->assertNotEquals($originalMetadata, $metadata);
            $this->assertEquals(count($originalMetadata['A']['rules']) - 7, count($metadata['A']['rules']));

            ModelMetadataUtil::removeAttribute('A', 'newRelation3Cstm');
            $metadata = A::getMetadata();
            $this->assertNotEquals($originalMetadata, $metadata);
            $this->assertEquals(count($originalMetadata['A']['rules']) - 7, count($metadata['A']['rules']));
        }

        /**
         * @depends testRemoveAttributes
         */
        public function testUsingNonRequiredCustomMemberWithoutDefaultValue()
        {
            $a = new A();
            $this->assertFalse($a->isAttribute('isSilly'));
            unset($a);
            $attributeLabels  = array('en' => 'isSilly');
            ModelMetadataUtil::addOrUpdateMember('A', 'isSilly', $attributeLabels,
                null, null, null, null, null, false, false, null, array());

            $a = new A();
            $a->a = 1;
            $this->assertTrue($a->isAttribute('isSillyCstm'));
            $this->assertTrue($a->validate());
            $this->assertNull($a->isSillyCstm);
            unset($a);

            ModelMetadataUtil::removeAttribute('A', 'isSillyCstm');
        }

        /**
         * @depends testUsingNonRequiredCustomMemberWithoutDefaultValue
         */
        public function testUsingNonRequiredCustomMemberWithDefaultValue()
        {
            $a = new A();
            $this->assertFalse($a->isAttribute('isSilly'));
            unset($a);
            $attributeLabels  = array('en' => 'isSilly');
            ModelMetadataUtil::addOrUpdateMember('A', 'isSilly', $attributeLabels, 'no',
                null, null, null, null, false, false, null, array());

            $a = new A();
            $a->a = 1;
            $this->assertTrue($a->isAttribute('isSillyCstm'));
            $this->assertTrue($a->validate());
            // Remember, yii default values are applied
            // on validation if there is no value set.
            $this->assertEquals('no', $a->isSillyCstm);
            unset($a);

            ModelMetadataUtil::removeAttribute('A', 'isSillyCstm');
        }

        /**
         * @depends testUsingNonRequiredCustomMemberWithDefaultValue
         */
        public function testUsingRequiredCustomMemberWithoutDefaultValue()
        {
            $a = new A();
            $this->assertFalse($a->isAttribute('isSilly'));
            unset($a);
            $attributeLabels  = array('en' => 'isSilly');
            ModelMetadataUtil::addOrUpdateMember('A', 'isSilly', $attributeLabels,
                null, null, null, null, null, true, true, null, array());

            $a = new A();
            $a->a = 1;
            $this->assertTrue($a->isAttribute('isSillyCstm'));
            $this->assertFalse($a->validate());
            $a->isSillyCstm = 'yes';
            $this->assertTrue ($a->validate());
            unset($a);

            ModelMetadataUtil::removeAttribute('A', 'isSillyCstm');
        }

        /**
         * @depends testUsingRequiredCustomMemberWithoutDefaultValue
         */
        public function testUsingRequiredCustomMemberWithDefaultValue()
        {
            $a = new A();
            $this->assertFalse($a->isAttribute('isSilly'));
            unset($a);
            $attributeLabels  = array('en' => 'isSilly');
            ModelMetadataUtil::addOrUpdateMember('A', 'isSilly', $attributeLabels,
                'no', null, null, null, null, true, true, null, array());

            $a = new A();
            $a->a = 1;
            $this->assertTrue($a->isAttribute('isSillyCstm'));
            $this->assertTrue($a->validate());
            // Remember, yii default values are applied
            // on validation if there is no value set.
            $this->assertEquals('no', $a->isSillyCstm);
            unset($a);

            ModelMetadataUtil::removeAttribute('A', 'isSillyCstm');
        }

        /**
         * @depends testUsingRequiredCustomMemberWithDefaultValue
         */
        public function testUsingNonRequiredCustomMemberWithMaxLength()
        {
            $a = new A();
            $this->assertFalse($a->isAttribute('isSilly'));
            unset($a);
            $attributeLabels  = array('en' => 'Is Silly');
            ModelMetadataUtil::addOrUpdateMember('A', 'isSilly', $attributeLabels,
                null, 10, null, null, null, false, false, null, array());

            $a = new A();
            $a->a = 1;
            $this->assertTrue($a->isAttribute('isSillyCstm'));
            $this->assertTrue($a->validate());
            $a->isSillyCstm = 'abcdefghij';
            $this->assertTrue($a->validate());
            $a->isSillyCstm = 'abcdefghijk';
            $this->assertFalse($a->validate());
            $errors = $a->getErrors();
            $this->assertEquals(1, count($errors));
            $this->assertEquals('Is Silly is too long (maximum is 10 characters).', $errors['isSillyCstm'][0]);
            unset($a);

            ModelMetadataUtil::removeAttribute('A', 'isSillyCstm');
        }

        /**
         * @depends testRemoveAttributes
         */
        public function testUsingNonRequiredCustomFieldRelationWithWithoutDefaultValue()
        {
            $a = new A();
            $this->assertFalse($a->isAttribute('fruit'));
            unset($a);

            $appleCustomField = new CustomField();
            $appleCustomField->value = 'apple';
            $appleCustomField->data = CustomFieldData::getByName('Fruit');
            $this->assertTrue($appleCustomField->save());

            $fruits = array('apple', 'orange', 'grape', 'banana', 'pear');
            $attributeLabels  = array('en' => 'fruit');
            ModelMetadataUtil::addOrUpdateCustomFieldRelation('A', 'fruit', $attributeLabels,
                null, false, false, 'DropDown', 'Fruit', $fruits);

            $a = new A();
            $a->a = 1;
            $this->assertTrue($a->isAttribute('fruitCstm'));
            $this->assertTrue($a->validate());
            unset($a);

            ModelMetadataUtil::removeAttribute('A', 'fruitCstm');
        }

        /**
         * @depends testUsingNonRequiredCustomFieldRelationWithWithoutDefaultValue
         */
        public function testUsingNonRequiredCustomFieldRelationWithDefaultValue()
        {
            $a = new A();
            $this->assertFalse($a->isAttribute('fruit'));
            unset($a);

            $appleCustomField = new CustomField();
            $appleCustomField->value = 'apple';
            $appleCustomField->data = CustomFieldData::getByName('Fruit');
            $this->assertTrue($appleCustomField->save());
            $attributeLabels  = array('en' => 'fruit');
            ModelMetadataUtil::addOrUpdateCustomFieldRelation('A', 'fruit', $attributeLabels,
                $appleCustomField, false, false, 'DropDown', 'Fruit', null, null, 'CustomField', false);

            $a = new A();
            $a->a = 1;
            $this->assertTrue($a->isAttribute('fruitCstm'));
            $this->assertTrue($a->validate());
            $this->assertEquals('apple', $a->fruitCstm->value);
            $a->fruitCstm->value = '';
            $this->assertTrue($a->validate());
            unset($a);

            ModelMetadataUtil::removeAttribute('A', 'fruitCstm');
        }

        /**
         * @depends testUsingNonRequiredCustomFieldRelationWithDefaultValue
         */
        public function testUsingRequiredCustomFieldRelationWithWithoutDefaultValue()
        {
            $a = new A();
            $this->assertFalse($a->isAttribute('fruit'));
            unset($a);

            $appleCustomField = new CustomField();
            $appleCustomField->value = 'apple';
            $appleCustomField->data = CustomFieldData::getByName('Fruit');
            $this->assertTrue($appleCustomField->save());
            $attributeLabels  = array('en' => 'Fruit');
            ModelMetadataUtil::addOrUpdateCustomFieldRelation('A', 'fruit', $attributeLabels,
                null, true, false, 'DropDown', 'Fruit', null, null, 'CustomField', false);

            $a = new A();
            $a->a = 1;
            $this->assertTrue($a->isAttribute('fruitCstm'));
            $this->assertFalse($a->validate());
            $errors = $a->getErrors();
            $this->assertEquals(1, count($errors));
            $this->assertEquals('Fruit cannot be blank.', $errors['fruitCstm'][0]);
            $a->fruitCstm->value = 'apple';
            $this->assertTrue($a->validate());
            $this->assertEquals('apple', $a->fruitCstm->value);
            unset($a);

            //Now test setting from post
            $fakePost = array('a' => '1', 'fruitCstm' => array('value' => '')); //using empty string, not null for value since
                                                                            //this properly mimics the post value for empty.
            $a = new A();
            $a->setAttributes($fakePost);
            $this->assertFalse($a->validate());
            $errors = $a->getErrors();
            $this->assertEquals(1, count($errors));
            $this->assertEquals('Fruit cannot be blank.', $errors['fruitCstm'][0]);

            ModelMetadataUtil::removeAttribute('A', 'fruitCstm');
        }

        /**
         * @depends testUsingRequiredCustomFieldRelationWithWithoutDefaultValue
         */
        public function testUsingRequiredCustomFieldRelationWithDefaultValue()
        {
            $a = new A();
            $this->assertFalse($a->isAttribute('fruit'));
            unset($a);

            $appleCustomField = new CustomField();
            $appleCustomField->value = 'apple';
            $appleCustomField->data = CustomFieldData::getByName('Fruit');
            $this->assertTrue($appleCustomField->save());
            $attributeLabels  = array('en' => 'fruit');
            ModelMetadataUtil::addOrUpdateCustomFieldRelation('A', 'fruit', $attributeLabels,
                $appleCustomField, true, false, 'DropDown', 'Fruit', null, null, 'CustomField', false);

            $a = new A();
            $a->a = 1;
            $this->assertTrue($a->isAttribute('fruitCstm'));
            $this->assertTrue($a->validate());
            // Remember, yii default values are applied
            // on validation if there is no value set.
            $this->assertEquals('apple', $a->fruitCstm->value);
            unset($a);

            ModelMetadataUtil::removeAttribute('A', 'fruit');
        }

        /**
         * @depends testUsingRequiredCustomFieldRelationWithDefaultValue
         */
        public function testAttributeLabelsMergeCorrectlyWithExistingData()
        {
            //Testing addOrUpdateMember merges correctly.
            $originalMetadata = A::getMetadata();
            $this->assertEquals($originalMetadata['A']['labels']['newMember2Cstm'], array('en' => 'newMember2'));
            $attributeLabels  = array('fr' => 'somethingDifferent');
            ModelMetadataUtil::addOrUpdateMember('A', 'newMember2', $attributeLabels,
                null, null, null, null, null, false, false, 'Text', array());
            $metadata = A::getMetadata();
            $this->assertEquals($metadata['A']['labels']['newMember2Cstm'],
                                array('en' => 'newMember2', 'fr' => 'somethingDifferent'));

             //Testing addOrUpdateRelation merges correctly.
             //todo: this is covered though by addOrUpdateCustomFieldRelation, but a test for this specifically woulud
             //be ideal.

             //Testing addOrUpdateCustomFieldRelation merges correctly.
            $originalMetadata = A::getMetadata();
            $this->assertEquals($originalMetadata['A']['labels']['fruitCstm'], array('en' => 'fruit'));
            $attributeLabels  = array('fr' => 'somethingDifferent2');
            $appleCustomField = new CustomField();
            $appleCustomField->value = 'apple';
            $appleCustomField->data = CustomFieldData::getByName('Fruit');
            $this->assertTrue($appleCustomField->save());
            ModelMetadataUtil::addOrUpdateCustomFieldRelation('A', 'fruit', $attributeLabels,
                $appleCustomField, true, false, 'DropDown', 'Fruit', null, null, 'CustomField', false);
            $metadata = A::getMetadata();
            $this->assertEquals($metadata['A']['labels']['fruitCstm'],
                                array('en' => 'fruit', 'fr' => 'somethingDifferent2'));
        }

        /**
         * @depends testAttributeLabelsMergeCorrectlyWithExistingData
         */
        public function testSavingCustomFieldDataLabels()
        {
            $a = new A();
            $this->assertTrue($a->isAttribute('fruitCstm'));
            unset($a);

            $appleCustomField = new CustomField();
            $appleCustomField->value = 'apple';
            $appleCustomField->data = CustomFieldData::getByName('Fruit');
            $this->assertTrue($appleCustomField->save());
            $attributeLabels  = array('en' => 'fruit');
            ModelMetadataUtil::addOrUpdateCustomFieldRelation('A', 'fruit', $attributeLabels,
                $appleCustomField, true, false, 'DropDown', 'Fruit', array('apple', 'grape', 'orange'),
                array('fr' => array('appleFr', 'grapeFr', 'orangeFr'), 'de' => array('', 'grape', '')), 'CustomField', false);

            $a = new A();
            $a->a = 1;
            $this->assertTrue($a->isAttribute('fruitCstm'));
            $this->assertTrue($a->validate());
            $this->assertEquals('apple', $a->fruitCstm->value);
            $compareData = array('fr' => array('appleFr', 'grapeFr', 'orangeFr'), 'de' => array('', 'grape', ''));
            $this->assertEquals($compareData, unserialize($a->fruitCstm->data->serializedLabels));
            unset($a);
            ModelMetadataUtil::removeAttribute('A', 'fruitCstm');
        }
    }
?>
