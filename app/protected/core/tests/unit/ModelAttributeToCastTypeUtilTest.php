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

    class ModelAttributeToCastTypeUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            ModulesSearchWithDataProviderTestHelper::createCustomAttributesForModel(new TestOperatorTypeModel());
        }

        public function testGetValidCastTypesForAllAttributeTypes()
        {
            $model = new TestOperatorTypeModel();

            $this->assertEquals('int',     ModelAttributeToCastTypeUtil::getCastType($model, 'integerStandard'));
            $this->assertEquals('string',  ModelAttributeToCastTypeUtil::getCastType($model, 'dateStandard'));
            $this->assertEquals('string',  ModelAttributeToCastTypeUtil::getCastType($model, 'dateTimeStandard'));
            $this->assertEquals('float',   ModelAttributeToCastTypeUtil::getCastType($model, 'floatStandard'));
            $this->assertEquals('string',  ModelAttributeToCastTypeUtil::getCastType($model, 'timeStandard'));
            $this->assertEquals('string',  ModelAttributeToCastTypeUtil::getCastType($model, 'emailStandard'));
            $this->assertEquals('bool',    ModelAttributeToCastTypeUtil::getCastType($model, 'booleanStandard'));
            $this->assertEquals('string',  ModelAttributeToCastTypeUtil::getCastType($model, 'urlStandard'));
            //Test all custom fields
            $this->assertEquals('bool',    ModelAttributeToCastTypeUtil::getCastType($model, 'checkBoxCstm'));
            $this->assertEquals('float',   ModelAttributeToCastTypeUtil::getCastType($model, 'currencyCstm'));
            $this->assertEquals('string',  ModelAttributeToCastTypeUtil::getCastType($model, 'dateCstm'));
            $this->assertEquals('string',  ModelAttributeToCastTypeUtil::getCastType($model, 'dateTimeCstm'));
            $this->assertEquals('float',   ModelAttributeToCastTypeUtil::getCastType($model, 'decimalCstm'));
            $this->assertEquals('int',     ModelAttributeToCastTypeUtil::getCastType($model, 'dropDownCstm'));
            $this->assertEquals('int',     ModelAttributeToCastTypeUtil::getCastType($model, 'integerCstm'));
            $this->assertEquals('string',  ModelAttributeToCastTypeUtil::getCastType($model, 'phoneCstm'));
            $this->assertEquals('int',     ModelAttributeToCastTypeUtil::getCastType($model, 'radioCstm'));
            $this->assertEquals('string',  ModelAttributeToCastTypeUtil::getCastType($model, 'textCstm'));
            $this->assertEquals('string',  ModelAttributeToCastTypeUtil::getCastType($model, 'textAreaCstm'));
            $this->assertEquals('string',  ModelAttributeToCastTypeUtil::getCastType($model, 'urlCstm'));
        }
    }
?>