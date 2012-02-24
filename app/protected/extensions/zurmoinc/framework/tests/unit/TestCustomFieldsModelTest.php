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

    class TestCustomFieldsModelTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $multiSelectValues = array(
                'Multi 1',
                'Multi 2',
                'Multi 3',
            );
            $customFieldData = CustomFieldData::getByName('MultipleSomethings');
            $customFieldData->serializedData = serialize($multiSelectValues);
            assert($customFieldData->save());

            $tagCloudValues = array(
                'Cloud 1',
                'Cloud 2',
                'Cloud 3',
            );
            $customFieldData = CustomFieldData::getByName('TagCloud');
            $customFieldData->serializedData = serialize($tagCloudValues);
            assert($customFieldData->save());
        }

        public function testMultiSelectAndTagCloudRelationships()
        {
            $testModel = new TestCustomFieldsModel();

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 1';
            $testModel->multipleSomethings->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 3';
            $testModel->multipleSomethings->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 2';
            $testModel->tagCloud->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 3';
            $testModel->tagCloud->values->add($customFieldValue);

            $this->assertEquals(2, count($testModel->multipleSomethings->values));
            $this->assertEquals('Multi 1', $testModel->multipleSomethings->values[0]);
            $this->assertEquals('Multi 3', $testModel->multipleSomethings->values[1]);

            $this->assertEquals(2, count($testModel->tagCloud->values));
            $this->assertEquals('Cloud 2', $testModel->tagCloud->values[0]);
            $this->assertEquals('Cloud 2', $testModel->tagCloud->values[1]);
        }
    }
