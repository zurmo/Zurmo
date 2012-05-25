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

    class DropDownDependencyDerivedAttributeDesignerUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testUpdateValueInMappingByOldAndNewValue()
        {
            //First create a dependency
            $mappingData = array(array('attributeName' => 'a'),
                                 array('attributeName' => 'b',
                                        'valuesToParentValues' =>
                                         array('b1' => 'a1',
                                               'b2' => 'a2',
                                               'b3' => 'a3',
                                               'b4' => 'a4'
                                         )),
                                 array('attributeName' => 'c',
                                        'valuesToParentValues' =>
                                         array('c1' => 'b1',
                                               'c2' => 'b2',
                                               'c3' => 'b3',
                                               'c4' => 'b4'
                                         )));
            $metadata = new DropDownDependencyDerivedAttributeMetadata();
            $metadata->setScenario('nonAutoBuild');
            $metadata->name               = 'aName';
            $metadata->modelClassName     = 'aModelClassName';
            $metadata->serializedMetadata = serialize(array('attributeLabels' => array('a' => 'b'),
                                                            'mappingData' => $mappingData));
            $this->assertTrue($metadata->save());

            //Change b3 to b3New
            $oldAndNewValuePairs = array('b3' => 'b3New');
            DropDownDependencyDerivedAttributeDesignerUtil::
            updateValueInMappingByOldAndNewValue('aModelClassName', 'b', $oldAndNewValuePairs);

            //Confirm b3 values changed correctly to b3New
            $metadata         = DropDownDependencyDerivedAttributeMetadata::getById($metadata->id);
            $unserializedData = unserialize($metadata->serializedMetadata);

            $compareData      = array(array('attributeName' => 'a'),
                                 array('attributeName' => 'b',
                                        'valuesToParentValues' =>
                                         array('b1' => 'a1',
                                               'b2' => 'a2',
                                               'b3New' => 'a3',
                                               'b4' => 'a4'
                                         )),
                                 array('attributeName' => 'c',
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
            $oldAndNewValuePairs = array('a2' => 'a2New');
            DropDownDependencyDerivedAttributeDesignerUtil::
            updateValueInMappingByOldAndNewValue('aModelClassName', 'a', $oldAndNewValuePairs);

            //Confirm a2 values changed correctly to a2New
            $metadata         = DropDownDependencyDerivedAttributeMetadata::getById($metadata->id);
            $unserializedData = unserialize($metadata->serializedMetadata);

            $compareData      = array(array('attributeName' => 'a'),
                                 array('attributeName' => 'b',
                                        'valuesToParentValues' =>
                                         array('b1' => 'a1',
                                               'b2' => 'a2New',
                                               'b3New' => 'a3',
                                               'b4' => 'a4'
                                         )),
                                 array('attributeName' => 'c',
                                        'valuesToParentValues' =>
                                         array('c1' => 'b1',
                                               'c2' => 'b2',
                                               'c3' => 'b3New',
                                               'c4' => 'b4'
                                         )));
            $this->assertEquals(array('a' => 'b'), $unserializedData['attributeLabels']);
            $this->assertEquals($compareData, $unserializedData['mappingData']);

            //Now change 2 values at once.
            $oldAndNewValuePairs = array('c1' => 'c1New', 'c2' => 'c2New');
            DropDownDependencyDerivedAttributeDesignerUtil::
            updateValueInMappingByOldAndNewValue('aModelClassName', 'c', $oldAndNewValuePairs);

            //Confirm c1, c2 both changed.
            $metadata         = DropDownDependencyDerivedAttributeMetadata::getById($metadata->id);
            $unserializedData = unserialize($metadata->serializedMetadata);

            $compareData      = array(array('attributeName' => 'a'),
                                 array('attributeName' => 'b',
                                        'valuesToParentValues' =>
                                         array('b1' => 'a1',
                                               'b2' => 'a2New',
                                               'b3New' => 'a3',
                                               'b4' => 'a4'
                                         )),
                                 array('attributeName' => 'c',
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
         * @depends testUpdateValueInMappingByOldAndNewValue
         */
        public function testResolveValuesInMappingWhenValueWasRemoved()
        {
            //Remove a1
            $customFieldDataData = array('a2New', 'a3', 'a4');
            DropDownDependencyDerivedAttributeDesignerUtil::
            resolveValuesInMappingWhenValueWasRemoved('aModelClassName', 'a', $customFieldDataData);

            //Confirm a1 has been removed from the mapping.
            $metadata         = DropDownDependencyDerivedAttributeMetadata::
                                getByNameAndModelClassName('aName', 'aModelClassName');
            $unserializedData = unserialize($metadata->serializedMetadata);

            $compareData      = array(array('attributeName' => 'a'),
                                 array('attributeName' => 'b',
                                        'valuesToParentValues' =>
                                         array('b1' => null,
                                               'b2' => 'a2New',
                                               'b3New' => 'a3',
                                               'b4' => 'a4'
                                         )),
                                 array('attributeName' => 'c',
                                        'valuesToParentValues' =>
                                         array('c1New' => 'b1',
                                               'c2New' => 'b2',
                                               'c3' => 'b3New',
                                               'c4' => 'b4'
                                         )));
            $this->assertEquals(array('a' => 'b'), $unserializedData['attributeLabels']);
            $this->assertEquals($compareData, $unserializedData['mappingData']);

            //Remove b4
            $customFieldDataData = array('b1', 'b2', 'b3New');
            DropDownDependencyDerivedAttributeDesignerUtil::
            resolveValuesInMappingWhenValueWasRemoved('aModelClassName', 'b', $customFieldDataData);

            //Confirm b4 has been removed from the mapping.
            $metadata         = DropDownDependencyDerivedAttributeMetadata::
                                getByNameAndModelClassName('aName', 'aModelClassName');
            $unserializedData = unserialize($metadata->serializedMetadata);

            $compareData      = array(array('attributeName' => 'a'),
                                 array('attributeName' => 'b',
                                        'valuesToParentValues' =>
                                         array('b1' => null,
                                               'b2' => 'a2New',
                                               'b3New' => 'a3',
                                         )),
                                 array('attributeName' => 'c',
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