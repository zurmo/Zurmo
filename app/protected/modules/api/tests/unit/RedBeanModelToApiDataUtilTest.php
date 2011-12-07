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

    class RedBeanModelToApiDataUtilTest extends BaseTest
    {
        public $freeze = false;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
        }

        public function setUp(){
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
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $testItem = new ApiModelTestItem();
            $testItem->firstName = 'Bob';
            $testItem->lastName  = 'Bob';
            $testItem->boolean   = true;
            $testItem->date      = '2002-04-03';
            $testItem->dateTime  = '2002-04-03 02:00:43';
            $testItem->float     = 54.22;
            $testItem->integer   = 10;
            $testItem->phone     = '21313213';
            $testItem->string    = 'aString';
            $testItem->textArea  = 'Some Text Area';
            $testItem->url       = 'http://www.asite.com';
            $testItem->owner     = $super;
            $createStamp         = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($testItem->save());
            $id = $testItem->id;
            $testItem->forget();
            unset($testItem);

            $testItem    = ApiModelTestItem::getById($id);
            $adapter     = new RedBeanModelToApiDataUtil($testItem);
            $data        = $adapter->getData();
            $compareData = array(
                'id'                => $id,
                'firstName'         => 'Bob',
                'lastName'          => 'Bob',
                'boolean'           => 1,
                'date'              => '2002-04-03',
                'dateTime'          => '2002-04-03 02:00:43',
                'float'             => 54.22,
                'integer'           => 10,
                'phone'             => '21313213',
                'string'            => 'aString',
                'textArea'		    => 'Some Text Area',
                'url'               => 'http://www.asite.com',
                'currencyValue'     => null,
                'dropDown'          => null,
                'radioDropDown'     => null,
                'hasOne'            => null,
                'hasOneAlso'        => null,
                'primaryEmail'      => null,
                'primaryAddress'    => null,
                'secondaryEmail'    => null,
                'owner' => array(
                    'id' => $super->id,
                    'username' => 'super'
                ),
                'createdDateTime'  => $createStamp,
                'modifiedDateTime' => $createStamp,
                'createdByUser'    => array(
                    'id' => $super->id,
                    'username' => 'super'
                ),
                'modifiedByUser' => array(
                    'id' => $super->id,
                    'username' => 'super'
                )
            );
            $this->assertEquals($compareData, $data);
        }

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
            $customFieldData = CustomFieldData::getByName('ApiTestDropDown');
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            $this->assertTrue($saved);

            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);

            $testItem = new ApiModelTestItem();
            $testItem->firstName     = 'Bob2';
            $testItem->lastName      = 'Bob2';
            $testItem->boolean       = true;
            $testItem->date          = '2002-04-03';
            $testItem->dateTime      = '2002-04-03 02:00:43';
            $testItem->float         = 54.22;
            $testItem->integer       = 10;
            $testItem->phone         = '21313213';
            $testItem->string        = 'aString';
            $testItem->textArea      = 'Some Text Area';
            $testItem->url           = 'http://www.asite.com';
            $testItem->owner         = $super;
            $testItem->currencyValue = $currencyValue;
            $testItem->dropDown->value = $values[1];
            $createStamp             = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($testItem->save());
            $id = $testItem->id;
            $testItem->forget();
            unset($testItem);

            $testItem    = ApiModelTestItem::getById($id);
            $adapter     = new RedBeanModelToApiDataUtil($testItem);
            $data        = $adapter->getData();
            $compareData = array(
                'id'                => $id,
                'firstName'         => 'Bob2',
                'lastName'          => 'Bob2',
                'boolean'           => 1,
                'date'              => '2002-04-03',
                'dateTime'          => '2002-04-03 02:00:43',
                'float'             => 54.22,
                'integer'           => 10,
                'phone'             => '21313213',
                'string'            => 'aString',
                'textArea'		    => 'Some Text Area',
                'url'               => 'http://www.asite.com',
                'currencyValue'     => array(
                    'id'         => $currencyValue->id,
                    'value'      => 100,
                    'rateToBase' => 1,
                    'currency'   => array(
                        'id'     => $currencies[0]->id,
                    ),
                ),
                'dropDown'          => array(
                    'id'         => $testItem->dropDown->id,
                    'value'      => $values[1],
                ),
                'radioDropDown'     => null,
                'hasOne'            => null,
                'hasOneAlso'        => null,
                'primaryEmail'      => null,
                'primaryAddress'    => null,
                'secondaryEmail'    => null,
                'owner' => array(
                    'id' => $super->id,
                    'username' => 'super'
                ),
                'createdDateTime'  => $createStamp,
                'modifiedDateTime' => $createStamp,
                'createdByUser'    => array(
                    'id' => $super->id,
                    'username' => 'super'
                ),
                'modifiedByUser' => array(
                    'id' => $super->id,
                    'username' => 'super'
                )
            );
            $this->assertEquals($compareData, $data);
        }

        public function testGetDataWithHasOneRelatedModel()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);

            $testItem2 = new ApiModelTestItem2();
            $testItem2->name     = 'John';
            $this->assertTrue($testItem2->save());

            $testItem4 = new ApiModelTestItem4();
            $testItem4->name     = 'John';
            $this->assertTrue($testItem4->save());

            //HAS_MANY and MANY_MANY relationships should be ignored.
            $testItem3_1 = new ApiModelTestItem3();
            $testItem3_1->name     = 'Kevin';
            $this->assertTrue($testItem3_1->save());

            $testItem3_2 = new ApiModelTestItem3();
            $testItem3_2->name     = 'Jim';
            $this->assertTrue($testItem3_2->save());

            $testItem = new ApiModelTestItem();
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
            $testItem->owner         = $super;
            $testItem->currencyValue = $currencyValue;
            $testItem->hasOne        = $testItem2;
            $testItem->hasMany->add($testItem3_1);
            $testItem->hasMany->add($testItem3_2);
            $testItem->hasOneAlso    = $testItem4;
            $createStamp             = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($testItem->save());
            $id = $testItem->id;
            $testItem->forget();
            unset($testItem);

            $testItem    = ApiModelTestItem::getById($id);
            $adapter     = new RedBeanModelToApiDataUtil($testItem);
            $data        = $adapter->getData();;
            $compareData = array(
                        'id'                => $id,
                        'firstName'         => 'Bob3',
                        'lastName'          => 'Bob3',
                        'boolean'           => 1,
                        'date'              => '2002-04-03',
                        'dateTime'          => '2002-04-03 02:00:43',
                        'float'             => 54.22,
                        'integer'           => 10,
                        'phone'             => '21313213',
                        'string'            => 'aString',
                        'textArea'          => 'Some Text Area',
                        'url'               => 'http://www.asite.com',
                        'currencyValue'     => array(
                            'id'         => $currencyValue->id,
                            'value'      => 100,
                            'rateToBase' => 1,
                            'currency'   => array(
                                'id'     => $currencies[0]->id,
                            ),
                        ),
                        'dropDown'          => null,
                        'radioDropDown'     => null,
                        'hasOne'            => array('id' => $testItem2->id),
                        'hasOneAlso'        => array('id' => $testItem4->id),
                        'primaryEmail'      => null,
                        'primaryAddress'    => null,
                        'secondaryEmail'    => null,
                        'owner' => array(
                            'id' => $super->id,
                            'username' => 'super'
                        ),
                        'createdDateTime'  => $createStamp,
                        'modifiedDateTime' => $createStamp,
                        'createdByUser'    => array(
                            'id' => $super->id,
                            'username' => 'super'
                        ),
                        'modifiedByUser' => array(
                            'id' => $super->id,
                            'username' => 'super'
                        )
            );
            $this->assertEquals($compareData, $data);
        }
    }
?>