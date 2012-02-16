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

    class DropDownDependencyCustomFieldMappingTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $values = array(
                'Item 1',
                'Item 2',
                'Item 3',
            );
            $labels = array(
                'fr' => array('Item 1 fr',
                              'Item 2 fr',
                              'Item 3 fr'),
            );
            $customFieldData = CustomFieldData::getByName('Items');
            $customFieldData->serializedData   = serialize($values);
            $customFieldData->serializedLabels = serialize($labels);
            $saved = $customFieldData->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
        }

        public function testGetSetAndMethods()
        {
            $availableCustomFieldAttributes = array('a', 'test', 'b');
            $mappingData                    = array('Item 1' => 'parentItem1', 'Item2' => null);
            $customFieldData                = CustomFieldData::getByName('Items');
            $mapping = new DropDownDependencyCustomFieldMapping(3, 'test',
                                    $availableCustomFieldAttributes,
                                    $customFieldData,
                                    $mappingData);

            $this->assertEquals(true,                            $mapping->allowsAttributeSelection());
            $mapping->doNotAllowAttributeSelection();
            $this->assertEquals(false,                           $mapping->allowsAttributeSelection());
            $this->assertEquals('Level: 4',                      $mapping->getTitle());
            $this->assertEquals(3,                               $mapping->getPosition());
            $this->assertEquals('test',                          $mapping->getAttributeName());
            $this->assertEquals($availableCustomFieldAttributes, $mapping->getAvailableCustomFieldAttributes());
            $this->assertEquals('First select level 3',          $mapping->getSelectHigherLevelFirstMessage());
            $this->assertEquals($customFieldData,                $mapping->getCustomFieldData());
            $this->assertEquals('parentItem1',                   $mapping->getMappingDataSelectedParentValueByValue('Item 1'));
            $this->assertEquals(null,                            $mapping->getMappingDataSelectedParentValueByValue('Item 2'));
        }
    }
?>