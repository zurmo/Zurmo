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

    class ImportToCustomFieldsInstructionDataAdapterTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testAppendCustomFieldsInstructionDataForAddMissingValues()
        {
            $import  = new Import();
            $customFieldsInstructionData = new CustomFieldsInstructionData();
            $this->assertNull($import->serializedData);
            $missingCustomFieldValues    = array('a', 'b', 'c');
            $customFieldsInstructionData->addMissingValuesByColumnName($missingCustomFieldValues, 'column_0');

            $adapter = new ImportToCustomFieldsInstructionDataAdapter($import);
            $adapter->appendCustomFieldsInstructionData($customFieldsInstructionData);
            $compareData = array('column_0' => array('customFieldsInstructionData' =>
                            array(CustomFieldsInstructionData::ADD_MISSING_VALUES => array('a', 'b', 'c'))));
            $unserializedData = unserialize($import->serializedData);
            $this->assertEquals($compareData, $unserializedData['mappingData']);

            //Now append the existing and that it doesn't duplicate existing values
            $customFieldsInstructionData = new CustomFieldsInstructionData();
            $missingCustomFieldValues    = array('a', 'd');
            $customFieldsInstructionData->addMissingValuesByColumnName($missingCustomFieldValues, 'column_0');
            $adapter = new ImportToCustomFieldsInstructionDataAdapter($import);
            $adapter->appendCustomFieldsInstructionData($customFieldsInstructionData);
            $compareData = array('column_0' => array('customFieldsInstructionData' =>
                            array(CustomFieldsInstructionData::ADD_MISSING_VALUES => array('a', 'b', 'c', 'd'))));
            $unserializedData = unserialize($import->serializedData);
            $this->assertEquals($compareData, $unserializedData['mappingData']);
        }

        /**
         * @depends testAppendCustomFieldsInstructionDataForAddMissingValues
         */
        public function testAppendCustomFieldsInstructionDataForMapMissingValues()
        {
            $import  = new Import();
            $customFieldsInstructionData = new CustomFieldsInstructionData();
            $this->assertNull($import->serializedData);
            $missingCustomFieldValues    = array(CustomFieldsInstructionData::MAP_MISSING_VALUES =>
                                            array('a' => 'b2', 'b' => 'b2', 'c' => 'c2'));
            $customFieldsInstructionData->addByInstructionsDataAndColumnName($missingCustomFieldValues, 'column_0');

            $adapter = new ImportToCustomFieldsInstructionDataAdapter($import);
            $adapter->appendCustomFieldsInstructionData($customFieldsInstructionData);
            $compareData = array('column_0' => array('customFieldsInstructionData' =>
                            array(CustomFieldsInstructionData::MAP_MISSING_VALUES => array('a' => 'b2', 'b' => 'b2', 'c' => 'c2'))));
            $unserializedData = unserialize($import->serializedData);
            $this->assertEquals($compareData, $unserializedData['mappingData']);

            //Now append the existing and that it doesn't duplicate existing values
            $customFieldsInstructionData = new CustomFieldsInstructionData();
            $missingCustomFieldValues    = array(CustomFieldsInstructionData::MAP_MISSING_VALUES =>
                                            array('a' => 'a2', 'd' => 'd2'));
            $customFieldsInstructionData->addByInstructionsDataAndColumnName($missingCustomFieldValues, 'column_0');
            $adapter = new ImportToCustomFieldsInstructionDataAdapter($import);
            $adapter->appendCustomFieldsInstructionData($customFieldsInstructionData);
            $compareData = array('column_0' => array('customFieldsInstructionData' =>
                            array(CustomFieldsInstructionData::MAP_MISSING_VALUES =>
                                array('a' => 'b2', 'b' => 'b2', 'c' => 'c2', 'd' => 'd2'))));
            $unserializedData = unserialize($import->serializedData);
            $this->assertEquals($compareData, $unserializedData['mappingData']);
        }
    }
?>