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