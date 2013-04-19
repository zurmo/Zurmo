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

    class ModelAttributeToOperatorTypeUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            ModulesSearchWithDataProviderTestHelper::createCustomAttributesForModel(new TestOperatorTypeModel());
        }

        public function setup()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testGetValidOperatorTypesForAllAttributeTypes()
        {
            $model = new TestOperatorTypeModel();
            $this->assertEquals('equals',     ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'integerStandard'));
            $this->assertEquals('equals',     ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'dateStandard'));
            $this->assertEquals('equals',     ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'dateTimeStandard'));
            $this->assertEquals('equals',     ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'floatStandard'));
            $this->assertEquals('equals',     ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'timeStandard'));
            $this->assertEquals('startsWith', ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'emailStandard'));
            $this->assertEquals('equals',     ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'booleanStandard'));
            $this->assertEquals('contains',   ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'urlStandard'));
            //Test all custom fields
            $this->assertEquals('equals',     ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'checkBoxCstm'));
            $this->assertEquals('equals',     ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'currencyCstm'));
            $this->assertEquals('equals',     ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'dateCstm'));
            $this->assertEquals('equals',     ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'dateTimeCstm'));
            $this->assertEquals('equals',     ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'decimalCstm'));
            $this->assertEquals('equals',     ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'dropDownCstm'));
            $this->assertEquals('equals',     ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'integerCstm'));
            $this->assertEquals('startsWith', ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'phoneCstm'));
            $this->assertEquals('equals',     ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'radioCstm'));
            $this->assertEquals('startsWith', ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'textCstm'));
            $this->assertEquals('contains',   ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'textAreaCstm'));
            $this->assertEquals('contains',   ModelAttributeToOperatorTypeUtil::getOperatorType($model, 'urlCstm'));
        }

        public function testGetAvailableOperatorsType()
        {
            $model = new TestOperatorTypeModel();
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_NUMBER,
                                ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'integerStandard'));
            $this->assertNull(  ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'dateStandard'));
            $this->assertNull(  ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'dateTimeStandard'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_NUMBER,
                                ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'floatStandard'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING,
                                ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'emailStandard'));
            $this->assertNull(  ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'booleanStandard'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING,
                                ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'urlStandard'));
            //Test all custom fields
            $this->assertNull(  ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'checkBoxCstm'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_NUMBER,
                                ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'currencyCstm'));
            $this->assertNull(  ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'dateCstm'));
            $this->assertNull(  ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'dateTimeCstm'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_NUMBER,
                                ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'decimalCstm'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_DROPDOWN,
                                ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'dropDownCstm'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_NUMBER,
                                ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'integerCstm'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING,
                                ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'phoneCstm'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_DROPDOWN,
                                ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'radioCstm'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING,
                                ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'textCstm'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING,
                                ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'textAreaCstm'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING,
                                ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($model, 'urlCstm'));
        }
    }
?>