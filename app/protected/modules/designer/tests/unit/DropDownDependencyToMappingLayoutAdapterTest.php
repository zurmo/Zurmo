<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class DropDownDependencyToMappingLayoutAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testGetCustomFieldAttributesNotUsedInOtherDependencyAttributes()
        {
            $adapter             = new DropDownDependencyToMappingLayoutAdapter('TestDropDownDependencyModel', null, 4);
            $attributeNames      = $adapter->getCustomFieldAttributesNotUsedInOtherDependencyAttributes();
            $availableAttributes = array('something1'           => 'Something 1',
                                         'something2'           => 'Something 2',
                                         'something3'           => 'Something 3',
                                         'something4'           => 'Something 4');
            $this->assertEquals($availableAttributes, $attributeNames);

            //Now save a drop down dependency. This means it will use up one of the attribute names.
            $metadata = new DropDownDependencyDerivedAttributeMetadata();
            $metadata->setScenario('nonAutoBuild');
            $metadata->name = 'someName';
            $metadata->modelClassName     = 'TestDropDownDependencyModel';
            $mappingData =  array(array('attributeName' => 'something1'));
            $metadata->serializedMetadata = serialize(array('stuff', 1, 'attributeLabels' => array(),
                                                            'mappingData' => $mappingData));
            $this->assertTrue($metadata->save());

            //Now requery and see that something1 is not available.
            $adapter             = new DropDownDependencyToMappingLayoutAdapter('TestDropDownDependencyModel', null, 4);
            $attributeNames      = $adapter->getCustomFieldAttributesNotUsedInOtherDependencyAttributes();
            $availableAttributes = array('something2'           => 'Something 2',
                                         'something3'           => 'Something 3',
                                         'something4'           => 'Something 4');
            $this->assertEquals($availableAttributes, $attributeNames);
        }

        /**
         * @depends testGetCustomFieldAttributesNotUsedInOtherDependencyAttributes
         */
        public function testMakeDependencyCollectionByMappingData()
        {
            $adapter             = new DropDownDependencyToMappingLayoutAdapter('TestDropDownDependencyModel', null, 4);
            $mappings            = $adapter->makeDependencyCollectionByMappingData(array());
            $this->assertEquals(4, count($mappings));

            $adapter             = new DropDownDependencyToMappingLayoutAdapter('TestDropDownDependencyModel', 'someName', 4);
            $mappingData         = array(array('attributeName' => 'something1'));
            $mappings            = $adapter->makeDependencyCollectionByMappingData($mappingData);
            $this->assertEquals(4, count($mappings));
            $this->assertEquals('something1', $mappings[0]->getAttributeName());
            $this->assertEquals(0,          $mappings[0]->getPosition());
        }
    }
?>