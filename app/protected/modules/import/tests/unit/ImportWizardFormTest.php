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
                                       'All required fields must be mapped or added: Owner, Last Name, String'),
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
                'mappingData' => array('You can only map each field once.',
                                       'All required fields must be mapped or added: Owner, Last Name'),
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
                'mappingData' => array('All required fields must be mapped or added: Owner, String',
                                       'The following field is mapped more than once. Last Name'),
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