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
    * Test ModelToArrayAdapter functions.
    */
    class ZurmoCopyModelUtilTest extends ZurmoBaseTest
    {
        public $freeze = false;

        protected static $sally;

        protected static $groupA;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Currency::getAll(); //In order to create base Currency
            $currency = new Currency();
            $currency->code       = 'EUR';
            $currency->rateToBase = 2;
            $saved                = $currency->save();
            assert('$saved'); // Not Coding Standard
            self::$sally = UserTestHelper::createBasicUser('sally');
            $multiSelectValues = array(
                'Multi 1',
                'Multi 2',
                'Multi 3',
            );
            $customFieldData = CustomFieldData::getByName('TestMultiDropDown');
            $customFieldData->serializedData = serialize($multiSelectValues);
            $saved = $customFieldData->save();
            assert('$saved'); // Not Coding Standard

            $tagCloudValues = array(
                'Cloud 1',
                'Cloud 2',
                'Cloud 3',
            );
            $customFieldData = CustomFieldData::getByName('TestTagCloud');
            $customFieldData->serializedData = serialize($tagCloudValues);
            $saved = $customFieldData->save();
            assert('$saved'); // Not Coding Standard
            $values = array(
                'Test1',
                'Test2',
                'Test3',
                'Sample',
                'Demo',
            );
            $customFieldData = CustomFieldData::getByName('TestDropDown');
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            assert('$saved'); // Not Coding Standard
            $a = new Group();
            $a->name = 'AAA';
            $saved = $a->save();
            assert('$saved'); // Not Coding Standard
            self::$groupA = $a;
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
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function teardown()
        {
            if ($this->freeze)
            {
                RedBeanDatabase::freeze();
            }
            parent::teardown();
        }

        public function testCopy()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = Currency::getByCode('EUR');
            $testItem = new ModelToArrayAdapterTestItem();
            $testItem->firstName     = 'Bob';
            $testItem->lastName      = 'Bobson';
            $testItem->boolean       = true;
            $testItem->date          = '2002-04-03';
            $testItem->dateTime      = '2002-04-03 02:00:43';
            $testItem->float         = 54.22;
            $testItem->integer       = 10;
            $testItem->phone         = '21313213';
            $testItem->string        = 'aString';
            $testItem->textArea      = 'Some Text Area';
            $testItem->url           = 'http://www.asite.com';
            $testItem->primaryEmail->emailAddress = 'bob.bobson@something.com';
            $testItem->primaryAddress->street1    = 'some street';


            $testItem->owner         = Yii::app()->user->userModel;
            $testItem->currencyValue = $currencyValue;

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

            $testItem->dropDown->value = 'Sample';

            $testItem2 = new ModelToArrayAdapterTestItem2();
            $testItem2->name     = 'John';
            $this->assertTrue($testItem2->save());

            $testItem4 = new ModelToArrayAdapterTestItem4();
            $testItem4->name     = 'John';
            $this->assertTrue($testItem4->save());

            //HAS_MANY and MANY_MANY relationships should be ignored.
            $testItem3_1 = new ModelToArrayAdapterTestItem3();
            $testItem3_1->name     = 'Kevin';
            $this->assertTrue($testItem3_1->save());

            $testItem3_2 = new ModelToArrayAdapterTestItem3();
            $testItem3_2->name     = 'Jim';
            $this->assertTrue($testItem3_2->save());
            $testItem->hasOne        = $testItem2;
            $testItem->hasMany->add($testItem3_1);
            $testItem->hasMany->add($testItem3_2);
            $testItem->hasOneAlso    = $testItem4;


            $this->assertTrue($testItem->save());
            $testItem->addPermissions(self::$groupA, Permission::READ_WRITE_CHANGE_PERMISSIONS_CHANGE_OWNER);
            $this->assertTrue($testItem->save());
            $id = $testItem->id;
            $testItem->forget();
            unset($testItem);

            //Switch to super and copy the model
            Yii::app()->user->userModel = User::getByUsername('super');
            $testItem    = ModelToArrayAdapterTestItem::getById($id);

            $copyToItem  = new ModelToArrayAdapterTestItem();
            ZurmoCopyModelUtil::copy($testItem, $copyToItem);

            $this->assertEquals('Bob',                      $copyToItem->firstName);
            $this->assertEquals('Bobson',                   $copyToItem->lastName);
            $this->assertEquals(true,                       $copyToItem->boolean);
            $this->assertEquals('2002-04-03',               $copyToItem->date);
            $this->assertEquals('2002-04-03 02:00:43',      $copyToItem->dateTime);
            $this->assertEquals(54.22,                      $copyToItem->float);
            $this->assertEquals(10,                         $copyToItem->integer);
            $this->assertEquals('21313213',                 $copyToItem->phone);
            $this->assertEquals('aString',                  $copyToItem->string);
            $this->assertEquals('Some Text Area',           $copyToItem->textArea);
            $this->assertEquals('http://www.asite.com',     $copyToItem->url);
            $this->assertEquals('bob.bobson@something.com', $copyToItem->primaryEmail->emailAddress);
            $this->assertEquals('some street',              $copyToItem->primaryAddress->street1);
            $this->assertEquals('Sample',                   $copyToItem->dropDown->value);
            $this->assertEquals(2,                          $copyToItem->multiDropDown->values->count());
            $this->assertTrue($copyToItem->multiDropDown->values[0] == 'Multi 1' ||
                              $copyToItem->multiDropDown->values[0] == 'Multi 3');
            $this->assertEquals(2,                          $copyToItem->tagCloud->values->count());
            $this->assertTrue($copyToItem->tagCloud->values[0] == 'Cloud 2' ||
                              $copyToItem->tagCloud->values[0] == 'Cloud 3');
            $this->assertEquals(100,                        $copyToItem->currencyValue->value);
            $this->assertEquals(2,                          $copyToItem->currencyValue->rateToBase);
            $this->assertEquals('EUR',                      $copyToItem->currencyValue->currency->code);
            $this->assertTrue($copyToItem->owner->isSame(self::$sally));
            $this->assertTrue($copyToItem->createdByUser->id < 0);
            $this->assertEquals(Yii::app()->user->userModel->id, $copyToItem->modifiedByUser->id);
            $this->assertEquals(0, $copyToItem->hasMany->count());
            $this->assertTrue($copyToItem->hasOne->isSame($testItem2));
            $this->assertTrue($copyToItem->hasOneAlso->isSame($testItem4));

            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($copyToItem);
            $permitables                       = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(1, count($permitables));
            $this->assertEquals('AAA', $permitables[self::$groupA->id]->name);
        }
    }
?>