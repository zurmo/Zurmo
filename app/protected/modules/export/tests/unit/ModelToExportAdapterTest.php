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

    /**
    * Test RedBeanModelAttributeValueToExportValueAdapter functions.
    */
    class ModelToExportAdapterTest extends ZurmoBaseTest
    {
        public $freeze = false;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
            $multiSelectValues = array(
                'Multi 1',
                'Multi 2',
                'Multi 3',
            );
            $customFieldData = CustomFieldData::getByName('ExportTestMultiDropDown');
            $customFieldData->serializedData = serialize($multiSelectValues);
            $saved = $customFieldData->save();
            assert('$saved'); // Not Coding Standard

            $tagCloudValues = array(
                'Cloud 1',
                'Cloud 2',
                'Cloud 3',
            );
            $customFieldData = CustomFieldData::getByName('ExportTestTagCloud');
            $customFieldData->serializedData = serialize($tagCloudValues);
            $saved = $customFieldData->save();
            assert('$saved'); // Not Coding Standard
        }

        public function setUp()
        {
            parent::setUp();
            $freeze = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $freeze = true;
            }
            $this->freeze = $freeze;
        }

        public function teardown()
        {
            if ($this->freeze)
            {
                RedBeanDatabase::freeze();
            }
            parent::teardown();
        }

        public function testGetDataWithNoRelationsSet()
        {
            $super                                  = User::getByUsername('super');
            Yii::app()->user->userModel             = $super;
            $testItem                               = new ExportTestModelItem();
            $testItem->firstName                    = 'Bob';
            $testItem->lastName                     = 'Bob';
            $testItem->boolean                      = true;
            $testItem->date                         = '2002-04-03';
            $testItem->dateTime                     = '2002-04-03 02:00:43';
            $testItem->float                        = 54.22;
            $testItem->integer                      = 10;
            $testItem->phone                        = '21313213';
            $testItem->string                       = 'aString';
            $testItem->textArea                     = 'Some Text Area';
            $testItem->url                          = 'http://www.asite.com';
            $testItem->email                        = 'a@a.com';
            $testItem->owner                        = $super;
            $testItem->primaryAddress->street1      = '129 Noodle Boulevard';
            $testItem->primaryAddress->street2      = 'Apartment 6000A';
            $testItem->primaryAddress->city         = 'Noodleville';
            $testItem->primaryAddress->postalCode   = '23453';
            $testItem->primaryAddress->country      = 'The Good Old US of A';

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 1';
            $testItem->multiDropDown->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 3';
            $testItem->multiDropDown->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 2';
            $testItem->tagCloud->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 3';
            $testItem->tagCloud->values->add($customFieldValue);

            $createStamp = strtotime(DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
            $this->assertTrue($testItem->save());
            $id = $testItem->id;
            $testItem->forget();
            unset($testItem);

            $testItem    = ExportTestModelItem::getById($id);
            $adapter     = new ModelToExportAdapter($testItem);
            $data        = $adapter->getData();
            $headerData  = $adapter->getHeaderData();
            $compareData = array(
                $id,
                'stubDateTime',
                'stubDateTime',
                'super',
                'super',
                'super',
                'Bob',
                'Bob',
                '1',
                '2002-04-03',
                '2002-04-03 02:00:43',
                54.22,
                10,
                '21313213',
                'aString',
                'Some Text Area',
                'http://www.asite.com',
                'a@a.com',
                null,
                null,
                null,
                null,
                null,
                'Multi 1,Multi 3', // Not Coding Standard
                'Cloud 2,Cloud 3', // Not Coding Standard
                null,
                null,
                null,
                null,
                null,
                'Noodleville',
                'The Good Old US of A',
                '23453',
                '129 Noodle Boulevard',
                'Apartment 6000A',
                null,
                null,
                null,
                null,
                null,
            );
            $compareHeaderData = array(
                $testItem->getAttributeLabel('id'),
                $testItem->getAttributeLabel('createdDateTime'),
                $testItem->getAttributeLabel('modifiedDateTime'),
                $testItem->getAttributeLabel('createdByUser'),
                $testItem->getAttributeLabel('modifiedByUser'),
                $testItem->getAttributeLabel('owner'),
                $testItem->getAttributeLabel('firstName'),
                $testItem->getAttributeLabel('lastName'),
                $testItem->getAttributeLabel('boolean'),
                $testItem->getAttributeLabel('date'),
                $testItem->getAttributeLabel('dateTime'),
                $testItem->getAttributeLabel('float'),
                $testItem->getAttributeLabel('integer'),
                $testItem->getAttributeLabel('phone'),
                $testItem->getAttributeLabel('string'),
                $testItem->getAttributeLabel('textArea'),
                $testItem->getAttributeLabel('url'),
                $testItem->getAttributeLabel('email'),
                $testItem->getAttributeLabel('currency'),
                $testItem->getAttributeLabel('currencyValue'),
                $testItem->getAttributeLabel('currencyValue') . ' ' . Zurmo::t('ZurmoModule', 'Currency'),
                $testItem->getAttributeLabel('dropDown'),
                $testItem->getAttributeLabel('radioDropDown'),
                $testItem->getAttributeLabel('multiDropDown'),
                $testItem->getAttributeLabel('tagCloud'),
                $testItem->getAttributeLabel('hasOne'),
                $testItem->getAttributeLabel('hasOneAlso'),
                'Primary Email - Email Address',
                'Primary Email - Is Invalid',
                'Primary Email - Opt Out',
                'Primary Address - City',
                'Primary Address - Country',
                'Primary Address - Postal Code',
                'Primary Address - Street 1',
                'Primary Address - Street 2',
                'Primary Address - State',
                'Secondary Email - Email Address',
                'Secondary Email - Is Invalid',
                'Secondary Email - Opt Out',
                $testItem->getAttributeLabel('user'),
            );

            // Because small delay in IO operation, allow tresholds
            $createdDateTimeKey = array_search($testItem->getAttributeLabel('createdDateTime'), $headerData);
            $modifiedDateTimeKey = array_search($testItem->getAttributeLabel('modifiedDateTime'), $headerData);
            $this->assertEquals($createStamp, strtotime($data[$createdDateTimeKey]), '', 2);
            $this->assertEquals($createStamp, strtotime($data[$modifiedDateTimeKey]), '', 2);
            $data[$createdDateTimeKey]  = 'stubDateTime';
            $data[$modifiedDateTimeKey] = 'stubDateTime';
            $this->assertEquals($compareData,       $data);
            $this->assertEquals($compareHeaderData, $headerData);
        }

        /**
         * @depends testGetDataWithNoRelationsSet
         */
        public function testGetDataWithAllHasOneOrOwnedRelationsSet()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $values = array(
                'Test1',
                'Test2',
                'Test3',
                'Sample',
                'Demo',
            );
            $customFieldData = CustomFieldData::getByName('ExportTestDropDown');
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            $this->assertTrue($saved);

            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);

            $testItem = new ExportTestModelItem();
            $testItem->firstName       = 'Bob2';
            $testItem->lastName        = 'Bob2';
            $testItem->boolean         = true;
            $testItem->date            = '2002-04-03';
            $testItem->dateTime        = '2002-04-03 02:00:43';
            $testItem->float           = 54.22;
            $testItem->integer         = 10;
            $testItem->phone           = '21313213';
            $testItem->string          = 'aString';
            $testItem->textArea        = 'Some Text Area';
            $testItem->url             = 'http://www.asite.com';
            $testItem->email           = 'a@a.com';
            $testItem->owner           = $super;
            $testItem->currencyValue   = $currencyValue;
            $testItem->dropDown->value = $values[1];
            $createStamp               = strtotime(DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
            $this->assertTrue($testItem->save());
            $id = $testItem->id;
            $testItem->forget();
            unset($testItem);

            $testItem    = ExportTestModelItem::getById($id);
            $adapter     = new ModelToExportAdapter($testItem);
            $data        = $adapter->getData();
            $headerData  = $adapter->getHeaderData();
            $compareData = array(
                $id,
                'stubDateTime',
                'stubDateTime',
                'super',
                'super',
                'super',
                'Bob2',
                'Bob2',
                '1',
                '2002-04-03',
                '2002-04-03 02:00:43',
                54.22,
                10,
                '21313213',
                'aString',
                'Some Text Area',
                'http://www.asite.com',
                'a@a.com',
                null,
                100,
                'USD',
                'Test2',
                'Test2',
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            );
            $compareHeaderData = array(
                $testItem->getAttributeLabel('id'),
                $testItem->getAttributeLabel('createdDateTime'),
                $testItem->getAttributeLabel('modifiedDateTime'),
                $testItem->getAttributeLabel('createdByUser'),
                $testItem->getAttributeLabel('modifiedByUser'),
                $testItem->getAttributeLabel('owner'),
                $testItem->getAttributeLabel('firstName'),
                $testItem->getAttributeLabel('lastName'),
                $testItem->getAttributeLabel('boolean'),
                $testItem->getAttributeLabel('date'),
                $testItem->getAttributeLabel('dateTime'),
                $testItem->getAttributeLabel('float'),
                $testItem->getAttributeLabel('integer'),
                $testItem->getAttributeLabel('phone'),
                $testItem->getAttributeLabel('string'),
                $testItem->getAttributeLabel('textArea'),
                $testItem->getAttributeLabel('url'),
                $testItem->getAttributeLabel('email'),
                $testItem->getAttributeLabel('currency'),
                $testItem->getAttributeLabel('currencyValue'),
                $testItem->getAttributeLabel('currencyValue') . ' ' . Zurmo::t('ZurmoModule', 'Currency'),
                $testItem->getAttributeLabel('dropDown'),
                $testItem->getAttributeLabel('radioDropDown'),
                $testItem->getAttributeLabel('multiDropDown'),
                $testItem->getAttributeLabel('tagCloud'),
                $testItem->getAttributeLabel('hasOne'),
                $testItem->getAttributeLabel('hasOneAlso'),
                'Primary Email - Email Address',
                'Primary Email - Is Invalid',
                'Primary Email - Opt Out',
                'Primary Address - City',
                'Primary Address - Country',
                'Primary Address - Postal Code',
                'Primary Address - Street 1',
                'Primary Address - Street 2',
                'Primary Address - State',
                'Secondary Email - Email Address',
                'Secondary Email - Is Invalid',
                'Secondary Email - Opt Out',
                $testItem->getAttributeLabel('user'),
            );

            // Because small delay in IO operation, allow tresholds
            $createdDateTimeKey = array_search($testItem->getAttributeLabel('createdDateTime'), $headerData);
            $modifiedDateTimeKey = array_search($testItem->getAttributeLabel('modifiedDateTime'), $headerData);
            $this->assertEquals($createStamp, strtotime($data[$createdDateTimeKey]), '', 2);
            $this->assertEquals($createStamp, strtotime($data[$modifiedDateTimeKey]), '', 2);
            $data[$createdDateTimeKey]  = 'stubDateTime';
            $data[$modifiedDateTimeKey] = 'stubDateTime';
            $this->assertEquals($compareData,       $data);
            $this->assertEquals($compareHeaderData, $headerData);
        }

        /**
         * @depends testGetDataWithAllHasOneOrOwnedRelationsSet
         */
        public function testGetDataWithHasOneRelatedModel()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);

            $testItem2 = new ExportTestModelItem2();
            $testItem2->name     = 'John';
            $this->assertTrue($testItem2->save());

            $testItem4 = new ExportTestModelItem4();
            $testItem4->name     = 'John';
            $this->assertTrue($testItem4->save());

            //HAS_MANY and MANY_MANY relationships should be ignored.
            $testItem3_1 = new ExportTestModelItem3();
            $testItem3_1->name     = 'Kevin';
            $this->assertTrue($testItem3_1->save());

            $testItem3_2 = new ExportTestModelItem3();
            $testItem3_2->name     = 'Jim';
            $this->assertTrue($testItem3_2->save());

            $testItem = new ExportTestModelItem();
            $testItem->firstName     = 'Bob3';
            $testItem->lastName      = 'Bob3';
            $testItem->boolean       = true;
            $testItem->date          = '2002-04-03';
            $testItem->dateTime      = '2002-04-03 02:00:43';
            $testItem->float         = 54.22;
            $testItem->integer       = 10;
            $testItem->phone         = '21313213';
            $testItem->string        = 'aString';
            $testItem->textArea      = 'Some Text Area';
            $testItem->url           = 'http://www.asite.com';
            $testItem->email         = 'a@a.com';
            $testItem->owner         = $super;
            $testItem->currencyValue = $currencyValue;
            $testItem->hasOne        = $testItem2;
            $testItem->hasMany->add($testItem3_1);
            $testItem->hasMany->add($testItem3_2);
            $testItem->hasOneAlso    = $testItem4;
            $createStamp             = strtotime(DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
            $this->assertTrue($testItem->save());
            $id = $testItem->id;
            $testItem->forget();
            unset($testItem);

            $testItem    = ExportTestModelItem::getById($id);
            $adapter     = new ModelToExportAdapter($testItem);
            $data        = $adapter->getData();
            $headerData  = $adapter->getHeaderData();
            $compareData = array(
                $id,
                'stubDateTime',
                'stubDateTime',
                'super',
                'super',
                'super',
                'Bob3',
                'Bob3',
                '1',
                '2002-04-03',
                '2002-04-03 02:00:43',
                54.22,
                10,
                '21313213',
                'aString',
                'Some Text Area',
                'http://www.asite.com',
                'a@a.com',
                null,
                100,
                'USD',
                null,
                null,
                null,
                null,
                '(None)',
                '(None)',
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            );
            $compareHeaderData = array(
                $testItem->getAttributeLabel('id'),
                $testItem->getAttributeLabel('createdDateTime'),
                $testItem->getAttributeLabel('modifiedDateTime'),
                $testItem->getAttributeLabel('createdByUser'),
                $testItem->getAttributeLabel('modifiedByUser'),
                $testItem->getAttributeLabel('owner'),
                $testItem->getAttributeLabel('firstName'),
                $testItem->getAttributeLabel('lastName'),
                $testItem->getAttributeLabel('boolean'),
                $testItem->getAttributeLabel('date'),
                $testItem->getAttributeLabel('dateTime'),
                $testItem->getAttributeLabel('float'),
                $testItem->getAttributeLabel('integer'),
                $testItem->getAttributeLabel('phone'),
                $testItem->getAttributeLabel('string'),
                $testItem->getAttributeLabel('textArea'),
                $testItem->getAttributeLabel('url'),
                $testItem->getAttributeLabel('email'),
                $testItem->getAttributeLabel('currency'),
                $testItem->getAttributeLabel('currencyValue'),
                $testItem->getAttributeLabel('currencyValue') . ' ' . 'Currency',
                $testItem->getAttributeLabel('dropDown'),
                $testItem->getAttributeLabel('radioDropDown'),
                $testItem->getAttributeLabel('multiDropDown'),
                $testItem->getAttributeLabel('tagCloud'),
                $testItem->getAttributeLabel('hasOne') . ' - ' . 'Name',
                $testItem->getAttributeLabel('hasOneAlso') . ' - ' . 'Name',
                'Primary Email - Email Address',
                'Primary Email - Is Invalid',
                'Primary Email - Opt Out',
                'Primary Address - City',
                'Primary Address - Country',
                'Primary Address - Postal Code',
                'Primary Address - Street 1',
                'Primary Address - Street 2',
                'Primary Address - State',
                'Secondary Email - Email Address',
                'Secondary Email - Is Invalid',
                'Secondary Email - Opt Out',
                $testItem->getAttributeLabel('user'),
            );

            // Because small delay in IO operation, allow tresholds
            $createdDateTimeKey = array_search($testItem->getAttributeLabel('createdDateTime'), $headerData);
            $modifiedDateTimeKey = array_search($testItem->getAttributeLabel('modifiedDateTime'), $headerData);
            $this->assertEquals($createStamp, strtotime($data[$createdDateTimeKey]), '', 2);
            $this->assertEquals($createStamp, strtotime($data[$modifiedDateTimeKey]), '', 2);
            $data[$createdDateTimeKey]  = 'stubDateTime';
            $data[$modifiedDateTimeKey] = 'stubDateTime';
            $this->assertEquals($compareData,       $data);
            $this->assertEquals($compareHeaderData, $headerData);
        }
    }
?>