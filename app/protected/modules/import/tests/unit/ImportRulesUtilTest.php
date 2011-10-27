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

    class ImportRulesUtilTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testGetImportRulesTypesForCurrentUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $billy                      = UserTestHelper::createBasicUser('billy');
            $importRulesTypes           = ImportRulesUtil::getImportRulesTypesForCurrentUser();
            $compareData = array(
                'Accounts'      => 'Accounts',
                'Contacts'      => 'Contacts',
                'Leads'         => 'Leads',
                'Meetings'      => 'Meetings',
                'Notes'         => 'Notes',
                'Opportunities' => 'Opportunities',
                'Tasks'         => 'Tasks',
                'Users'         => 'Users',
            );
            $this->assertEquals($compareData, $importRulesTypes);
            Yii::app()->user->userModel = User::getByUsername('billy');
            $importRulesTypes           = ImportRulesUtil::getImportRulesTypesForCurrentUser();
            $this->assertEquals(array(), $importRulesTypes);
        }

        public function testAreAllRequiredAttributesMappedOrHaveRules()
        {
            Yii::app()->user->userModel  = User::getByUsername('super');
            $requiredAttributeCollection = ImportModelTestItemImportRules::
                                           getRequiredAttributesCollectionNotIncludingReadOnly();
            $this->assertEquals(3, count($requiredAttributeCollection));

            //Should fail, because nothing has been mapped.
            $mappedAttributeImportRulesCollection = AttributeImportRulesFactory::
                                                    makeCollection( 'ImportModelTestItem', array());
            $passed = ImportRulesUtil::
                      areAllRequiredAttributesMappedOrHaveRules($requiredAttributeCollection,
                                                                $mappedAttributeImportRulesCollection);
            $this->assertFalse($passed);

            //Should fail because only one of 3 required attributes has been mapped.
            $this->assertEquals(3, count($requiredAttributeCollection));
            $mappedAttributeImportRulesCollection = AttributeImportRulesFactory::
                                                    makeCollection( 'ImportModelTestItem', array('boolean'));
            $passed = ImportRulesUtil::
                      areAllRequiredAttributesMappedOrHaveRules($requiredAttributeCollection,
                                                                $mappedAttributeImportRulesCollection);
            $this->assertFalse($passed);

            //Should pass because all three required attributes are mapped as non-derived
            $this->assertEquals(3, count($requiredAttributeCollection));
            $mappedAttributeImportRulesCollection = AttributeImportRulesFactory::
                                                    makeCollection( 'ImportModelTestItem',
                                                    array('owner', 'boolean', 'string', 'lastName'));
            $passed = ImportRulesUtil::
                      areAllRequiredAttributesMappedOrHaveRules($requiredAttributeCollection,
                                                                $mappedAttributeImportRulesCollection);
            $this->assertTrue($passed);
            //Should pass because 2 of the attributes are mapped as non-derived, and lastName is mapped via derived
            $this->assertEquals(0, count($requiredAttributeCollection));
            $mappedAttributeImportRulesCollection = AttributeImportRulesFactory::
                                                    makeCollection( 'ImportModelTestItem',
                                                    array('owner', 'boolean', 'string', 'FullName'));
            $requiredAttributeCollection = ImportModelTestItemImportRules::
                                           getRequiredAttributesCollectionNotIncludingReadOnly();
            $passed = ImportRulesUtil::
                      areAllRequiredAttributesMappedOrHaveRules($requiredAttributeCollection,
                                                                $mappedAttributeImportRulesCollection);
            $this->assertTrue($passed);
        }

        public function testCheckIfAnyAttributesAreDoubleMappedWhenTheyAreDobuleMapped()
        {
            Yii::app()->user->userModel  = User::getByUsername('super');
            $mappedAttributeImportRulesCollection = AttributeImportRulesFactory::
                                                    makeCollection( 'ImportModelTestItem',
                                                    array('boolean', 'string', 'FullName'));
            ImportRulesUtil::checkIfAnyAttributesAreDoubleMapped($mappedAttributeImportRulesCollection);

            //Now it should fail, because lastName is mapped both as a non-derived and within FullName
            $mappedAttributeImportRulesCollection = AttributeImportRulesFactory::
                                                    makeCollection( 'ImportModelTestItem',
                                                    array('boolean', 'lastName', 'FullName'));
            try
            {
                ImportRulesUtil::checkIfAnyAttributesAreDoubleMapped($mappedAttributeImportRulesCollection);
                $this->fail();
            }
            catch (ImportAttributeMappedMoreThanOnceException $e)
            {
            }
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testInvalidDataPassedToAreAllRequiredAttributesMappedOrHaveRules()
        {
            $requiredAttributeCollection = array('a', 'b');
            ImportRulesUtil::areAllRequiredAttributesMappedOrHaveRules($requiredAttributeCollection, array('d', 'e'));
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testInvalidDataPassedToCheckIfAnyAttributesAreDoubleMapped()
        {
            ImportRulesUtil::checkIfAnyAttributesAreDoubleMapped(array('a', 'b'));
        }

        public function testGetImportRulesClassNameByType()
        {
            $rulesClassName = ImportRulesUtil::getImportRulesClassNameByType('ImportModelTestItem');
            $this->assertEquals('ImportModelTestItemImportRules', $rulesClassName);
        }

            /**
         * @expectedException NotSupportedException
         */
        public function testGetImportRulesClassNameByTypeWithBadType()
        {
            ImportRulesUtil::getImportRulesClassNameByType('abc');
        }
    }
?>