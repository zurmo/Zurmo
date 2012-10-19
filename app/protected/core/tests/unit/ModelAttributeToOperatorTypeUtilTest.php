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
    }
?>