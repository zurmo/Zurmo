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

    class ImportWizardMappingViewUtilTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testResolveMappingDataForView()
        {
            $testTableName = 'testimporttable';
            $this->assertTrue(ImportTestHelper::createTempTableByFileNameAndTableName('importTest.csv', $testTableName));
            $mappingData = ImportMappingUtil::makeMappingDataByTableName($testTableName);
            $compareData = array(
                'column_0' => array('type' => 'importColumn',   'attributeNameOrDerivedType' => null,
                                    'mappingRulesData' => null),
                'column_1' => array('type' => 'importColumn',   'attributeNameOrDerivedType' => null,
                                    'mappingRulesData' => null),
                'column_2' => array('type' => 'importColumn',   'attributeNameOrDerivedType' =>null,
                                    'mappingRulesData' => null),
            );
            $this->assertEquals($compareData, $mappingData);
            $mappingData['column_3'] = array('type' => 'extraColumn', 'attributeNameOrDerivedType' => 'xyz',
                                             'mappingRulesData' => null);
            $mappingDataMetadata = ImportWizardMappingViewUtil::
                                   resolveMappingDataForView($mappingData, $testTableName, true);
            $compareData = array(
                'column_0' => array('type'                       => 'importColumn',
                                    'attributeNameOrDerivedType' => null,
                                    'mappingRulesData'           => null,
                                    'headerValue'                => 'name',
                                    'sampleValue' 			     => 'abc'),
                'column_1' => array('type' => 'importColumn',
                                    'attributeNameOrDerivedType' => null,
                                    'mappingRulesData'           => null,
                                    'headerValue'                => 'phone',
                                    'sampleValue' 			     => '123'),
                'column_2' => array('type'                       => 'importColumn',
                                    'attributeNameOrDerivedType' => null,
                                    'mappingRulesData'           => null,
                                    'headerValue'                => 'industry',
                                    'sampleValue' 			     => 'a'),
                'column_3' => array('type'                       => 'extraColumn',
                                    'attributeNameOrDerivedType' => 'xyz',
                                    'mappingRulesData'           => null,
                                    'headerValue'                => null,
                                    'sampleValue' 			     => null),
            );
            $this->assertEquals($compareData, $mappingDataMetadata);
            $mappingDataMetadata = ImportWizardMappingViewUtil::
                                   resolveMappingDataForView($mappingData, $testTableName, false);
            $compareData = array(
                'column_0' => array('type' => 'importColumn',
                                    'attributeNameOrDerivedType' => null,
                                    'mappingRulesData'           => null,
                                    'sampleValue' 			     => 'name'),
                'column_1' => array('type' => 'importColumn',
                                    'attributeNameOrDerivedType' => null,
                                    'mappingRulesData'           => null,
                                    'sampleValue' 			     => 'phone'),
                'column_2' => array('type' => 'importColumn',
                                    'attributeNameOrDerivedType' => null,
                                    'mappingRulesData'           => null,
                                    'sampleValue' 			     => 'industry'),
                'column_3' => array('type'                       => 'extraColumn',
                                    'attributeNameOrDerivedType' => 'xyz',
                                    'mappingRulesData'           => null,
                                    'sampleValue' 			     => null),
            );
            $this->assertEquals($compareData, $mappingDataMetadata);
        }
    }
?>