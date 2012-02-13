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
            $this->setAndGetCheckBoxAttribute('testCheckBox2', true);
            $this->setAndGetCheckBoxAttribute('testCheckBox3', false);
        }

        protected function setAndGetCheckBoxAttribute($attributeName, $withDefaultData)
        {
            $this->assertTrue(isset($attributeName) && $attributeName != '');
            $this->assertTrue(isset($withDefaultData) && is_bool($withDefaultData));
            $attributeForm = new CheckBoxAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Checkbox 2 de',
                'en' => 'Test Checkbox 2 en',
                'es' => 'Test Checkbox 2 es',
                'fr' => 'Test Checkbox 2 fr',
                'it' => 'Test Checkbox 2 it',
            );
            $attributeForm->isAudited        = true;

            if ($withDefaultData)
            {
                $attributeForm->defaultValue     = 1; //means checked.
            }

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

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), $attributeName);
            $this->assertEquals('CheckBox',         $attributeForm->getAttributeTypeName());
            $this->assertEquals($attributeName,     $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Checkbox 2 de',
                'en' => 'Test Checkbox 2 en',
                'es' => 'Test Checkbox 2 es',
                'fr' => 'Test Checkbox 2 fr',
                'it' => 'Test Checkbox 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,                    $attributeForm->isAudited);

            if ($withDefaultData)
            {
                $this->assertEquals(1,                   $attributeForm->defaultValue);
            }
            else
            {
                $this->assertEquals(null,                $attributeForm->defaultValue);
            }
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
            $this->setAndGetDateAttribute('testDate2', true);
            $this->setAndGetDateAttribute('testDate3', false);
        }

        protected function setAndGetDateAttribute($attributeName, $withDefaultData)
        {
            $this->assertTrue(isset($attributeName) && $attributeName != '');
            $this->assertTrue(isset($withDefaultData) && is_bool($withDefaultData));

            $attributeForm = new DateAttributeForm();
            $attributeForm->attributeName = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Date 2 de',
                'en' => 'Test Date 2 en',
                'es' => 'Test Date 2 es',
                'fr' => 'Test Date 2 fr',
                'it' => 'Test Date 2 it',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = true;

            if ($withDefaultData)
            {
                $attributeForm->defaultValueCalculationType  = DateTimeCalculatorUtil::YESTERDAY;
            }

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

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), $attributeName);
            $this->assertEquals('Date',        $attributeForm->getAttributeTypeName());
            $this->assertEquals($attributeName,   $attributeForm->attributeName);
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

            if ($withDefaultData)
            {
                $this->assertEquals(DateTimeCalculatorUtil::YESTERDAY,        $attributeForm->defaultValueCalculationType);
                //Confirm default calculation loads correct default value for Account.
                $account = new Account();
                $yesterdayDateTime  = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
                $yesterday          = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                        $yesterdayDateTime->getTimeStamp() - (60 * 60 *24));
                $this->assertEquals($yesterday, $account->$attributeName);
            }
            else
            {
                $account = new Account();
                $this->assertEquals(null,        $attributeForm->defaultValueCalculationType);
                $this->assertEquals(null,        $account->$attributeName);
            }
        }

        /**
         * @depends testSetAndGetDateAttribute
         */
        public function testSetAndGetDateTimeAttribute()
        {
            $this->setAndGetDateTimeAttribute('testDateTime2', true);
            $this->setAndGetDateTimeAttribute('testDateTime3', false);
        }

        protected function setAndGetDateTimeAttribute($attributeName, $withDefaultData)
        {
            $this->assertTrue(isset($attributeName) && $attributeName != '');
            $this->assertTrue(isset($withDefaultData) && is_bool($withDefaultData));

            $attributeForm = new DateTimeAttributeForm();
            $attributeForm->attributeName = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test DateTime 2 de',
                'en' => 'Test DateTime 2 en',
                'es' => 'Test DateTime 2 es',
                'fr' => 'Test DateTime 2 fr',
                'it' => 'Test DateTime 2 it',
            );
            $attributeForm->isAudited                    = true;
            $attributeForm->isRequired                   = true;

            if ($withDefaultData)
            {
                $attributeForm->defaultValueCalculationType  = DateTimeCalculatorUtil::NOW;
            }
            else
            {
                $attributeForm->defaultValueCalculationType  = null;
            }

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

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), $attributeName);
            $this->assertEquals('DateTime',         $attributeForm->getAttributeTypeName());
            $this->assertEquals($attributeName,    $attributeForm->attributeName);
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

            if ($withDefaultData)
            {
                $this->assertEquals(DateTimeCalculatorUtil::NOW, $attributeForm->defaultValueCalculationType);
                //Confirm default calculation loads correct default value for Account.
                $account = new Account();
                $nowDateTime        = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
                $this->assertWithinTolerance($nowDateTime->getTimeStamp(), DateTimeUtil::convertDbFormatDateTimeToTimestamp($account->$attributeName), 1);
            }
            else
            {
                $this->assertEquals(null, $attributeForm->defaultValueCalculationType);
                //Confirm default calculation loads correct default value (null) for Account.
                $account = new Account();
                $this->assertEquals(null, $account->$attributeName);
            }
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
            $this->setAndGetDecimalAttribute('testDecimal2', true);
            $this->setAndGetDecimalAttribute('testDecimal3', false);
        }

        protected function setAndGetDecimalAttribute($attributeName, $withDefaultData)
        {
            $this->assertTrue(isset($attributeName) && $attributeName != '');
            $this->assertTrue(isset($withDefaultData) && is_bool($withDefaultData));

            $attributeForm = new DecimalAttributeForm();
            $attributeForm->attributeName   = $attributeName;
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

            if ($withDefaultData)
            {
                $attributeForm->defaultValue    = 34.213;
            }

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

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), $attributeName);
            $this->assertEquals('Decimal',        $attributeForm->getAttributeTypeName());
            $this->assertEquals($attributeName,   $attributeForm->attributeName);
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

            if ($withDefaultData)
            {
                $this->assertEquals(34.213,       $attributeForm->defaultValue);
            }
            else
            {
                $this->assertEquals(null,         $attributeForm->defaultValue);
            }
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
            $labels = array('fr' => array('747 fr', 'A380 fr', 'Seaplane fr', 'Dive Bomber fr'),
                            'de' => array('747 de', 'A380 de', 'Seaplane de', 'Dive Bomber de'),
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
            $attributeForm->isAudited             = true;
            $attributeForm->isRequired            = true;
            $attributeForm->defaultValueOrder     = 1;
            $attributeForm->customFieldDataData   = $values;
            $attributeForm->customFieldDataName   = 'Airplanes';
            $attributeForm->customFieldDataLabels = $labels;

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
            $this->assertEquals($labels,          $attributeForm->customFieldDataLabels);

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
            $this->setAndGetIntegerAttribute('testInteger2', true);
            $this->setAndGetIntegerAttribute('testInteger3', false);
        }

        protected function setAndGetIntegerAttribute($attributeName, $withDefaultData)
        {
            $this->assertTrue(isset($attributeName) && $attributeName != '');
            $this->assertTrue(isset($withDefaultData) && is_bool($withDefaultData));

            $attributeForm = new IntegerAttributeForm();
            $attributeForm->attributeName = $attributeName;
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

            if ($withDefaultData)
            {
                $attributeForm->defaultValue  = 458;
            }

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

            if ($withDefaultData)
            {
                $attributeForm->defaultValue  = 50;
            }

            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), $attributeName);
            $this->assertEquals('Integer',        $attributeForm->getAttributeTypeName());
            $this->assertEquals($attributeName,   $attributeForm->attributeName);
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

            if ($withDefaultData)
            {
                $this->assertEquals(50,           $attributeForm->defaultValue);
            }
            else
            {
                $this->assertEquals(null,         $attributeForm->defaultValue);
            }
        }

        /**
         * @depends testSetAndGetIntegerAttribute
         */
        public function testsetAndGetIntegerAttributeWithNoMinOrMax()
        {
            $attributeName = 'integernominmax';
            $attributeForm = new IntegerAttributeForm();
            $attributeForm->attributeName = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Integer NoMinMax de',
                'en' => 'Test Integer NoMinMax en',
                'es' => 'Test Integer NoMinMax es',
                'fr' => 'Test Integer NoMinMax fr',
                'it' => 'Test Integer NoMinMax it',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = false;
            $attributeForm->maxLength     = 11;

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

            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), $attributeName);
            $this->assertEquals('Integer',        $attributeForm->getAttributeTypeName());
            $this->assertEquals($attributeName,   $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Integer NoMinMax de',
                'en' => 'Test Integer NoMinMax en',
                'es' => 'Test Integer NoMinMax es',
                'fr' => 'Test Integer NoMinMax fr',
                'it' => 'Test Integer NoMinMax it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,  $attributeForm->isAudited);
            $this->assertEquals(false, $attributeForm->isRequired);
            $this->assertEquals(11,    $attributeForm->maxLength);
            $this->assertEquals(null,  $attributeForm->minValue);
            $this->assertEquals(null,  $attributeForm->maxValue);
            $this->assertEquals(null,  $attributeForm->defaultValue);
        }

        /**
         * @depends testsetAndGetIntegerAttributeWithNoMinOrMax
         */
        public function testSetAndGetMultiSelectDropDownAttribute()
        {
            $this->setAndGetMultiSelectDropDownAttribute('testHobbies1', true);
            $this->setAndGetMultiSelectDropDownAttribute('testHobbies2', false);
        }

        protected function setAndGetMultiSelectDropDownAttribute($attributeName, $withDefaultData)
        {
            $this->assertTrue(isset($attributeName) && $attributeName != '');
            $this->assertTrue(isset($withDefaultData) && is_bool($withDefaultData));

            $values = array(
                'Reading',
                'Writing',
                'Singing',
                'Surfing',
            );
            $labels = array('fr' => array('Reading fr', 'Writing fr', 'Singing fr', 'Surfing fr'),
                            'de' => array('Reading de', 'Writing de', 'Singing de', 'Surfing de'),
            );
            $hobbiesFieldData = CustomFieldData::getByName('Hobbies');
            $hobbiesFieldData->serializedData = serialize($values);
            $this->assertTrue($hobbiesFieldData->save());

            $attributeForm = new MultiSelectDropDownAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Hobbies 2 de',
                'en' => 'Test Hobbies 2 en',
                'es' => 'Test Hobbies 2 es',
                'fr' => 'Test Hobbies 2 fr',
                'it' => 'Test Hobbies 2 it',
            );
            $attributeForm->isAudited             = true;
            $attributeForm->isRequired            = true;
            $attributeForm->customFieldDataData   = $values;
            $attributeForm->customFieldDataName   = 'Hobbies';
            $attributeForm->customFieldDataLabels = $labels;

            if ($withDefaultData)
            {
                $attributeForm->defaultValueOrder = 1;
            }
            else
            {
                $attributeForm->defaultValue      = null;
                $attributeForm->defaultValueOrder = null;
            }

            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter                         = new $modelAttributesAdapterClassName(new Account());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }

            $account       = new Account();
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, $attributeName);
            $this->assertEquals('MultiSelectDropDown', $attributeForm->getAttributeTypeName());
            $this->assertEquals($attributeName,         $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Hobbies 2 de',
                'en' => 'Test Hobbies 2 en',
                'es' => 'Test Hobbies 2 es',
                'fr' => 'Test Hobbies 2 fr',
                'it' => 'Test Hobbies 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,                    $attributeForm->isAudited);
            $this->assertEquals(true,                    $attributeForm->isRequired);
            $this->assertEquals('Hobbies',               $attributeForm->customFieldDataName);
            $this->assertEquals($values,                 $attributeForm->customFieldDataData);
            $this->assertEquals($labels,                 $attributeForm->customFieldDataLabels);

            if ($withDefaultData)
            {
                $this->assertEquals('Writing',           $attributeForm->defaultValue);
                $this->assertEquals(1,                   $attributeForm->defaultValueOrder);
            }
            else
            {
                $this->assertEquals(null,                $attributeForm->defaultValue);
                $this->assertEquals(null,                $attributeForm->defaultValueOrder);
            }

            //Test that validation on completely new multi select picklists works correctly and is inline with the rules 
            //from the CustomFieldData model.
            $attributeForm = new MultiSelectDropDownAttributeForm();
            $attributeForm->attributeName    = 's';    //name to short. test that this fails.
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Hobbies 3 de',
                'en' => 'Test Hobbies 3 en',
                'es' => 'Test Hobbies 3 es',
                'fr' => 'Test Hobbies 3 fr',
                'it' => 'Test Hobbies 3 it',
            );
            $attributeForm->isAudited           = true;
            $attributeForm->isRequired          = true;
            if ($withDefaultData)
            {
                $attributeForm->defaultValueOrder = 1;
            }
            else
            {
                $attributeForm->defaultValue      = null;
                $attributeForm->defaultValueOrder = null;
            }
            $attributeForm->customFieldDataData = array('a', 'b', 'c');
            $modelAttributesAdapterClassName    = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter                            = new $modelAttributesAdapterClassName(new Account());
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
         * @depends testSetAndGetMultiSelectDropDownAttribute
         */
        public function testSetAndGetTagCloudAttribute()
        {
            $this->setAndGetTagCloudAttribute('testLanguages1', true);
            $this->setAndGetTagCloudAttribute('testLanguages2', false);
        }

        protected function setAndGetTagCloudAttribute($attributeName, $withDefaultData)
        {
            $this->assertTrue(isset($attributeName) && $attributeName != '');
            $this->assertTrue(isset($withDefaultData) && is_bool($withDefaultData));

            $values = array(
                'English',
                'French',
                'Danish',
                'Spanish',
            );
            $labels = array('fr' => array('English fr', 'French fr', 'Danish fr', 'Spanish fr'),
                            'de' => array('English de', 'French de', 'Danish de', 'Spanish de'),
            );
            $languageFieldData = CustomFieldData::getByName('Languages');
            $languageFieldData->serializedData = serialize($values);
            $this->assertTrue($languageFieldData->save());

            $attributeForm                   = new TagCloudAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Languages 2 de',
                'en' => 'Test Languages 2 en',
                'es' => 'Test Languages 2 es',
                'fr' => 'Test Languages 2 fr',
                'it' => 'Test Languages 2 it',
            );
            $attributeForm->isAudited             = true;
            $attributeForm->isRequired            = true;
            $attributeForm->customFieldDataData   = $values;
            $attributeForm->customFieldDataName   = 'Languages';
            $attributeForm->customFieldDataLabels = $labels;

            if ($withDefaultData)
            {
                $attributeForm->defaultValueOrder = 1;
            }
            else
            {
                $attributeForm->defaultValueOrder = null;
            }

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
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, $attributeName);
            $this->assertEquals('TagCloud',     $attributeForm->getAttributeTypeName());
            $this->assertEquals($attributeName, $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Languages 2 de',
                'en' => 'Test Languages 2 en',
                'es' => 'Test Languages 2 es',
                'fr' => 'Test Languages 2 fr',
                'it' => 'Test Languages 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,                    $attributeForm->isAudited);
            $this->assertEquals(true,                    $attributeForm->isRequired);
            $this->assertEquals('Languages',             $attributeForm->customFieldDataName);
            $this->assertEquals($values,                 $attributeForm->customFieldDataData);
            $this->assertEquals($labels,                 $attributeForm->customFieldDataLabels);

            if ($withDefaultData)
            {
                $this->assertEquals('French',            $attributeForm->defaultValue);
                $this->assertEquals(1,                   $attributeForm->defaultValueOrder);
            }
            else
            {
                $this->assertEquals(null,                $attributeForm->defaultValue);
                $this->assertEquals(null,                $attributeForm->defaultValueOrder);
            }

            //Test that validation on completely new multi select picklists works correctly and is inline with the rules 
            //from the CustomFieldData model.
            $attributeForm = new TagCloudAttributeForm();
            $attributeForm->attributeName    = 's';    //name to short. test that this fails.
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Languages 3 de',
                'en' => 'Test Languages 3 en',
                'es' => 'Test Languages 3 es',
                'fr' => 'Test Languages 3 fr',
                'it' => 'Test Languages 3 it',
            );
            $attributeForm->isAudited           = true;
            $attributeForm->isRequired          = true;
            $attributeForm->defaultValueOrder   = 1;
            $attributeForm->customFieldDataData = array('a', 'b', 'c');
            $modelAttributesAdapterClassName    = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter                            = new $modelAttributesAdapterClassName(new Account());
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
         * @depends testSetAndGetTagCloudAttribute
         */
        public function testSetAndGetDropDownDependencyAttribute()
        {
            $this->setAndGetDropDownDependencyAttributeWithThreeLevelDependency();
            $this->setAndGetDropDownDependencyAttributeWithTwoLevelDependency();
        }

        protected function setAndGetDropDownDependencyAttributeWithThreeLevelDependency()
        {
            $countries = array(
                'aaaa',
                'bbbb',
                'cccc',
            );
            $country_labels = array('fr' => array('aaaa fr', 'bbbb fr', 'cccc fr'),
                                    'de' => array('aaaa de', 'bbbb de', 'cccc de'),
            );
            $countriesFieldData = CustomFieldData::getByName('Countries');
            $countriesFieldData->serializedData = serialize($countries);
            $this->assertTrue($countriesFieldData->save());

            $attributeForm = new DropDownAttributeForm();
            $attributeForm->attributeName       = 'testCountry';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Country 2 de',
                'en' => 'Test Country 2 en',
                'es' => 'Test Country 2 es',
                'fr' => 'Test Country 2 fr',
                'it' => 'Test Country 2 it',
            );
            $attributeForm->isAudited             = true;
            $attributeForm->isRequired            = true;
            $attributeForm->customFieldDataData   = $countries;
            $attributeForm->customFieldDataName   = 'Countries';
            $attributeForm->customFieldDataLabels = $country_labels;

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
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'testCountry');
            $this->assertEquals('DropDown',      $attributeForm->getAttributeTypeName());
            $this->assertEquals('testCountry',   $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Country 2 de',
                'en' => 'Test Country 2 en',
                'es' => 'Test Country 2 es',
                'fr' => 'Test Country 2 fr',
                'it' => 'Test Country 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,             $attributeForm->isAudited);
            $this->assertEquals(true,             $attributeForm->isRequired);
            $this->assertEquals('Countries',      $attributeForm->customFieldDataName);
            $this->assertEquals($countries,       $attributeForm->customFieldDataData);
            $this->assertEquals($country_labels,  $attributeForm->customFieldDataLabels);

            $states = array(
                'aaa1',
                'aaa2',
                'aaa3',
                'bbb1',
                'bbb2',
                'bbb3',
                'ccc1',
                'ccc2',
                'ccc3',
            );
            $state_labels = array('fr' => array('aaa1 fr', 'aaa2 fr', 'aaa3 fr', 'bbb1 fr', 'bbb2 fr', 'bbb3 fr', 'ccc1 fr', 'ccc2 fr', 'ccc3 fr'),
                                  'de' => array('aaa1 de', 'aaa2 de', 'aaa3 de', 'bbb1 de', 'bbb2 de', 'bbb3 de', 'ccc1 de', 'ccc2 de', 'ccc3 de'),
            );
            $statesFieldData = CustomFieldData::getByName('States');
            $statesFieldData->serializedData = serialize($states);
            $this->assertTrue($statesFieldData->save());

            $attributeForm = new DropDownAttributeForm();
            $attributeForm->attributeName    = 'testState';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test State 2 de',
                'en' => 'Test State 2 en',
                'es' => 'Test State 2 es',
                'fr' => 'Test State 2 fr',
                'it' => 'Test State 2 it',
            );
            $attributeForm->isAudited             = true;
            $attributeForm->isRequired            = true;
            $attributeForm->customFieldDataData   = $states;
            $attributeForm->customFieldDataName   = 'States';
            $attributeForm->customFieldDataLabels = $state_labels;

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
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'testState');
            $this->assertEquals('DropDown',  $attributeForm->getAttributeTypeName());
            $this->assertEquals('testState', $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test State 2 de',
                'en' => 'Test State 2 en',
                'es' => 'Test State 2 es',
                'fr' => 'Test State 2 fr',
                'it' => 'Test State 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,          $attributeForm->isAudited);
            $this->assertEquals(true,          $attributeForm->isRequired);
            $this->assertEquals('States',      $attributeForm->customFieldDataName);
            $this->assertEquals($states,       $attributeForm->customFieldDataData);
            $this->assertEquals($state_labels, $attributeForm->customFieldDataLabels);

            $cities = array(
                'aa1',
                'ab1',
                'ac1',
                'aa2',
                'ab2',
                'ac2',
                'aa3',
                'ab3',
                'ac3',
                'ba1',
                'bb1',
                'bc1',
                'ba2',
                'bb2',
                'bc2',
                'ba3',
                'bb3',
                'bc3',
                'ca1',
                'cb1',
                'cc1',
                'ca2',
                'cb2',
                'cc2',
                'ca3',
                'cb3',
                'cc3',
            );
            $city_labels = array('fr' => array('aa1 fr', 'ab1 fr', 'ac1 fr', 'aa2 fr', 'ab2 fr', 'ac2 fr', 'aa3 fr', 'ab3 fr', 'ac3 fr', 
                                               'ba1 fr', 'bb1 fr', 'bc1 fr', 'ba2 fr', 'bb2 fr', 'bc2 fr', 'ba3 fr', 'bb3 fr', 'bc3 fr', 
                                               'ca1 fr', 'cb1 fr', 'cc1 fr', 'ca2 fr', 'cb2 fr', 'cc2 fr', 'ca3 fr', 'cb3 fr', 'cc3 fr'), 
                                 'de' => array('aa1 de', 'ab1 de', 'ac1 de', 'aa2 de', 'ab2 de', 'ac2 de', 'aa3 de', 'ab3 de', 'ac3 de', 
                                               'ba1 de', 'bb1 de', 'bc1 de', 'ba2 de', 'bb2 de', 'bc2 de', 'ba3 de', 'bb3 de', 'bc3 de', 
                                               'ca1 de', 'cb1 de', 'cc1 de', 'ca2 de', 'cb2 de', 'cc2 de', 'ca3 de', 'cb3 de', 'cc3 de'),
            );
            $statesFieldData = CustomFieldData::getByName('Cities');
            $statesFieldData->serializedData = serialize($cities);
            $this->assertTrue($statesFieldData->save());

            $attributeForm = new DropDownAttributeForm();
            $attributeForm->attributeName         = 'testCity';
            $attributeForm->attributeLabels       = array(
                'de' => 'Test City 2 de',
                'en' => 'Test City 2 en',
                'es' => 'Test City 2 es',
                'fr' => 'Test City 2 fr',
                'it' => 'Test City 2 it',
            );
            $attributeForm->isAudited             = true;
            $attributeForm->isRequired            = true;
            $attributeForm->customFieldDataData   = $cities;
            $attributeForm->customFieldDataName   = 'Cities';
            $attributeForm->customFieldDataLabels = $city_labels;

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
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'testCity');
            $this->assertEquals('DropDown',  $attributeForm->getAttributeTypeName());
            $this->assertEquals('testCity', $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test City 2 de',
                'en' => 'Test City 2 en',
                'es' => 'Test City 2 es',
                'fr' => 'Test City 2 fr',
                'it' => 'Test City 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,         $attributeForm->isAudited);
            $this->assertEquals(true,         $attributeForm->isRequired);
            $this->assertEquals('Cities',     $attributeForm->customFieldDataName);
            $this->assertEquals($cities,      $attributeForm->customFieldDataData);
            $this->assertEquals($city_labels, $attributeForm->customFieldDataLabels);

            $attributeName = "testLocation";
            $attributeForm = new DropDownDependencyAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Location Value 2 de',
                'en' => 'Test Location Value 2 en',
                'es' => 'Test Location Value 2 es',
                'fr' => 'Test Location Value 2 fr',
                'it' => 'Test Location Value 2 it',
            );
            $attributeForm->mappingData      = array(
                                                    array('attributeName'        => 'testCountry'),
                                                    array('attributeName'        => 'testState',
                                                          'valuesToParentValues' => array('aaa1' => 'aaaa',
                                                                                          'aaa2' => 'aaaa',
                                                                                          'aaa3' => 'aaaa',
                                                                                          'bbb1' => 'bbbb',
                                                                                          'bbb2' => 'bbbb',
                                                                                          'bbb3' => 'bbbb',
                                                                                          'ccc1' => 'cccc',
                                                                                          'ccc2' => 'cccc',
                                                                                          'ccc3' => 'cccc'
                                                                                  )
                                                    ),
                                                    array('attributeName'        => 'testCity',
                                                          'valuesToParentValues' => array('aa1' => 'aaa1',
                                                                                          'ab1' => 'aaa1',
                                                                                          'ac1' => 'aaa1',
                                                                                          'aa2' => 'aaa2',
                                                                                          'ab2' => 'aaa2',
                                                                                          'ac2' => 'aaa2',
                                                                                          'aa3' => 'aaa3',
                                                                                          'ab3' => 'aaa3',
                                                                                          'ac3' => 'aaa3',
                                                                                          'ba1' => 'bbb1',
                                                                                          'bb1' => 'bbb1',
                                                                                          'bc1' => 'bbb1',
                                                                                          'ba2' => 'bbb2',
                                                                                          'bb2' => 'bbb2',
                                                                                          'bc2' => 'bbb2',
                                                                                          'ba3' => 'bbb3',
                                                                                          'bb3' => 'bbb3',
                                                                                          'bc3' => 'bbb3',
                                                                                          'ca1' => 'ccc1',
                                                                                          'cb1' => 'ccc1',
                                                                                          'cc1' => 'ccc1',
                                                                                          'ca2' => 'ccc2',
                                                                                          'cb2' => 'ccc2',
                                                                                          'cc2' => 'ccc2',
                                                                                          'ca3' => 'ccc3',
                                                                                          'cb3' => 'ccc3',
                                                                                          'cc3' => 'ccc3'
                                                                                   )
                                                    ),
                                                    array('attributeName' => '')
                                               );

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

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), $attributeName);
            $this->assertEquals('DropDownDependency', $attributeForm->getAttributeTypeName());
            $this->assertEquals($attributeName,       $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Location Value 2 de',
                'en' => 'Test Location Value 2 en',
                'es' => 'Test Location Value 2 es',
                'fr' => 'Test Location Value 2 fr',
                'it' => 'Test Location Value 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
        }

        public function setAndGetDropDownDependencyAttributeWithTwoLevelDependency()
        {
            $education = array(
                'aaaa',
                'bbbb',
                'cccc',
            );
            $education_labels = array('fr' => array('aaaa fr', 'bbbb fr', 'cccc fr'),
                                      'de' => array('aaaa de', 'bbbb de', 'cccc de'),
            );
            $educationFieldData                   = CustomFieldData::getByName('Education');
            $educationFieldData->serializedData   = serialize($education);
            $this->assertTrue($educationFieldData->save());

            $attributeForm                        = new DropDownAttributeForm();
            $attributeForm->attributeName         = 'testEducation';
            $attributeForm->attributeLabels       = array(
                'de' => 'Test Education 2 de',
                'en' => 'Test Education 2 en',
                'es' => 'Test Education 2 es',
                'fr' => 'Test Education 2 fr',
                'it' => 'Test Education 2 it',
            );
            $attributeForm->isAudited             = true;
            $attributeForm->isRequired            = true;
            $attributeForm->customFieldDataData   = $education;
            $attributeForm->customFieldDataName   = 'Education';
            $attributeForm->customFieldDataLabels = $education_labels;

            $modelAttributesAdapterClassName      = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
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
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'testEducation');
            $this->assertEquals('DropDown',              $attributeForm->getAttributeTypeName());
            $this->assertEquals('testEducation',         $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Education 2 de',
                'en' => 'Test Education 2 en',
                'es' => 'Test Education 2 es',
                'fr' => 'Test Education 2 fr',
                'it' => 'Test Education 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,                    $attributeForm->isAudited);
            $this->assertEquals(true,                    $attributeForm->isRequired);
            $this->assertEquals('Education',             $attributeForm->customFieldDataName);
            $this->assertEquals($education,              $attributeForm->customFieldDataData);
            $this->assertEquals($education_labels,       $attributeForm->customFieldDataLabels);

            $stream = array(
                'aaa1',
                'aaa2',
                'aaa3',
                'bbb1',
                'bbb2',
                'bbb3',
                'ccc1',
                'ccc2',
                'ccc3',
            );
            $stream_labels = array('fr' => array('aaa1 fr', 'aaa2 fr', 'aaa3 fr', 'bbb1 fr', 'bbb2 fr', 'bbb3 fr', 'ccc1 fr', 'ccc2 fr', 'ccc3 fr'),
                                   'de' => array('aaa1 de', 'aaa2 de', 'aaa3 de', 'bbb1 de', 'bbb2 de', 'bbb3 de', 'ccc1 de', 'ccc2 de', 'ccc3 de'),
            );
            $streamFieldData                      = CustomFieldData::getByName('Stream');
            $streamFieldData->serializedData      = serialize($stream);
            $this->assertTrue($streamFieldData->save());

            $attributeForm                        = new DropDownAttributeForm();
            $attributeForm->attributeName         = 'testStream';
            $attributeForm->attributeLabels       = array(
                'de' => 'Test Stream 2 de',
                'en' => 'Test Stream 2 en',
                'es' => 'Test Stream 2 es',
                'fr' => 'Test Stream 2 fr',
                'it' => 'Test Stream 2 it',
            );
            $attributeForm->isAudited             = true;
            $attributeForm->isRequired            = true;
            $attributeForm->customFieldDataData   = $stream;
            $attributeForm->customFieldDataName   = 'Stream';
            $attributeForm->customFieldDataLabels = $stream_labels;

            $modelAttributesAdapterClassName      = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter                              = new $modelAttributesAdapterClassName(new Account());
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
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($account, 'testStream');
            $this->assertEquals('DropDown',     $attributeForm->getAttributeTypeName());
            $this->assertEquals('testStream',   $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Stream 2 de',
                'en' => 'Test Stream 2 en',
                'es' => 'Test Stream 2 es',
                'fr' => 'Test Stream 2 fr',
                'it' => 'Test Stream 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,           $attributeForm->isAudited);
            $this->assertEquals(true,           $attributeForm->isRequired);
            $this->assertEquals('Stream',       $attributeForm->customFieldDataName);
            $this->assertEquals($stream,        $attributeForm->customFieldDataData);
            $this->assertEquals($stream_labels, $attributeForm->customFieldDataLabels);

            $attributeName = "testQualification";
            $attributeForm = new DropDownDependencyAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Qualification Value 2 de',
                'en' => 'Test Qualification Value 2 en',
                'es' => 'Test Qualification Value 2 es',
                'fr' => 'Test Qualification Value 2 fr',
                'it' => 'Test Qualification Value 2 it',
            );
            $attributeForm->mappingData      = array(
                                                    array('attributeName'        => 'testEducation'),
                                                    array('attributeName'        => 'testStream',
                                                          'valuesToParentValues' => array('aaa1' => 'aaaa',
                                                                                          'aaa2' => 'aaaa',
                                                                                          'aaa3' => 'aaaa',
                                                                                          'bbb1' => 'bbbb',
                                                                                          'bbb2' => 'bbbb',
                                                                                          'bbb3' => 'bbbb',
                                                                                          'ccc1' => 'cccc',
                                                                                          'ccc2' => 'cccc',
                                                                                          'ccc3' => 'cccc'
                                                                                  )
                                                    ),
                                                    array('attributeName' => ''),
                                                    array('attributeName' => '')
                                               );

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

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), $attributeName);
            $this->assertEquals('DropDownDependency', $attributeForm->getAttributeTypeName());
            $this->assertEquals($attributeName,       $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Qualification Value 2 de',
                'en' => 'Test Qualification Value 2 en',
                'es' => 'Test Qualification Value 2 es',
                'fr' => 'Test Qualification Value 2 fr',
                'it' => 'Test Qualification Value 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
        }

        /**
         * @depends testSetAndGetDropDownDependencyAttribute
         */
        public function testSetAndGetCalculatedNumberAttribute()
        {
            $attributeName = 'testCalculatedValue';
            $attributeForm = new CalculatedNumberAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Calculated Value 2 de',
                'en' => 'Test Calculated Value 2 en',
                'es' => 'Test Calculated Value 2 es',
                'fr' => 'Test Calculated Value 2 fr',
                'it' => 'Test Calculated Value 2 it',
            );
            $attributeForm->formula          = 'testCurrency2 + testDecimal2';

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

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), $attributeName);
            $this->assertEquals('CalculatedNumber', $attributeForm->getAttributeTypeName());
            $this->assertEquals($attributeName,     $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Calculated Value 2 de',
                'en' => 'Test Calculated Value 2 en',
                'es' => 'Test Calculated Value 2 es',
                'fr' => 'Test Calculated Value 2 fr',
                'it' => 'Test Calculated Value 2 it',
            );
            $this->assertEquals($compareAttributeLabels,        $attributeForm->attributeLabels);
            $this->assertEquals('testCurrency2 + testDecimal2', $attributeForm->formula);
        }

        /**
         * @depends testSetAndGetCalculatedNumberAttribute
         */
        public function testSetAndGetPhoneAttribute()
        {
            $this->setAndGetPhoneAttribute('testPhone2', true);
            $this->setAndGetPhoneAttribute('testPhone3', false);
        }

        protected function setAndGetPhoneAttribute($attributeName, $withDefaultData)
        {
            $this->assertTrue(isset($attributeName) && $attributeName != '');
            $this->assertTrue(isset($withDefaultData) && is_bool($withDefaultData));

            $attributeForm = new PhoneAttributeForm();
            $attributeForm->attributeName = $attributeName;
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

            if ($withDefaultData)
            {
                $attributeForm->defaultValue  = '1-800-111-2233';
            }

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

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), $attributeName);
            $this->assertEquals('Phone',          $attributeForm->getAttributeTypeName());
            $this->assertEquals($attributeName,     $attributeForm->attributeName);
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

            if ($withDefaultData)
            {
                $this->assertEquals('1-800-111-2233', $attributeForm->defaultValue);
            }
            else
            {
                $this->assertEquals(null,             $attributeForm->defaultValue);
            }
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
            $this->setAndGetTextAttribute('testText2', true);
            $this->setAndGetTextAttribute('testText3', false);
        }

        protected function setAndGetTextAttribute($attributeName, $withDefaultData)
        {
            $this->assertTrue(isset($attributeName) && $attributeName != '');
            $this->assertTrue(isset($withDefaultData) && is_bool($withDefaultData));

            $attributeForm = new TextAttributeForm();
            $attributeForm->attributeName = $attributeName;
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

            if ($withDefaultData)
            {
                $attributeForm->defaultValue  = 'Kangaroo';
            }

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

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), $attributeName);
            $this->assertEquals('Text',        $attributeForm->getAttributeTypeName());
            $this->assertEquals($attributeName,   $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Text 2 de',
                'en' => 'Test Text 2 en',
                'es' => 'Test Text 2 es',
                'fr' => 'Test Text 2 fr',
                'it' => 'Test Text 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,          $attributeForm->isAudited);
            $this->assertEquals(true,          $attributeForm->isRequired);
            $this->assertEquals(50,            $attributeForm->maxLength);

            if ($withDefaultData)
            {
                $this->assertEquals('Kangaroo',    $attributeForm->defaultValue);
            }
            else
            {
                $this->assertEquals(null,          $attributeForm->defaultValue);
            }
        }

        /**
         * @depends testSetAndGetTextAttribute
         */
        public function testSetAndGetTextAreaAttribute()
        {
            $this->setAndGetTextAreaAttribute('testTextArea2', true);
            $this->setAndGetTextAreaAttribute('testTextArea3', false);
        }

        protected function setAndGetTextAreaAttribute($attributeName, $withDefaultData)
        {
            $this->assertTrue(isset($attributeName) && $attributeName != '');
            $this->assertTrue(isset($withDefaultData) && is_bool($withDefaultData));

            $attributeForm = new TextAreaAttributeForm();
            $attributeForm->attributeName = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Text Area 2 de',
                'en' => 'Test Text Area 2 en',
                'es' => 'Test Text Area 2 es',
                'fr' => 'Test Text Area 2 fr',
                'it' => 'Test Text Area 2 it',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = true;

            if ($withDefaultData)
            {
                $attributeForm->defaultValue  = 'Kangaroo Pouch';
            }

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

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), $attributeName);
            $this->assertEquals('TextArea',         $attributeForm->getAttributeTypeName());
            $this->assertEquals($attributeName,     $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Text Area 2 de',
                'en' => 'Test Text Area 2 en',
                'es' => 'Test Text Area 2 es',
                'fr' => 'Test Text Area 2 fr',
                'it' => 'Test Text Area 2 it',
            );
            $this->assertEquals($compareAttributeLabels, $attributeForm->attributeLabels);
            $this->assertEquals(true,                    $attributeForm->isAudited);
            $this->assertEquals(true,                    $attributeForm->isRequired);

            if ($withDefaultData)
            {
                $this->assertEquals('Kangaroo Pouch',    $attributeForm->defaultValue);
            }
            else
            {
                $this->assertEquals(null,                $attributeForm->defaultValue);
            }
        }

        /**
         * @depends testSetAndGetTextAreaAttribute
         */
        public function testSetAndGetUrlAttribute()
        {
            $this->setAndGetUrlAttribute('testUrl2', true);
            $this->setAndGetUrlAttribute('testUrl3', false);
        }

        protected function setAndGetUrlAttribute($attributeName, $withDefaultData)
        {
            $this->assertTrue(isset($attributeName) && $attributeName != '');
            $this->assertTrue(isset($withDefaultData) && is_bool($withDefaultData));

            $attributeForm = new UrlAttributeForm();
            $attributeForm->attributeName = $attributeName;
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

            if ($withDefaultData)
            {
                $attributeForm->defaultValue  = 'http://www.outback.com';
            }

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

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Account(), $attributeName);
            $this->assertEquals('Url',                    $attributeForm->getAttributeTypeName());
            $this->assertEquals($attributeName,           $attributeForm->attributeName);
            $compareAttributeLabels = array(
                'de' => 'Test Url 2 de',
                'en' => 'Test Url 2 en',
                'es' => 'Test Url 2 es',
                'fr' => 'Test Url 2 fr',
                'it' => 'Test Url 2 it',
            );
            $this->assertEquals($compareAttributeLabels,  $attributeForm->attributeLabels);
            $this->assertEquals(true,                     $attributeForm->isAudited);
            $this->assertEquals(true,                     $attributeForm->isRequired);
            $this->assertEquals(50,                       $attributeForm->maxLength);

            if ($withDefaultData)
            {
                $this->assertEquals('http://www.outback.com', $attributeForm->defaultValue);
            }
            else
            {
                $this->assertEquals(null,                     $attributeForm->defaultValue);
            }
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
            $account->name                              = 'my test account';
            $account->owner                             = Yii::app()->user->userModel;
            $account->testCheckBox2                     = 0;
            $account->testCurrency2->value              = 728.89;
            $account->testCurrency2->currency           = $currencies[0];
            $account->testDate2                         = '2008-09-03';
            $account->testDate3                         = '2008-09-02';
            $account->testDateTime2                     = '2008-09-02 03:03:03';
            $account->testDateTime3                     = '2008-09-01 03:03:03';
            $account->testDecimal2                      = 45.67;
            $account->testDecimal3                      = 31.05;
            $account->testAirPlane->value               = 'A380'; //Dive Bomber
            $account->testInteger2                      = 56;
            $account->testInteger3                      = 21;
            $account->testPhone2                        = '345345234';
            $account->testPhone3                        = '345345221';
            $account->testAirPlaneParts->value          = 'Seat'; // Wheel
            $account->testText2                         = 'some test stuff';
            $account->testText3                         = 'some test stuff 3';
            $account->testTextArea2                     = 'some test text area stuff';
            $account->testTextArea3                     = 'some test text area stuff 3';
            $account->testUrl2                          = 'http://www.zurmo.com';
            $account->testUrl3                          = 'http://www.zurmo.org';
            $account->playMyFavoriteSong->value         = 'song2'; // song 3
            $account->testCountry->value                = 'bbbb';
            $account->testState->value                  = 'bbb2';
            $account->testCity->value                   = 'bc2';
            $account->testEducation->value              = 'cccc';
            $account->testStream->value                 = 'ccc3';

            //Set value to Multiselect list.
            $customHobbyValue1                          = new CustomFieldValue();
            $customHobbyValue1->value                   = 'Reading';
            $account->testHobbies1->values->add($customHobbyValue1);
            $customHobbyValue2                          = new CustomFieldValue();
            $customHobbyValue2->value                   = 'Singing';
            $account->testHobbies2->values->add($customHobbyValue2);
            //Set value to Tagcloud.
            $customLanguageValue1                       = new CustomFieldValue();
            $customLanguageValue1->value                = 'English';
            $account->testLanguages1->values->add($customLanguageValue1);
            $customLanguageValue2                       = new CustomFieldValue();
            $customLanguageValue2->value                = 'Spanish';
            $account->testLanguages2->values->add($customLanguageValue2);

            unset($customHobbyValue1);
            unset($customHobbyValue2);
            unset($customLanguageValue1);
            unset($customLanguageValue2);

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
            $this->assertContains('Writing',                 $account->testHobbies1->values);
            $this->assertContains('Reading',                 $account->testHobbies1->values);
            $this->assertContains('Singing',                 $account->testHobbies2->values);
            $this->assertContains('English',                 $account->testLanguages1->values);
            $this->assertContains('French',                  $account->testLanguages1->values);
            $this->assertContains('Spanish',                 $account->testLanguages2->values);
            $this->assertEquals('bbbb',                      $account->testCountry->value);
            $this->assertEquals('bbb2',                      $account->testState->value);
            $this->assertEquals('bc2',                       $account->testCity->value);
            $this->assertEquals('cccc',                      $account->testEducation->value);
            $this->assertEquals('ccc3',                      $account->testStream->value);
            $metadata              = CalculatedDerivedAttributeMetadata::
                                     getByNameAndModelClassName('testCalculatedValue', 'Account');
            $testCalculatedValue   = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $account);
            $this->assertEquals(774.56,                      (double)$testCalculatedValue);

            unset($testCalculatedValue);
            $account->forget();
            unset($account);
            $account = Account::getById($accountId);
            //Switch values around to cover for any default value pollution on the assertions above.
            $account->testCheckBox2                     = 1;
            $account->testCurrency2->value              = 728.92;
            $account->testCurrency2->currency           = $currencies[0];
            $account->testDate2                         = '2008-09-04';
            $account->testDateTime2                     = '2008-09-03 03:03:03';
            $account->testDecimal2                      = 45.68;
            $account->testAirPlane->value               = 'Dive Bomber';
            $account->testInteger2                      = 57;
            $account->testPhone2                        = '3453452344';
            $account->testAirPlaneParts->value          = 'Wheel';
            $account->testText2                         = 'some test stuff2';
            $account->testTextArea2                     = 'some test text area stuff2';
            $account->testUrl2                          = 'http://www.zurmo.org';
            $account->playMyFavoriteSong->value         = 'song3';
            $account->testCountry->value                = 'cccc'; 
            $account->testState->value                  = 'ccc3'; 
            $account->testCity->value                   = 'ca3'; 
            $account->testEducation->value              = 'aaaa';
            $account->testStream->value                 = 'aaa1';

            $account->testHobbies1->values->removeAll();
            $account->testHobbies2->values->removeAll();
            $account->testLanguages1->values->removeAll();
            $account->testLanguages2->values->removeAll();

            $this->assertEquals(0, $account->testHobbies1->values->count());
            $this->assertEquals(0, $account->testHobbies2->values->count());
            $this->assertEquals(0, $account->testLanguages1->values->count());
            $this->assertEquals(0, $account->testLanguages2->values->count());

            //Set multiple value to Multiselect list.
            $customHobbyValue1                          = new CustomFieldValue();
            $customHobbyValue1->value                   = 'Writing';
            $account->testHobbies1->values->add($customHobbyValue1);
            $customHobbyValue2                          = new CustomFieldValue();
            $customHobbyValue2->value                   = 'Reading';
            $account->testHobbies1->values->add($customHobbyValue2);

            $customHobbyValue3                          = new CustomFieldValue();
            $customHobbyValue3->value                   = 'Singing';
            $account->testHobbies2->values->add($customHobbyValue3);
            $customHobbyValue4                          = new CustomFieldValue();
            $customHobbyValue4->value                   = 'Surfing';
            $account->testHobbies2->values->add($customHobbyValue4);
            $customHobbyValue5                          = new CustomFieldValue();
            $customHobbyValue5->value                   = 'Reading';
            $account->testHobbies2->values->add($customHobbyValue5);

            //Set multiple value to Tagcloud.
            $customLanguageValue1                       = new CustomFieldValue();
            $customLanguageValue1->value                = 'English';
            $account->testLanguages1->values->add($customLanguageValue1);
            $customLanguageValue2                       = new CustomFieldValue();
            $customLanguageValue2->value                = 'Danish';
            $account->testLanguages1->values->add($customLanguageValue2);
            $customLanguageValue3                       = new CustomFieldValue();
            $customLanguageValue3->value                = 'Spanish';
            $account->testLanguages1->values->add($customLanguageValue3);

            $customLanguageValue4                       = new CustomFieldValue();
            $customLanguageValue4->value                = 'French';
            $account->testLanguages2->values->add($customLanguageValue4);
            $customLanguageValue5                       = new CustomFieldValue();
            $customLanguageValue5->value                = 'Spanish';
            $account->testLanguages2->values->add($customLanguageValue5);

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
            $this->assertEquals(2,                            $account->testHobbies1->values->count());
            $this->assertEquals(3,                            $account->testHobbies2->values->count());
            $this->assertEquals(3,                            $account->testLanguages1->values->count());
            $this->assertEquals(2,                            $account->testLanguages2->values->count());
            $this->assertContains('Writing',                  $account->testHobbies1->values);
            $this->assertContains('Reading',                  $account->testHobbies1->values);
            $this->assertContains('Singing',                  $account->testHobbies2->values);
            $this->assertContains('Surfing',                  $account->testHobbies2->values);
            $this->assertContains('Reading',                  $account->testHobbies2->values);
            $this->assertContains('English',                  $account->testLanguages1->values);
            $this->assertContains('Danish',                   $account->testLanguages1->values);
            $this->assertContains('Spanish',                  $account->testLanguages1->values);
            $this->assertContains('French',                   $account->testLanguages2->values);
            $this->assertContains('Spanish',                  $account->testLanguages2->values);
            $this->assertEquals('cccc',                       $account->testCountry->value);
            $this->assertEquals('ccc3',                       $account->testState->value);
            $this->assertEquals('ca3',                        $account->testCity->value);
            $this->assertEquals('aaaa',                       $account->testEducation->value);
            $this->assertEquals('aaa1',                       $account->testStream->value);
        }

        /**
         * @depends testPopulateCustomAttributes
         */
        public function testPopulateCustomAttributesWithAValueTooLarge()
        {
            $this->setAndGetTextAttribute('testTextSpecial', true);
            $account = new Account();
            $account->testTextSpecial = 'asdasdasdasdasdasdasdasdasdasdsadasdasdasdasdasdasdasdasdasdasdasdasdasdasdasdsadas' .
                                        'asdasdasdasdasdasdasdasdasdasdsadasdasdasdasdasdasdasdasdasdasdasdasdasdasdasdsadas' .
                                        'asdasdasdasdasdasdasdasdasdasdsadasdasdasdasdasdasdasdasdasdasdasdasdasdasdasdsadas';
            $saved  = $account->save();
            $this->assertFalse($saved);
            $errors = $account->getErrors();
            $this->assertEquals('Test Text 2 en is too long (maximum is 50 characters).', $errors['testTextSpecial'][0]);
        }
    }
?>