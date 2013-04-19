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

    class SearchFormTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testGetSearchFormAttributeMappingRulesTypeByAttributeWithInvalidAttribute()
        {
            $searchForm = new MixedRelationsModelSearchFormTestModel(new MixedRelationsModel());
            $searchForm::getSearchFormAttributeMappingRulesTypeByAttribute('AttributeDoesNotExist');
        }

        /**
         * @depends testGetSearchFormAttributeMappingRulesTypeByAttributeWithInvalidAttribute
         */
        public function testGetSearchFormAttributeMappingRulesTypeByAttributeWithValidAttribute()
        {
            $searchForm = new MixedRelationsModelSearchFormTestModel(new MixedRelationsModel());
            $mappingRulesType = $searchForm::getSearchFormAttributeMappingRulesTypeByAttribute('differentOperatorA');
            $this->assertEquals('OwnedItemsOnly', $mappingRulesType);
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testInvalidDynamicDateAttributeOnForm()
        {
            $searchForm = new MixedRelationsModelSearchFormTestModel(new MixedRelationsModel());
            $searchForm->something__NotReal;
        }

        public function testDynamicDateAttributeOnForm()
        {
            $searchForm = new MixedRelationsModelSearchFormTestModel(new MixedRelationsModel());

            //Test get and set.
            $this->assertEquals(null, $searchForm->date__Date);
            $searchForm->date__Date = 'aTest';
            $this->assertEquals('aTest', $searchForm->date__Date);

            //Test getting attribute names collection
            $compareData = array('anyA',
                                 'ABName',
                                 'differentOperatorA',
                                 'differentOperatorB',
                                 'dateDateTimeADate__Date',
                                 'dateDateTimeADateTime__DateTime',
                                 'dynamicStructure',
                                 'dynamicClauses',
                                 'anyMixedAttributes',
                                 'date__Date',
                                 'date2__Date',
                                 'dateTime__DateTime',
                                 'dateTime2__DateTime',
            );
            $this->assertEquals($compareData, $searchForm->attributeNames());

            //Check some other methods to make sure they work ok.
            $this->assertFalse ($searchForm::isRelation('date__Date'));
            $this->assertTrue  ($searchForm->isAttribute('date__Date'));
            $this->assertFalse ($searchForm->isAttributeRequired('date__Date'));

            //Test attributeRules and attributeLabels
            $attributeLabels = $searchForm->attributeLabels();
            $this->assertEquals('Date',   $attributeLabels['date__Date']);
            $this->assertEquals('Date 2', $attributeLabels['date2__Date']);
            $compareData = array(
                array('date__Date', 'safe'),
                array('date2__Date', 'safe'),
                array('dateTime__DateTime', 'safe'),
                array('dateTime2__DateTime', 'safe'),
                array('dynamicStructure', 'safe'),
                array('dynamicStructure',   'validateDynamicStructure', 'on' => 'validateDynamic, validateSaveSearch'),
                array('dynamicClauses',   'safe'),
                array('dynamicClauses',   'validateDynamicClauses', 'on' => 'validateDynamic, validateSaveSearch'),
                array('anyA', 'safe'),
                array('ABName', 'safe'),
                array('differentOperatorA', 'safe'),
                array('differentOperatorB', 'boolean'),
                array('dateDateTimeADate__Date', 'safe'),
                array('dateDateTimeADateTime__DateTime', 'safe'),
            );
            $this->assertEquals($compareData, $searchForm->rules());

            //Test additional methods.
            $mappedData       = $searchForm->getAttributesMappedToRealAttributesMetadata();
            $this->assertEquals('resolveEntireMappingByRules', $mappedData['date__Date']);
            $mappingRulesType = $searchForm->getSearchFormAttributeMappingRulesTypeByAttribute('date__Date');
            $this->assertEquals('MixedDateTypes', $mappingRulesType);

            //Test that the correct elements are used for the dynamic date attribute.
            $elementType = ModelAttributeToMixedTypeUtil::getType($searchForm, 'date__Date');
            $this->assertEquals('MixedDateTypesForSearch', $elementType);
        }

        public function testDynamicDateTimeAttributeOnForm()
        {
            $searchForm = new MixedRelationsModelSearchFormTestModel(new MixedRelationsModel());

            //Test get and set.
            $this->assertEquals(null, $searchForm->dateTime__DateTime);
            $searchForm->dateTime__DateTime = 'aTest';
            $this->assertEquals('aTest', $searchForm->dateTime__DateTime);

            //Check some other methods to make sure they work ok.
            $this->assertFalse ($searchForm::isRelation('dateTime__DateTime'));
            $this->assertTrue  ($searchForm->isAttribute('dateTime__DateTime'));
            $this->assertFalse ($searchForm->isAttributeRequired('dateTime__DateTime'));

            //Test attributeRules and attributeLabels
            $attributeLabels = $searchForm->attributeLabels();
            $this->assertEquals('Date Time',   $attributeLabels['dateTime__DateTime']);
            $this->assertEquals('Date Time 2', $attributeLabels['dateTime2__DateTime']);

            //Test additional methods.
            $mappedData       = $searchForm->getAttributesMappedToRealAttributesMetadata();
            $this->assertEquals('resolveEntireMappingByRules', $mappedData['dateTime__DateTime']);
            $mappingRulesType = $searchForm->getSearchFormAttributeMappingRulesTypeByAttribute('dateTime__DateTime');
            $this->assertEquals('MixedDateTimeTypes', $mappingRulesType);

            //Test that the correct elements are used for the dynamic date attribute.
            $elementType = ModelAttributeToMixedTypeUtil::getType($searchForm, 'dateTime__DateTime');
            $this->assertEquals('MixedDateTypesForSearch', $elementType);
        }

        public function testGetGlobalSearchAttributeNamesAndLabelsAndAll()
        {
            $searchModel = new ASearchFormTestModel(new A());
            $data        = $searchModel->getGlobalSearchAttributeNamesAndLabelsAndAll();
            $compareData = array('All' => 'All', 'a' => 'A', 'name' => 'Name');
            $this->assertEquals($compareData, $data);
        }

        public function testResolveMixedSearchAttributeMappedToRealAttributesMetadata()
        {
            $realAttributesMetadata = array('something' => 'somethingElse');
            $searchModel = new ASearchFormTestModel(new A());
            $searchModel->resolveMixedSearchAttributeMappedToRealAttributesMetadata($realAttributesMetadata);
            $compareData = array('anyMixedAttributes' => array(array('a'), array('name')),
                                 'something' => 'somethingElse');
            $this->assertEquals($compareData, $realAttributesMetadata);

            //Add scoping.
            $searchModel = new ASearchFormTestModel(new A());
            $searchModel->setAnyMixedAttributesScope(array('name'));
            $searchModel->resolveMixedSearchAttributeMappedToRealAttributesMetadata($realAttributesMetadata);
            $compareData = array('anyMixedAttributes' => array(array('name')),
                                 'something' => 'somethingElse');
            $this->assertEquals($compareData, $realAttributesMetadata);
        }
    }
?>