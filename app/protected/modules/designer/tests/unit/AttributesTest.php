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

    class AttributesTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setup()
        {
            parent::setup();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testSetAndGetCheckBoxAttribute()
        {
            $attributeForm = new CheckBoxAttributeForm();
            $attributeForm->attributeName    = 'testCheckBox2';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Checkbox 2 de',
                'en' => 'Test Checkbox 2 en',
                'es' => 'Test Checkbox 2 es',
                'fr' => 'Test Checkbox 2 fr',
                'it' => 'Test Checkbox 2 it',
            );
            $attributeForm->isAudited        = true;
            $attributeForm->defaultValue     = 1; //means checked.
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), 'testCheckBox2');
            $this->assertEquals('CheckBox',         $attributeForm->getAttributeTypeName());
            $this->assertEquals('testCheckBox2',    $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Checkbox 2 de',
                'en' => 'Test Checkbox 2 en',
                'es' => 'Test Checkbox 2 es',
                'fr' => 'Test Checkbox 2 fr',
                'it' => 'Test Checkbox 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,                    $attributeForm->isAudited);
            $this->assertEquals(1,                       $attributeForm->defaultValue);
        }

        /**
         * @depends testSetAndGetCheckBoxAttribute
         */
        public function testSetAndGetCurrencyAttribute()
        {
            $attributeForm = new CurrencyValueAttributeForm();
            $attributeForm->attributeName = 'testCurrency2';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Currency 2 de',
                'en' => 'Test Currency 2 en',
                'es' => 'Test Currency 2 es',
                'fr' => 'Test Currency 2 fr',
                'it' => 'Test Currency 2 it',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = true;
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), 'testCurrency2');
            $this->assertEquals('CurrencyValue',   $attributeForm->getAttributeTypeName());
            $this->assertEquals('testCurrency2',   $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Currency 2 de',
                'en' => 'Test Currency 2 en',
                'es' => 'Test Currency 2 es',
                'fr' => 'Test Currency 2 fr',
                'it' => 'Test Currency 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,              $attributeForm->isAudited);
            $this->assertEquals(true,              $attributeForm->isRequired);
        }

        /**
         * @depends testSetAndGetCurrencyAttribute
         */
        public function testSetAndGetDateAttribute()
        {
            $attributeForm = new DateAttributeForm();
            $attributeForm->attributeName = 'testDate2';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Date 2 de',
                'en' => 'Test Date 2 en',
                'es' => 'Test Date 2 es',
                'fr' => 'Test Date 2 fr',
                'it' => 'Test Date 2 it',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = true;
            $attributeForm->defaultValueCalculationType  = DateTimeCalculatorUtil::YESTERDAY;
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), 'testDate2');
            $this->assertEquals('Date',        $attributeForm->getAttributeTypeName());
            $this->assertEquals('testDate2',   $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Date 2 de',
                'en' => 'Test Date 2 en',
                'es' => 'Test Date 2 es',
                'fr' => 'Test Date 2 fr',
                'it' => 'Test Date 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,          $attributeForm->isAudited);
            $this->assertEquals(true,          $attributeForm->isRequired);
            $this->assertEquals(DateTimeCalculatorUtil::YESTERDAY,        $attributeForm->defaultValueCalculationType);
            //Confirm default calculation loads correct default value for Account.
            $account = new Account();
            $yesterdayDateTime  = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $yesterday          = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                    $yesterdayDateTime->getTimeStamp() - (60 * 60 *24));
            $this->assertEquals($yesterday, $account->testDate2);
        }

        /**
         * @depends testSetAndGetDateAttribute
         */
        public function testSetAndGetDateTimeAttribute()
        {
            $attributeForm = new DateTimeAttributeForm();
            $attributeForm->attributeName = 'testDateTime2';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test DateTime 2 de',
                'en' => 'Test DateTime 2 en',
                'es' => 'Test DateTime 2 es',
                'fr' => 'Test DateTime 2 fr',
                'it' => 'Test DateTime 2 it',
            );
            $attributeForm->isAudited                    = true;
            $attributeForm->isRequired                   = true;
            $attributeForm->defaultValueCalculationType  = DateTimeCalculatorUtil::NOW;
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), 'testDateTime2');
            $this->assertEquals('DateTime',         $attributeForm->getAttributeTypeName());
            $this->assertEquals('testDateTime2',    $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test DateTime 2 de',
                'en' => 'Test DateTime 2 en',
                'es' => 'Test DateTime 2 es',
                'fr' => 'Test DateTime 2 fr',
                'it' => 'Test DateTime 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,                        $attributeForm->isAudited);
            $this->assertEquals(true,                        $attributeForm->isRequired);
            $this->assertEquals(DateTimeCalculatorUtil::NOW, $attributeForm->defaultValueCalculationType);
            //Confirm default calculation loads correct default value for Account.
            $account = new Account();
            $nowDateTime        = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $this->assertWithinTolerance($nowDateTime->getTimeStamp(), DateTimeUtil::convertDbFormatDateTimeToTimestamp($account->testDateTime2), 1);
        }

        /**
         * @depends testSetAndGetDateTimeAttribute
         */
        public function testChangeDefaultValueCalculationTypeToNullOnDateTimeAttribute()
        {
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), 'testDateTime2');
            $this->assertEquals(DateTimeCalculatorUtil::NOW,        $attributeForm->defaultValueCalculationType);
            //Change the defaultValueCalculationType back to null and confirm the default value is still null on new model.
            $attributeForm = new DateTimeAttributeForm();
            $attributeForm->attributeName = 'testDateTime2';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test DateTime 2 de',
                'en' => 'Test DateTime 2 en',
                'es' => 'Test DateTime 2 es',
                'fr' => 'Test DateTime 2 fr',
                'it' => 'Test DateTime 2 it',
            );
            $attributeForm->isAudited                    = true;
            $attributeForm->isRequired                   = true;
            $attributeForm->defaultValueCalculationType  = null;
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), 'testDateTime2');
            $this->assertEquals('DateTime',         $attributeForm->getAttributeTypeName());
            $this->assertEquals('testDateTime2',    $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test DateTime 2 de',
                'en' => 'Test DateTime 2 en',
                'es' => 'Test DateTime 2 es',
                'fr' => 'Test DateTime 2 fr',
                'it' => 'Test DateTime 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,               $attributeForm->isAudited);
            $this->assertEquals(true,               $attributeForm->isRequired);
            $this->assertEquals(null,               $attributeForm->defaultValueCalculationType);
            //Confirm default calculation loads correct default value (null) for Account.
            $account = new Account();
            $this->assertEquals(null, $account->testDateTime2);
        }

        /**
         * @depends testChangeDefaultValueCalculationTypeToNullOnDateTimeAttribute
         */
        public function testSetAndGetDecimalAttribute()
        {
            $attributeForm = new DecimalAttributeForm();
            $attributeForm->attributeName   = 'testDecimal2';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Decimal 2 de',
                'en' => 'Test Decimal 2 en',
                'es' => 'Test Decimal 2 es',
                'fr' => 'Test Decimal 2 fr',
                'it' => 'Test Decimal 2 it',
            );
            $attributeForm->isAudited       = true;
            $attributeForm->isRequired      = true;
            $attributeForm->maxLength       = 11;
            $attributeForm->precisionLength = 5;
            $attributeForm->defaultValue    = 34.213;
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $attributeForm->precisionLength  = 3;
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), 'testDecimal2');
            $this->assertEquals('Decimal',        $attributeForm->getAttributeTypeName());
            $this->assertEquals('testDecimal2',   $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Decimal 2 de',
                'en' => 'Test Decimal 2 en',
                'es' => 'Test Decimal 2 es',
                'fr' => 'Test Decimal 2 fr',
                'it' => 'Test Decimal 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,             $attributeForm->isAudited);
            $this->assertEquals(true,             $attributeForm->isRequired);
            $this->assertEquals(11,               $attributeForm->maxLength);
            $this->assertEquals(3,                $attributeForm->precisionLength);
            $this->assertEquals(34.213,           $attributeForm->defaultValue);
        }

        /**
         * @depends testSetAndGetDecimalAttribute
         */
        public function testSetAndGetDropDownAttribute()
        {
            $values = array(
                '747',
                'A380',
                'Seaplane',
                'Dive Bomber',
            );
            $airplanesFieldData = CustomFieldData::getByName('Airplanes');
            $airplanesFieldData->serializedData = serialize($values);
            $this->assertTrue($airplanesFieldData->save());

            $attributeForm = new DropDownAttributeForm();
            $attributeForm->attributeName       = 'testAirPlane';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Airplane 2 de',
                'en' => 'Test Airplane 2 en',
                'es' => 'Test Airplane 2 es',
                'fr' => 'Test Airplane 2 fr',
                'it' => 'Test Airplane 2 it',
            );
            $attributeForm->isAudited           = true;
            $attributeForm->isRequired          = true;
            $attributeForm->defaultValueOrder   = 1;
            $attributeForm->customFieldDataData = $values;
            $attributeForm->customFieldDataName = 'Airplanes';

            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $account = new Account();
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'testAirPlane');
            $this->assertEquals('DropDown',       $attributeForm->getAttributeTypeName());
            $this->assertEquals('testAirPlane',   $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Airplane 2 de',
                'en' => 'Test Airplane 2 en',
                'es' => 'Test Airplane 2 es',
                'fr' => 'Test Airplane 2 fr',
                'it' => 'Test Airplane 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,             $attributeForm->isAudited);
            $this->assertEquals(true,             $attributeForm->isRequired);
            $this->assertEquals('A380',           $attributeForm->defaultValue);
            $this->assertEquals(1,                $attributeForm->defaultValueOrder);
            $this->assertEquals('Airplanes',      $attributeForm->customFieldDataName);
            $this->assertEquals($values,          $attributeForm->customFieldDataData);

            //Test that validation on completely new picklists works correctly and is inline with the rules from
            //the CustomFieldData model.
            $attributeForm = new DropDownAttributeForm();
            $attributeForm->attributeName       = 's';    //name to short. test that this fails.
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Airplane 3 de',
                'en' => 'Test Airplane 3 en',
                'es' => 'Test Airplane 3 es',
                'fr' => 'Test Airplane 3 fr',
                'it' => 'Test Airplane 3 it',
            );
            $attributeForm->isAudited           = true;
            $attributeForm->isRequired          = true;
            $attributeForm->defaultValueOrder   = 1;
            $attributeForm->customFieldDataData = array('a', 'b', 'c');
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            $this->assertFalse($attributeForm->validate());
            $attributeForm->attributeName       = 'camelcased';
            $this->assertTrue($attributeForm->validate());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (Exception $e)
            {
                echo $e->getMessage();
                $this->fail();
            }
        }

        /**
         * @depends testSetAndGetDropDownAttribute
         */
        public function testSetAndGetIntegerAttribute()
        {
            $attributeForm = new IntegerAttributeForm();
            $attributeForm->attributeName = 'testInteger2';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Integer 2 de',
                'en' => 'Test Integer 2 en',
                'es' => 'Test Integer 2 es',
                'fr' => 'Test Integer 2 fr',
                'it' => 'Test Integer 2 it',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = true;
            $attributeForm->maxLength     = 11;
            $attributeForm->minValue      = -500;
            $attributeForm->maxValue      = 500;
            $attributeForm->defaultValue  = 458;
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $attributeForm->defaultValue  = 50;
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), 'testInteger2');
            $this->assertEquals('Integer',        $attributeForm->getAttributeTypeName());
            $this->assertEquals('testInteger2',   $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Integer 2 de',
                'en' => 'Test Integer 2 en',
                'es' => 'Test Integer 2 es',
                'fr' => 'Test Integer 2 fr',
                'it' => 'Test Integer 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,             $attributeForm->isAudited);
            $this->assertEquals(true,             $attributeForm->isRequired);
            $this->assertEquals(11,               $attributeForm->maxLength);
            $this->assertEquals(-500,             $attributeForm->minValue);
            $this->assertEquals(500,              $attributeForm->maxValue);
            $this->assertEquals(50,               $attributeForm->defaultValue);
        }

        /**
         * @depends testSetAndGetIntegerAttribute
         */
        public function testSetAndGetMultiSelectDropDownAttribute()
        {
            //todo:
        }

        /**
         * @depends testSetAndGetMultiSelectDropDownAttribute
         */
        public function testSetAndGetPhoneAttribute()
        {
            $attributeForm = new PhoneAttributeForm();
            $attributeForm->attributeName = 'testPhone2';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Phone 2 de',
                'en' => 'Test Phone 2 en',
                'es' => 'Test Phone 2 es',
                'fr' => 'Test Phone 2 fr',
                'it' => 'Test Phone 2 it',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = true;
            $attributeForm->maxLength     = 50;
            $attributeForm->defaultValue  = '1-800-111-2233';
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $attributeForm->maxLength     = 20;
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), 'testPhone2');
            $this->assertEquals('Phone',          $attributeForm->getAttributeTypeName());
            $this->assertEquals('testPhone2',     $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Phone 2 de',
                'en' => 'Test Phone 2 en',
                'es' => 'Test Phone 2 es',
                'fr' => 'Test Phone 2 fr',
                'it' => 'Test Phone 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,             $attributeForm->isAudited);
            $this->assertEquals(true,             $attributeForm->isRequired);
            $this->assertEquals(20,               $attributeForm->maxLength);
            $this->assertEquals('1-800-111-2233', $attributeForm->defaultValue);
        }

        /**
         * @depends testSetAndGetPhoneAttribute
         */
        public function testSetAndGetRadioDropDownAttribute()
        {
            $values = array(
                'Wing',
                'Nose',
                'Seat',
                'Wheel',
            );
            $airplanePartsFieldData = CustomFieldData::getByName('AirplaneParts');
            $airplanePartsFieldData->serializedData = serialize($values);
            $this->assertTrue($airplanePartsFieldData->save());
            $attributeForm = new RadioDropDownAttributeForm();
            $attributeForm->attributeName       = 'testAirPlaneParts';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Airplane Parts 2 de',
                'en' => 'Test Airplane Parts 2 en',
                'es' => 'Test Airplane Parts 2 es',
                'fr' => 'Test Airplane Parts 2 fr',
                'it' => 'Test Airplane Parts 2 it',
            );
            $attributeForm->isAudited           = true;
            $attributeForm->isRequired          = true;
            $attributeForm->defaultValueOrder   = 3;
            $attributeForm->customFieldDataData = $values;
            $attributeForm->customFieldDataName = 'AirplaneParts';

            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $account = new Account();
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'testAirPlaneParts');
            $this->assertEquals('testAirPlaneParts',    $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Airplane Parts 2 de',
                'en' => 'Test Airplane Parts 2 en',
                'es' => 'Test Airplane Parts 2 es',
                'fr' => 'Test Airplane Parts 2 fr',
                'it' => 'Test Airplane Parts 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,                   $attributeForm->isAudited);
            $this->assertEquals(true,                   $attributeForm->isRequired);
            $this->assertEquals('Wheel',                $attributeForm->defaultValue);
            $this->assertEquals(3,                      $attributeForm->defaultValueOrder);
            $this->assertEquals($values,                $attributeForm->customFieldDataData);
            $this->assertEquals('RadioDropDown',        $attributeForm->getAttributeTypeName());
        }

        /**
         * @depends testSetAndGetRadioDropDownAttribute
         */
        public function testSetAndGetTextAttribute()
        {
            $attributeForm = new TextAttributeForm();
            $attributeForm->attributeName = 'testText2';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Text 2 de',
                'en' => 'Test Text 2 en',
                'es' => 'Test Text 2 es',
                'fr' => 'Test Text 2 fr',
                'it' => 'Test Text 2 it',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = true;
            $attributeForm->maxLength     = 50;
            $attributeForm->defaultValue  = 'Kangaroo';
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), 'testText2');
            $this->assertEquals('Text',        $attributeForm->getAttributeTypeName());
            $this->assertEquals('testText2',   $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Text 2 de',
                'en' => 'Test Text 2 en',
                'es' => 'Test Text 2 es',
                'fr' => 'Test Text 2 fr',
                'it' => 'Test Text 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,         $attributeForm->isAudited);
            $this->assertEquals(true,          $attributeForm->isRequired);
            $this->assertEquals(50,            $attributeForm->maxLength);
            $this->assertEquals('Kangaroo',    $attributeForm->defaultValue);
        }

        /**
         * @depends testSetAndGetTextAttribute
         */
        public function testSetAndGetTextAreaAttribute()
        {
            $attributeForm = new TextAreaAttributeForm();
            $attributeForm->attributeName = 'testTextArea2';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Text Area 2 de',
                'en' => 'Test Text Area 2 en',
                'es' => 'Test Text Area 2 es',
                'fr' => 'Test Text Area 2 fr',
                'it' => 'Test Text Area 2 it',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = true;
            $attributeForm->defaultValue  = 'Kangaroo Pouch';
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), 'testTextArea2');
            $this->assertEquals('TextArea',         $attributeForm->getAttributeTypeName());
            $this->assertEquals('testTextArea2',    $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Text Area 2 de',
                'en' => 'Test Text Area 2 en',
                'es' => 'Test Text Area 2 es',
                'fr' => 'Test Text Area 2 fr',
                'it' => 'Test Text Area 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,               $attributeForm->isAudited);
            $this->assertEquals(true,               $attributeForm->isRequired);
            $this->assertEquals('Kangaroo Pouch',   $attributeForm->defaultValue);
        }

        /**
         * @depends testSetAndGetTextAreaAttribute
         */
        public function testSetAndGetUrlAttribute()
        {
            $attributeForm = new UrlAttributeForm();
            $attributeForm->attributeName = 'testUrl2';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Url 2 de',
                'en' => 'Test Url 2 en',
                'es' => 'Test Url 2 es',
                'fr' => 'Test Url 2 fr',
                'it' => 'Test Url 2 it',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = true;
            $attributeForm->maxLength     = 50;
            $attributeForm->defaultValue  = 'http://www.outback.com';
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), 'testUrl2');
            $this->assertEquals('Url',                    $attributeForm->getAttributeTypeName());
            $this->assertEquals('testUrl2',               $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Url 2 de',
                'en' => 'Test Url 2 en',
                'es' => 'Test Url 2 es',
                'fr' => 'Test Url 2 fr',
                'it' => 'Test Url 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,                     $attributeForm->isAudited);
            $this->assertEquals(true,                     $attributeForm->isRequired);
            $this->assertEquals(50,                       $attributeForm->maxLength);
            $this->assertEquals('http://www.outback.com', $attributeForm->defaultValue);
        }

        /**
         * @depends testSetAndGetUrlAttribute
         */
        public function testCreateDropDownWithMixedCaseAttributeName()
        {
            $values = array(
                'song 1',
                'song 2',
                'song 3',
            );
            $attributeForm = new DropDownAttributeForm();
            $attributeForm->attributeName       = 'playMyFavoriteSong';
            $attributeForm->attributeLabels  = array(
                'de' => 'Play My Favorite Song de',
                'en' => 'Play My Favorite Song en',
                'es' => 'Play My Favorite Song es',
                'fr' => 'Play My Favorite Song fr',
                'it' => 'Play My Favorite Song it',
            );
            $attributeForm->isAudited           = true;
            $attributeForm->isRequired          = true;
            $attributeForm->defaultValueOrder   = 1;
            $attributeForm->customFieldDataData = $values;
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }
        }

        /**
         * @depends testCreateDropDownWithMixedCaseAttributeName
         */
        public function testPopulateCustomAttributes()
        {
            $currencies = Currency::getAll();
            $account = new Account();
            $account->name                       = 'my test account';
            $account->owner                      = Yii::app()->user->userModel;
            $account->testCheckBox2              = 0;
            $account->testCurrency2->value       = 728.89;
            $account->testCurrency2->currency    = $currencies[0];
            $account->testDate2                  = '2008-09-03';
            $account->testDateTime2              = '2008-09-02 03:03:03';
            $account->testDecimal2               = 45.67;
            $account->testAirPlane->value        = 'A380'; //Dive Bomber
            $account->testInteger2               = 56;
            $account->testPhone2                 = '345345234';
            $account->testAirPlaneParts->value   = 'Seat'; // Wheel
            $account->testText2                  = 'some test stuff';
            $account->testTextArea2              = 'some test text area stuff';
            $account->testUrl2                   = 'http://www.zurmo.com';
            $account->playMyFavoriteSong->value  = 'song2'; // song 3
            $saved = $account->save();
            $this->assertTrue($saved);
            $accountId = $account->id;
            $account->forget();
            unset($account);
            $account = Account::getById($accountId);
            $this->assertEquals(0,                           $account->testCheckBox2);
            $this->assertEquals(false,                       (bool)$account->testCheckBox2);
            $this->assertEquals(728.89,                      $account->testCurrency2->value);
            $this->assertEquals(1,                           $account->testCurrency2->rateToBase);
            $this->assertEquals('2008-09-03',                $account->testDate2);
            $this->assertEquals('2008-09-02 03:03:03',       $account->testDateTime2);
            $this->assertEquals(45.67,                       $account->testDecimal2);
            $this->assertEquals('A380',                      $account->testAirPlane->value);
            $this->assertEquals(56,                          $account->testInteger2);
            $this->assertEquals(345345234,                   $account->testPhone2);
            $this->assertEquals('Seat',                      $account->testAirPlaneParts->value);
            $this->assertEquals('some test stuff',           $account->testText2);
            $this->assertEquals('some test text area stuff', $account->testTextArea2);
            $this->assertEquals('http://www.zurmo.com',      $account->testUrl2);
            $this->assertEquals('song2',                     $account->playMyFavoriteSong->value);

            //Switch values around to cover for any default value pollution on the assertions above.
            $account->testCheckBox2              = 1;
            $account->testCurrency2->value       = 728.92;
            $account->testCurrency2->currency    = $currencies[0];
            $account->testDate2                  = '2008-09-04';
            $account->testDateTime2              = '2008-09-03 03:03:03';
            $account->testDecimal2               = 45.68;
            $account->testAirPlane->value        = 'Dive Bomber';
            $account->testInteger2               = 57;
            $account->testPhone2                 = '3453452344';
            $account->testAirPlaneParts->value   = 'Wheel';
            $account->testText2                  = 'some test stuff2';
            $account->testTextArea2              = 'some test text area stuff2';
            $account->testUrl2                   = 'http://www.zurmo.org';
            $account->playMyFavoriteSong->value  = 'song3';
            $saved = $account->save();
            $this->assertTrue($saved);
            $accountId = $account->id;
            $account->forget();
            unset($account);
            $account = Account::getById($accountId);
            $this->assertEquals(1,                            $account->testCheckBox2);
            $this->assertEquals(true,                         (bool)$account->testCheckBox2);
            $this->assertEquals(728.92,                       $account->testCurrency2->value);
            $this->assertEquals(1,                            $account->testCurrency2->rateToBase);
            $this->assertEquals('2008-09-04',                 $account->testDate2);
            $this->assertEquals('2008-09-03 03:03:03',        $account->testDateTime2);
            $this->assertEquals(45.68,                        $account->testDecimal2);
            $this->assertEquals('Dive Bomber',                $account->testAirPlane->value);
            $this->assertEquals(57,                           $account->testInteger2);
            $this->assertEquals(3453452344,                   $account->testPhone2);
            $this->assertEquals('Wheel',                      $account->testAirPlaneParts->value);
            $this->assertEquals('some test stuff2',           $account->testText2);
            $this->assertEquals('some test text area stuff2', $account->testTextArea2);
            $this->assertEquals('http://www.zurmo.org',       $account->testUrl2);
            $this->assertEquals('song3',                      $account->playMyFavoriteSong->value);
        }
    }
?>
