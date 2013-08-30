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

    class CustomFieldsInstructionDataTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testSetAndGetMissingValuesToAdd()
        {
            $customFieldsInstructionData = new CustomFieldsInstructionData();
            $this->assertEquals(array(), $customFieldsInstructionData->getMissingValuesToAdd());
            $missingCustomFieldValues = array('a', 'b', 'c');
            $customFieldsInstructionData->addMissingValuesByColumnName($missingCustomFieldValues, 'column_0');
            $compareData = array('column_0' => array('a', 'b', 'c'));
            $this->assertEquals($compareData, $customFieldsInstructionData->getMissingValuesToAdd());
        }

        public function testSetAndGetMissingValuesToMap()
        {
            $customFieldsInstructionData = new CustomFieldsInstructionData();
            $this->assertEquals(array(), $customFieldsInstructionData->getMissingValuesToMap());
            $instructionsData[CustomFieldsInstructionData::ADD_MISSING_VALUES] = array('a', 'b', 'c');
            $instructionsData[CustomFieldsInstructionData::MAP_MISSING_VALUES] = array('d', 'e', 'f');
            $customFieldsInstructionData->addByInstructionsDataAndColumnName($instructionsData, 'column_0');
            $compareData = array('column_0' => array('a', 'b', 'c'));
            $this->assertEquals($compareData, $customFieldsInstructionData->getMissingValuesToAdd());
            $compareData = array('column_0' => array('d', 'e', 'f'));
            $this->assertEquals($compareData, $customFieldsInstructionData->getMissingValuesToMap());
        }

        public function testResolveForNewData()
        {
            $customFieldsInstructionData = new CustomFieldsInstructionData();
            $instructionsData[CustomFieldsInstructionData::ADD_MISSING_VALUES] = array('a', 'b', 'c');
            $instructionsData[CustomFieldsInstructionData::MAP_MISSING_VALUES] = array('d' => 'dx', 'e' => 'ex', 'f' => 'fx');
            $customFieldsInstructionData->addByInstructionsDataAndColumnName($instructionsData, 'column_0');

            $newCustomFieldsInstructionData = new CustomFieldsInstructionData();
            $instructionsData[CustomFieldsInstructionData::ADD_MISSING_VALUES] = array('a2', 'b', 'c2');
            $instructionsData[CustomFieldsInstructionData::MAP_MISSING_VALUES] = array('d2' => 'd2x', 'e' => 'ex', 'f2' => 'f2x');
            $newCustomFieldsInstructionData->addByInstructionsDataAndColumnName($instructionsData, 'column_0');

            $customFieldsInstructionData->resolveForNewData($newCustomFieldsInstructionData);
            $compareData = array('column_0' => array('a', 'b', 'c', 'a2', 'c2'));
            $this->assertEquals($compareData, $customFieldsInstructionData->getMissingValuesToAdd());
            $compareData = array('column_0' => array('d' => 'dx', 'd2' => 'd2x', 'e' => 'ex', 'f' => 'fx', 'f2' => 'f2x'));
            $this->assertEquals($compareData, $customFieldsInstructionData->getMissingValuesToMap());

            $this->assertTrue($customFieldsInstructionData->hasDataByColumnName('column_0'));
            $this->assertFalse($customFieldsInstructionData->hasDataByColumnName('column_1'));
            $compareData = array(CustomFieldsInstructionData::ADD_MISSING_VALUES => array('a', 'b', 'c', 'a2', 'c2'),
                                 CustomFieldsInstructionData::MAP_MISSING_VALUES => array('d'  => 'dx', 'd2' => 'd2x',
                                                                                         'e'  => 'ex', 'f' => 'fx',
                                                                                         'f2' => 'f2x'));
            $this->assertEquals($compareData, $customFieldsInstructionData->getDataByColumnName('column_0'));
        }
    }
?>