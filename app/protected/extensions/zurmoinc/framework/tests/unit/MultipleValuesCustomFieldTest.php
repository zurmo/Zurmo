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

    class MultipleValuesCustomFieldTest extends BaseTest
    {
        public function testSaveAndLoadMultipleValuesCustomFieldData()
        {
            $values = array(
                'Item 1',
                'Item 2',
                'Item 3',
            );
            $labels = array(
                'fr' => 'Item 1 fr',
                'fr' => 'Item 2 fr',
                'fr' => 'Item 3 fr',
            );
            $customFieldData = CustomFieldData::getByName('Items');
            $customFieldData->serializedData   = serialize($values);
            $customFieldData->serializedLabels = serialize($labels);
            $this->assertTrue($customFieldData->save());
            $id = $customFieldData->id;
            unset($customFieldData);

            $customFieldData = CustomFieldData::getById($id);
            $loadedValues = unserialize($customFieldData->serializedData);
            $loadedLabels = unserialize($customFieldData->serializedLabels);
            $this->assertEquals('Items', $customFieldData->name);
            $this->assertNull  (         $customFieldData->defaultValue);
            $this->assertEquals($values, $loadedValues);
            $this->assertEquals($labels, $loadedLabels);

            $customFieldData->defaultValue = $values[2];
            $this->assertTrue($customFieldData->save());
            unset($customFieldData);
            $customFieldData = CustomFieldData::getById($id);
            $this->assertEquals('Items',  $customFieldData->name);
            $this->assertEquals('Item 3', $customFieldData->defaultValue);
            $this->assertEquals($values,  $loadedValues);
        }

        /**
         * @depends testSaveAndLoadMultipleValuesCustomFieldData
         */
        public function testMultipleValuesCustomField()
        {
            $customFieldData = CustomFieldData::getByName('Items');
            $customField = new MultipleValuesCustomField();
            $customFieldValue1 = new CustomFieldValue();
            $customFieldValue1->value = 'Item 2';
            $customField->values->add($customFieldValue1);
            $customFieldValue2 = new CustomFieldValue();
            $customFieldValue2->value = 'Item 3';
            $customField->values->add($customFieldValue2);
            $customField->data  = $customFieldData;
            $this->assertTrue($customField->save());

            $customFieldId = $customField->id;
            $customField->forget();
            unset($customField);
            $customField = MultipleValuesCustomField::getById($customFieldId);
            $this->assertEquals(2, $customField->values->count());

            $values = unserialize($customField->data->serializedData);
            $customField->values->removeAll();
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Item 1';
            $customField->values->add($customFieldValue);
            $this->assertTrue($customField->save());
            $customField->forget();
            unset($customField);

            $customField = MultipleValuesCustomField::getById($customFieldId);
            $this->assertEquals(1, $customField->values->count());
            $this->assertTrue($customField->values[0]->isSame($customFieldValue));
        }

        /**
         * @depends testMultipleValuesCustomField
         */
        public function testSetAttributesWithPostForMultipleValuesCustomField()
        {
            $values = array(
                'Automotive',
                'Adult Entertainment',
                'Financial Services',
                'Mercenaries & Armaments',
            );
            $industryFieldData = CustomFieldData::getByName('MultipleIndustries');
            $industryFieldData->defaultValue = $values[1];
            $industryFieldData->serializedData = serialize($values);
            $this->assertTrue($industryFieldData->save());

            $model = new TestCustomFieldsModel();
            $this->assertEquals(1, $model->multipleIndustries->values->count());
            $this->assertEquals($values[1], $model->multipleIndustries->values[0]->value);
            $this->assertTrue($model->validate());

            //Test populating with a single value
            $_FAKEPOST = array(
                'multipleIndustries' => array(
                    'values' => array($values[2]),
                ),
            );
            $model->setAttributes($_FAKEPOST);
            $this->assertEquals(1, $model->multipleIndustries->values->count());
            $this->assertEquals($values[2], $model->multipleIndustries->values[0]->value);
            $this->assertEquals('Financial Services', strval($model->multipleIndustries));

            //Now test populating more than one value
            $_FAKEPOST = array(
                'multipleIndustries' => array(
                    'values' => array($values[1], $values[3]),
                ),
            );
            $model->setAttributes($_FAKEPOST);
            $this->assertEquals(2, $model->multipleIndustries->values->count());
            $this->assertEquals($values[1], $model->multipleIndustries->values[0]->value);
            $this->assertEquals($values[3], $model->multipleIndustries->values[1]->value);
            $this->assertEquals('Adult Entertainment, Mercenaries & Armaments', strval($model->multipleIndustries));

            //Test clearing out the values
            $_FAKEPOST = array(
                'multipleIndustries' => array(
                    'values' => array(),
                ),
            );
            $model->setAttributes($_FAKEPOST);
            $this->assertEquals(0, $model->multipleIndustries->values->count());
            $this->assertEquals('(None)', strval($model->multipleIndustries));
        }

        /**
         * @depends testSetAttributesWithPostForMultipleValuesCustomField
         */
        public function testUpdateValueOnCustomFieldRows()
        {
            $values = array(
                'A',
                'B',
                'C',
            );
            $customFieldData = CustomFieldData::getByName('updateItems');
            $customFieldData->serializedData = serialize($values);
            $this->assertTrue($customFieldData->save());
            $id = $customFieldData->id;

            $customField = new MultipleValuesCustomField();
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'A';
            $customField->values->add($customFieldValue);
            $customField->data  = $customFieldData;
            $this->assertTrue($customField->save());

            $customField = new MultipleValuesCustomField();
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'B';
            $customField->values->add($customFieldValue);
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'C';
            $customField->values->add($customFieldValue);
            $customField->data  = $customFieldData;
            $this->assertTrue($customField->save());

            $customField = new MultipleValuesCustomField();
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'C';
            $customField->values->add($customFieldValue);
            $customField->data  = $customFieldData;
            $this->assertTrue($customField->save());

            $customField = new MultipleValuesCustomField();
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'D';
            $customField->values->add($customFieldValue);
            $customField->data  = $customFieldData;
            $this->assertTrue($customField->save());

            $quote                     = DatabaseCompatibilityUtil::getQuote();
            $customFieldTableName      = RedBeanModel::getTableName('MultipleValuesCustomField');
            $baseCustomFieldTableName  = RedBeanModel::getTableName('BaseCustomField');
            $customFieldValueTableName = RedBeanModel::getTableName('CustomFieldValue');
            $valueAttributeColumnName = 'value';
            $dataAttributeColumnName  = RedBeanModel::getForeignKeyName('MultipleValuesCustomField', 'data');
            $sql  = "select {$quote}{$customFieldTableName}{$quote}.id from {$quote}{$customFieldTableName}{$quote} ";
            $sql .= "left join {$quote}{$baseCustomFieldTableName}{$quote} on ";
            $sql .= "{$quote}{$baseCustomFieldTableName}{$quote}.id = ";
            $sql .= "{$quote}{$customFieldTableName}{$quote}.basecustomfield_id ";
            $sql .= "where {$quote}{$dataAttributeColumnName}{$quote} = {$id}";
            $ids = R::getCol($sql);
            $beans = R::batch($customFieldTableName, $ids);
            $customFields = RedBeanModel::makeModels($beans, 'CustomField');
            $this->assertEquals(4, count($customFields));

            $sql  = "select {$quote}{$customFieldTableName}{$quote}.id from {$quote}{$customFieldTableName}{$quote} ";
            $sql .= "left join {$quote}{$baseCustomFieldTableName}{$quote} on ";
            $sql .= "{$quote}{$baseCustomFieldTableName}{$quote}.id = ";
            $sql .= "{$quote}{$customFieldTableName}{$quote}.basecustomfield_id ";
            $sql .= "where {$quote}{$dataAttributeColumnName}{$quote} = {$id} ";
            $sql .= "and (select count(*) from {$quote}{$customFieldValueTableName}{$quote} ";
            $sql .= "where {$quote}{$valueAttributeColumnName}{$quote} IN('B','C') ";
            $sql .= "and {$quote}{$customFieldTableName}{$quote}.id = {$customFieldValueTableName}.{$customFieldTableName}_id)";
            $sql .= " = 2";
            $this->assertEquals(1, count(R::getCol($sql)));
            $sql  = "select {$quote}{$customFieldTableName}{$quote}.id from {$quote}{$customFieldTableName}{$quote} ";
            $sql .= "left join {$quote}{$baseCustomFieldTableName}{$quote} on ";
            $sql .= "{$quote}{$baseCustomFieldTableName}{$quote}.id = ";
            $sql .= "{$quote}{$customFieldTableName}{$quote}.basecustomfield_id ";
            $sql .= "where {$quote}{$dataAttributeColumnName}{$quote} = {$id} ";
            $sql .= "and (select count(*) from {$quote}{$customFieldValueTableName}{$quote} ";
            $sql .= "where {$quote}{$valueAttributeColumnName}{$quote} IN('C') ";
            $sql .= "and {$quote}{$customFieldTableName}{$quote}.id = {$customFieldValueTableName}.{$customFieldTableName}_id)";
            $sql .= " = 1";
            $this->assertEquals(2, count(R::getCol($sql)));
            $sql  = "select {$quote}{$customFieldTableName}{$quote}.id from {$quote}{$customFieldTableName}{$quote} ";
            $sql .= "left join {$quote}{$baseCustomFieldTableName}{$quote} on ";
            $sql .= "{$quote}{$baseCustomFieldTableName}{$quote}.id = ";
            $sql .= "{$quote}{$customFieldTableName}{$quote}.basecustomfield_id ";
            $sql .= "where {$quote}{$dataAttributeColumnName}{$quote} = {$id} ";
            $sql .= "and (select count(*) from {$quote}{$customFieldValueTableName}{$quote} ";
            $sql .= "where {$quote}{$valueAttributeColumnName}{$quote} IN('E') ";
            $sql .= "and {$quote}{$customFieldTableName}{$quote}.id = {$customFieldValueTableName}.{$customFieldTableName}_id)";
            $sql .= " = 1";
            $this->assertEquals(0, count(R::getCol($sql)));

            MultipleValuesCustomField::updateValueByDataIdAndOldValueAndNewValue($id, 'C', 'E');
            $sql  = "select {$quote}{$customFieldTableName}{$quote}.id from {$quote}{$customFieldTableName}{$quote} ";
            $sql .= "left join {$quote}{$baseCustomFieldTableName}{$quote} on ";
            $sql .= "{$quote}{$baseCustomFieldTableName}{$quote}.id = ";
            $sql .= "{$quote}{$customFieldTableName}{$quote}.basecustomfield_id ";
            $sql .= "where {$quote}{$dataAttributeColumnName}{$quote} = {$id} ";
            $sql .= "and (select count(*) from {$quote}{$customFieldValueTableName}{$quote} ";
            $sql .= "where {$quote}{$valueAttributeColumnName}{$quote} IN('B') ";
            $sql .= "and {$quote}{$customFieldTableName}{$quote}.id = {$customFieldValueTableName}.{$customFieldTableName}_id)";
            $sql .= " = 1";
            $this->assertEquals(1, count(R::getCol($sql)));
            $sql  = "select {$quote}{$customFieldTableName}{$quote}.id from {$quote}{$customFieldTableName}{$quote} ";
            $sql .= "left join {$quote}{$baseCustomFieldTableName}{$quote} on ";
            $sql .= "{$quote}{$baseCustomFieldTableName}{$quote}.id = ";
            $sql .= "{$quote}{$customFieldTableName}{$quote}.basecustomfield_id ";
            $sql .= "where {$quote}{$dataAttributeColumnName}{$quote} = {$id} ";
            $sql .= "and (select count(*) from {$quote}{$customFieldValueTableName}{$quote} ";
            $sql .= "where {$quote}{$valueAttributeColumnName}{$quote} IN('C') ";
            $sql .= "and {$quote}{$customFieldTableName}{$quote}.id = {$customFieldValueTableName}.{$customFieldTableName}_id)";
            $sql .= " = 1";
            $this->assertEquals(0, count(R::getCol($sql)));
            $sql  = "select {$quote}{$customFieldTableName}{$quote}.id from {$quote}{$customFieldTableName}{$quote} ";
            $sql .= "left join {$quote}{$baseCustomFieldTableName}{$quote} on ";
            $sql .= "{$quote}{$baseCustomFieldTableName}{$quote}.id = ";
            $sql .= "{$quote}{$customFieldTableName}{$quote}.basecustomfield_id ";
            $sql .= "where {$quote}{$dataAttributeColumnName}{$quote} = {$id} ";
            $sql .= "and (select count(*) from {$quote}{$customFieldValueTableName}{$quote} ";
            $sql .= "where {$quote}{$valueAttributeColumnName}{$quote} IN('E') ";
            $sql .= "and {$quote}{$customFieldTableName}{$quote}.id = {$customFieldValueTableName}.{$customFieldTableName}_id)";
            $sql .= " = 1";
            $this->assertEquals(2, count(R::getCol($sql)));
            $sql  = "select {$quote}{$customFieldTableName}{$quote}.id from {$quote}{$customFieldTableName}{$quote} ";
            $sql .= "left join {$quote}{$baseCustomFieldTableName}{$quote} on ";
            $sql .= "{$quote}{$baseCustomFieldTableName}{$quote}.id = ";
            $sql .= "{$quote}{$customFieldTableName}{$quote}.basecustomfield_id ";
            $sql .= "where {$quote}{$dataAttributeColumnName}{$quote} = {$id} ";
            $sql .= "and (select count(*) from {$quote}{$customFieldValueTableName}{$quote} ";
            $sql .= "where {$quote}{$valueAttributeColumnName}{$quote} IN('B', 'E') ";
            $sql .= "and {$quote}{$customFieldTableName}{$quote}.id = {$customFieldValueTableName}.{$customFieldTableName}_id)";
            $sql .= " = 2";
            $this->assertEquals(1, count(R::getCol($sql)));
        }
    }
