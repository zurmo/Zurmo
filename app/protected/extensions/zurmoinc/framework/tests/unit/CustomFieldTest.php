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

    class CustomFieldTest extends BaseTest
    {
        public function testSaveAndLoadCustomFieldData()
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
         * @depends testSaveAndLoadCustomFieldData
         */
        public function testCustomField()
        {
            $customFieldData = CustomFieldData::getByName('Items');
            $customField = new CustomField();
            $customField->value = $customFieldData->defaultValue;
            $customField->data  = $customFieldData;
            $this->assertTrue($customField->save());

            $values = unserialize($customField->data->serializedData);
            $customField->value = $values[0];
            $this->assertTrue($customFieldData->save());
            unset($customFieldData);

            $customFieldData = CustomFieldData::getByName('Items');
            $this->assertEquals('Item 1', $customField->value);
        }

        /**
         * @depends testCustomField
         */
        public function testSetAttributesWithPostForCustomField()
        {
            $values = array(
                'Automotive',
                'Adult Entertainment',
                'Financial Services',
                'Mercenaries & Armaments',
            );
            $industryFieldData = CustomFieldData::getByName('Industries');
            $industryFieldData->defaultValue = $values[1];
            $industryFieldData->serializedData = serialize($values);
            $this->assertTrue($industryFieldData->save());

            $model = new TestCustomFieldsModel();
            $this->assertEquals($values[1], $model->industry->value);
            $this->assertTrue($model->validate());

            $_FAKEPOST = array(
                'industry' => array(
                    'value' => $values[2],
                ),
            );

            $model->setAttributes($_FAKEPOST);
            $this->assertEquals($values[2], $model->industry->value);
        }

        /**
         * @depends testSetAttributesWithPostForCustomField
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

            $customField = new CustomField();
            $customField->value = 'A';
            $customField->data  = $customFieldData;
            $this->assertTrue($customField->save());

            $customField = new CustomField();
            $customField->value = 'B';
            $customField->data  = $customFieldData;
            $this->assertTrue($customField->save());

            $customField = new CustomField();
            $customField->value = 'C';
            $customField->data  = $customFieldData;
            $this->assertTrue($customField->save());

            $customField = new CustomField();
            $customField->value = 'C';
            $customField->data  = $customFieldData;
            $this->assertTrue($customField->save());

            $quote                    = DatabaseCompatibilityUtil::getQuote();
            $customFieldTableName     = RedBeanModel::getTableName('CustomField');
            $valueAttributeColumnName = 'value';
            $dataAttributeColumnName  = RedBeanModel::getForeignKeyName('CustomField', 'data');
            $sql = "select id from {$quote}{$customFieldTableName}{$quote} where {$quote}{$dataAttributeColumnName}{$quote} = {$id}";
            $ids = R::getCol($sql);
            $beans = R::batch($customFieldTableName, $ids);
            $customFields = RedBeanModel::makeModels($beans, 'CustomField');
            $this->assertEquals(4, count($customFields));
            $sql  = "select id from {$quote}{$customFieldTableName}{$quote} where {$quote}{$dataAttributeColumnName}{$quote} = {$id} ";
            $sql .= "and {$quote}{$valueAttributeColumnName}{$quote} = 'B'";
            $this->assertEquals(1, count(R::getCol($sql)));
            $sql  = "select id from {$quote}{$customFieldTableName}{$quote} where {$quote}{$dataAttributeColumnName}{$quote} = {$id} ";
            $sql .= "and {$quote}{$valueAttributeColumnName}{$quote} = 'C'";
            $this->assertEquals(2, count(R::getCol($sql)));
            $sql  = "select id from {$quote}{$customFieldTableName}{$quote} where {$quote}{$dataAttributeColumnName}{$quote} = {$id} ";
            $sql .= "and {$quote}{$valueAttributeColumnName}{$quote} = 'E'";
            $this->assertEquals(0, count(R::getCol($sql)));
            CustomField::updateValueByDataIdAndOldValueAndNewValue($id, 'C', 'E');
            $sql  = "select id from {$quote}{$customFieldTableName}{$quote} where {$quote}{$dataAttributeColumnName}{$quote} = {$id} ";
            $sql .= "and {$quote}{$valueAttributeColumnName}{$quote} = 'B'";
            $this->assertEquals(1, count(R::getCol($sql)));
            $sql .= "and {$quote}{$valueAttributeColumnName}{$quote} = 'C'";
            $this->assertEquals(0, count(R::getCol($sql)));
            $sql  = "select id from {$quote}{$customFieldTableName}{$quote} where {$quote}{$dataAttributeColumnName}{$quote} = {$id} ";
            $sql .= "and {$quote}{$valueAttributeColumnName}{$quote} = 'E'";
            $this->assertEquals(2, count(R::getCol($sql)));
        }
    }
