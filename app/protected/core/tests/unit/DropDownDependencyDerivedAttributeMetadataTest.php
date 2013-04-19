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

    class DropDownDependencyDerivedAttributeMetadataTest extends BaseTest
    {
        public function testMetadata()
        {
            $metadata = new DropDownDependencyDerivedAttributeMetadata();
            $metadata->setScenario('nonAutoBuild');
            $metadata->name = 'someName';
            $metadata->modelClassName     = 'Whatever';
            $metadata->serializedMetadata = serialize(array('stuff', 1, 'attributeLabels' => array()));
            $this->assertTrue($metadata->save());
            unset($metadata);
            $metadata = DropDownDependencyDerivedAttributeMetadata::getByNameAndModelClassName('someName', 'Whatever');
            $metadata->setScenario('nonAutoBuild');
            $this->assertEquals('someName', $metadata->name);
            $this->assertEquals('Whatever', $metadata->modelClassName);
            $this->assertEquals('a:3:{i:0;s:5:"stuff";i:1;i:1;s:15:"attributeLabels";a:0:{}}', $metadata->serializedMetadata);

            $metadata->serializedMetadata = serialize(array('stuffx', 1, 'attributeLabels' => array()));
            $this->assertTrue($metadata->save());
        }

        /**
         * @depends testMetadata
         */
        public function testSavingMetadataWithSameName()
        {
            $metadata = new DropDownDependencyDerivedAttributeMetadata();
            $metadata->setScenario('nonAutoBuild');
            $metadata->name = 'someName';
            $metadata->modelClassName     = 'Whatever';
            $metadata->serializedMetadata = serialize(array('stuff', 1, 'attributeLabels' => array()));
            $this->assertFalse($metadata->save());

            $metadata = new DropDownDependencyDerivedAttributeMetadata();
            $metadata->setScenario('nonAutoBuild');
            $metadata->name = 'someName2';
            $metadata->modelClassName     = 'Whatever2';
            $metadata->serializedMetadata = serialize(array('stuff', 1, 'attributeLabels' => array()));
            $this->assertTrue($metadata->save());
        }

        /**
         * @depends testSavingMetadataWithSameName
         */
        public function testGetAllByModelClassName()
        {
            $models = DropDownDependencyDerivedAttributeMetadata::getAllByModelClassName('Whatever');
            $this->assertEquals(1, count($models));
            $this->assertEquals('someName', $models[0]->name);
            $models = DropDownDependencyDerivedAttributeMetadata::getAllByModelClassName('Whatever2');
            $this->assertEquals(1, count($models));
            $this->assertEquals('someName2', $models[0]->name);
        }

        public function testGetUsedAttributeNames()
        {
            $mappingData = array(array('attributeName' => 'a'),
                                 array('attributeName' => 'b'),
                                 array('attributeName' => 'c'));
            $metadata = new DropDownDependencyDerivedAttributeMetadata();
            $metadata->setScenario('nonAutoBuild');
            $metadata->name = 'someName3';
            $metadata->modelClassName     = 'Whatever2';
            $metadata->serializedMetadata = serialize(array('stuff', 1, 'attributeLabels' => array(),
                                                            'mappingData' => $mappingData));
            $this->assertTrue($metadata->save());

            $usedModelAttributeNames = $metadata->getUsedAttributeNames();
            $this->assertEquals(array('a', 'b', 'c'), $usedModelAttributeNames);
        }
    }
?>