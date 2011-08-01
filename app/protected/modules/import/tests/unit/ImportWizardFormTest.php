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

    class ImportWizardFormTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testValidateMappingData()
        {
            $mappingData = array(
                'column_0' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => null, 'mappingRulesData' => null),
                'column_1' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => null, 'mappingRulesData' => null),
                'column_2' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => null, 'mappingRulesData' => null),
            );
            $importWizardForm                  = new ImportWizardForm();
            $importWizardForm->importRulesType = 'ImportModelTestItem';
            $importWizardForm->mappingData     = $mappingData;
            $importWizardForm->validateMappingData('mappingData', array());
            $this->assertTrue($importWizardForm->hasErrors());
            $compareData = array(
                'mappingData' => array('You must map at least one of your import columns.',
                                       'All required attributes must be mapped or added.'),
            );
            $this->assertEquals($compareData, $importWizardForm->getErrors());

            //Show the error mapping the same attribute more than once.
            $importWizardForm->clearErrors();
            $mappingData = array(
                'column_0' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'string', 'mappingRulesData' => null),
                'column_1' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'string', 'mappingRulesData' => null),
            );
            $importWizardForm->mappingData = $mappingData;
            $importWizardForm->validateMappingData('mappingData', array());
            $this->assertTrue($importWizardForm->hasErrors());
            $compareData = array(
                'mappingData' => array('You can only map each attribute once.',
                                       'All required attributes must be mapped or added.'),
            );
            $this->assertEquals($compareData, $importWizardForm->getErrors());

            //Now try to show a failed validation where you map an attribute more than once using a mix between
            //non-derived and derived attributes.
            $importWizardForm->clearErrors();
            $mappingData = array(
                'column_0' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'lastName', 'mappingRulesData' => null),
                'column_1' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'FullName', 'mappingRulesData' => null),
            );
            $importWizardForm->mappingData = $mappingData;
            $importWizardForm->validateMappingData('mappingData', array());
            $this->assertTrue($importWizardForm->hasErrors());
            $compareData = array(
                'mappingData' => array('All required attributes must be mapped or added.',
                                       'The following attribute is mapped more than once. lastName'),
            );
            $this->assertEquals($compareData, $importWizardForm->getErrors());

            //Now show a successful validation.
            $importWizardForm->clearErrors();
            $mappingData = array(
                'column_0' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'lastName', 'mappingRulesData' => null),
                'column_1' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'string', 'mappingRulesData' => null),
                'column_2' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'owner', 'mappingRulesData' => null),
            );
            $importWizardForm->mappingData = $mappingData;
            $importWizardForm->validateMappingData('mappingData', array());
            $this->assertFalse($importWizardForm->hasErrors());
            $this->assertEquals(array(), $importWizardForm->getErrors());
        }
    }
?>