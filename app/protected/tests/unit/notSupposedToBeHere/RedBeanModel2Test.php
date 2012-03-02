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

    // These tests were split out of RedBeanModelTest because they
    // depend on modules in the application. RedBeanModelTest was
    // put back in extensions/zurmoinc/framework/test/unit.
    // These tests need to be put in the right places given
    // what they depend on.
    class RedBeanModel2Test extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function testBulkSetAndGetWithRelatedModels()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user = User::getByUsername('bobby');
            $_FAKEPOST = array(
                'Account' => array(
                    'name'          => 'Vomitorio Corp',
                    'officePhone'   => '123456789',
                    'officeFax'      => null,
                    'industry'      => array(
                        'value' => 'Automotive',
                    ),
                    'owner'         => array(
                        'id' => $user->id,
                    ),
                    'employees'     => 3,
                    'annualRevenue' => null,
                    'type'          => array(
                        'value' => 'Customer',
                    ),
                    'website'       => 'http://barf.com',
                    'billingAddress' => array(
                        'street1'    => '123 Road Rd',
                        'street2'    => null,
                        'city'       => 'Cityville',
                        'postalCode' => '12345',
                        'country'    => 'Countrilia',
                    ),
                    'shippingAddress' => array(
                        'street1'    => '456 Street St',
                        'street2'    => 'Apartment 2A',
                        'city'       => 'Cityville',
                        'postalCode' => '12345',
                        'country'    => 'Countrilia',
                    ),
                    'description' => 'a description',
                ),
            );

            $account = new Account();
            $account->setAttributes($_FAKEPOST['Account']);
            $this->assertEquals('Vomitorio Corp',  $account->name);
            $this->assertEquals('123456789',       $account->officePhone);
            $this->assertNull  (                   $account->officeFax);
            $this->assertEquals('Automotive',      $account->industry->value);
            $this->assertEquals(3,                 $account->employees);
            $this->assertNull  (                   $account->annualRevenue);
            $this->assertEquals('Customer' ,       $account->type->value);
            $this->assertEquals('http://barf.com', $account->website);
            $this->assertEquals('a description',   $account->description);
            $this->assertEquals('123 Road Rd',     $account->billingAddress->street1);
            $this->assertEquals('',                $account->billingAddress->street2);
            $this->assertEquals('Cityville',       $account->billingAddress->city);
            $this->assertEquals('12345',           $account->billingAddress->postalCode);
            $this->assertEquals('Countrilia',      $account->billingAddress->country);
            $this->assertEquals('456 Street St',   $account->shippingAddress->street1);
            $this->assertEquals('Apartment 2A',    $account->shippingAddress->street2);
            $this->assertEquals('Cityville',       $account->shippingAddress->city);
            $this->assertEquals('12345',           $account->shippingAddress->postalCode);
            $this->assertEquals('Countrilia',      $account->shippingAddress->country);
            $this->assertEquals($user->id,         $account->owner->id);
        }

        /**
         * @depends testBulkSetAndGetWithRelatedModels
         * @expectedException NotFoundException
         */
        public function testBulkSetAndGetWithRelatedModelsWithBadId()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user = User::getByUsername('bobby');
            $_FAKEPOST = array(
                'Account' => array(
                    'name'          => 'Vomitorio Corp',
                    'officePhone'   => '123456789',
                    'officeFax'     => null,
                    'industry'      => array(
                        'value' => 'Automotive',
                    ),
                    'employees'     => 3,
                    'annualRevenue' => null,
                    'type'          => array(
                        'id' => 666, // This will cause...
                    ),
                    'website'       => 'http://barf.com',
                    'billingAddress' => array(
                        'street1'    => '123 Road Rd',
                        'street2'    => null,
                        'city'       => 'Cityville',
                        'postalCode' => '12345',
                        'country'    => 'Countrilia',
                    ),
                        'shippingAddress' => array(
                        'street1'    => '456 Street St',
                        'street2'    => 'Apartment 2A',
                        'city'       => 'Cityville',
                        'postalCode' => '12345',
                        'country'    => 'Countrilia',
                    ),
                    'description' => 'a description',
                ),
            );

            $account = new Account();
            $account->setAttributes($_FAKEPOST['Account']); // ...this to blow.
        }

        // Should work as per MiscTest::testEmptyAndIsSetBehaviour.
        public function testUsingIssetOrEmptyOnPropertiesOfObject()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $_FAKEPOST = array(
                'Address' => array(
                    'street1'    => '123 Road Rd',
                    'street2'    => null,
                    'city'       => 'Cityville',
                    'postalCode' => '12345',
                    'country'    => 'Countrilia',
                ),
            );

            $address = new Address();
            $address->setAttributes($_FAKEPOST['Address']);
            $this->assertEquals('123 Road Rd', $address->street1);
            $this->assertTrue  (         isset($address->street1));
            $this->assertTrue  (        !empty($address->street1));

            unset($address->street1);
            $this->assertTrue  (        !isset($address->street1));
            $this->assertTrue  (         empty($address->street1));

            $this->assertTrue  (        !isset($address->street3));
        }

        /**
         * @depends testUsingIssetOrEmptyOnPropertiesOfObject
         */
        public function testUsingIssetOrEmptyOnPropertiesOfRelatedObject()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $_FAKEPOST = array(
                'Account' => array(
                    'name'          => 'Vomitorio Corp',
                    'officePhone'   => '123456789',
                    'officeFax'     => null,
                    'employees'     => 3,
                    'annualRevenue' => null,
                    'website'       => 'http://barf.com',
                    'billingAddress' => array(
                        'street1'    => '123 Road Rd',
                        'street2'    => null,
                        'city'       => 'Cityville',
                        'postalCode' => '12345',
                        'country'    => 'Countrilia',
                    ),
                    'description' => 'a description',
                ),
            );

            $account = new Account();
            $account->setAttributes($_FAKEPOST['Account']);
            $this->assertEquals('123 Road Rd', $account->billingAddress->street1);
            $this->assertTrue  (         isset($account->billingAddress->street1));
            $this->assertTrue  (        !empty($account->billingAddress->street1));
        }

        public function testRequiredRelatedModel2()
        {
            $perUserMetadata = new PerUserMetadata();
            $this->assertFalse($perUserMetadata->validate());
            $errors = $perUserMetadata->getErrors();
            $this->assertEquals(3, count($errors));
            $this->assertEquals('Class Name cannot be blank.',          $errors['className']         [0]);
            $this->assertEquals('Serialized Metadata cannot be blank.', $errors['serializedMetadata'][0]);
            $this->assertEquals('Username cannot be blank.',            $errors['user']['username']  [0]);
            $this->assertEquals('Last Name cannot be blank.',           $errors['user']['lastName']  [0]);
        }

        /**
         * @depends testBulkSetAndGetWithRelatedModels
         */
        public function testEmptyPostValueForDropDownOnSave()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $_FAKEPOST = array(
                'Account' => array(
                    'name'          => 'Vomitorio Corp 2',
                    'officePhone'   => '123456789',
                    'officeFax'     => null,
                    'employees'     => 3,
                    'annualRevenue' => null,
                    'website'       => 'http://barf.com',
                    'billingAddress' => array(
                        'street1'    => '123 Road Rd',
                        'street2'    => null,
                        'city'       => 'Cityville',
                        'postalCode' => '12345',
                        'country'    => 'Countrilia',
                    ),
                    'description' => 'a description',
                    'industry' => array(
                        'id' => null,
                    ),
                    'owner' => array(
                        'id' => User::getByUsername('bobby')->id,
                    ),
                ),
            );

            $account = new Account();
            $account->setAttributes($_FAKEPOST['Account']);
            $this->assertTrue($account->save());
        }

        /**
         * @depends testBulkSetAndGetWithRelatedModels
         */
        public function testEmptyPostValueForUserOnSave()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $_FAKEPOST = array(
                'Account' => array(
                    'name'          => 'Vomitorio Corp 2',
                    'officePhone'   => '123456789',
                    'officeFax'     => null,
                    'employees'     => 3,
                    'annualRevenue' => null,
                    'website'       => 'http://barf.com',
                    'billingAddress' => array(
                        'street1'    => '123 Road Rd',
                        'street2'    => null,
                        'city'       => 'Cityville',
                        'postalCode' => '12345',
                        'country'    => 'Countrilia',
                    ),
                    'description' => 'a description',
                    'owner' => array(
                        'id' => '', // Blank now means the same as null.
                    ),
                ),
            );

            $user = User::getByUsername('bobby');

            $account = new Account();
            $account->owner = $user;
            $account->setAttributes($_FAKEPOST['Account']);
            $this->assertFalse($account->save());
            $errors = $account->getErrors();
            $this->assertEquals('Username cannot be blank.',  $errors['owner']['username'][0]);
            $this->assertEquals('Last Name cannot be blank.', $errors['owner']['lastName'][0]);
        }

        public function testNewCustomFieldNotModified()
        {
            $customField = new CustomField();
            $this->assertTrue ($customField->id < 0);
            $this->assertFalse($customField->isModified());
        }

        public function testNewUserNotModified()
        {
            $user = new User();
            $this->assertTrue ($user->id < 0);
            $this->assertFalse($user->isModified());
            $this->assertTrue ($user->title->id < 0);
            $this->assertFalse($user->title->isModified());
        }

        /**
         * @depends testNewUserNotModified
         */
        public function testNewAccountNotModified()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $account = new Account();
            $this->assertTrue ($account->id < 0);
            $this->assertFalse($account->isModified());
        }

        /**
         * @depends testBulkSetAndGetWithRelatedModels
         */
        public function testEmptyPostValueForRequiredRelations()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $_FAKEPOST = array(
                'Account' => array(
                    'name'          => 'Vomitorio Corp 2',
                    'officePhone'   => '123456789',
                    'officeFax'     => null,
                    'employees'     => 3,
                    'annualRevenue' => null,
                    'website'       => 'http://barf.com',
                    'billingAddress' => array(
                        'street1'    => '123 Road Rd',
                        'street2'    => null,
                        'city'       => 'Cityville',
                        'postalCode' => '12345',
                        'country'    => 'Countrilia',
                    ),
                    'description' => 'a description',
                    'owner' => array( // Is required.
                        'id' => '', // Blank now means the same as null.
                    ),
                    'industry' => array( // Isn't required.
                        'id' => '', // Blank now means the same as null.
                    ),
                ),
            );

            $user = User::getByUsername('bobby');

            $account = new Account();
            $account->owner = $user;
            $account->setAttributes($_FAKEPOST['Account']);
            $this->assertFalse($account->validate());
            $errors = $account->getErrors();
            $this->assertEquals(1, count($errors));
            $this->assertEquals('Username cannot be blank.',  $errors['owner']['username'][0]);
            $this->assertEquals('Last Name cannot be blank.', $errors['owner']['lastName'][0]);
        }

        /**
         * @depends testBulkSetAndGetWithRelatedModels
         */
        public function testEmptyPostValueSavingAsZeros()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $_FAKEPOST = array(
                'Account' => array(
                    'name'          => 'Vomitorio Corp 2',
                    'officePhone'   => '123456789',
                    'officeFax'     => '',
                    'employees'     => 3,
                    'annualRevenue' => null,
                    'website'       => 'http://barf.com',
                    'billingAddress' => array(
                        'street1'    => '123 Road Rd',
                        'street2'    => null,
                        'city'       => 'Cityville',
                        'postalCode' => '12345',
                        'country'    => 'Countrilia',
                    ),
                    'description' => 'a description',
                ),
            );

            $user = User::getByUsername('bobby');

            $account = new Account();
            $account->owner = $user;
            $account->setAttributes($_FAKEPOST['Account']);
            $this->assertTrue($account->save());
            $account = Account::getById($account->id);
            $this->assertEmpty($account->officeFax);
            $this->assertNotSame(0, $account->officeFax);
            $this->assertNotSame(0, $account->billingAddress->street2);
        }

        public function testForgettingAModelWithAddingAManyToManyRelation()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            $contact = ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
            $contactId = $contact->id;
            OpportunityTestHelper::createOpportunityStagesIfDoesNotExist     ();
            $opportunity = OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp', $super, $account);
            $opportunityId = $opportunity->id;
            //Model forgets ok.
            $contact->forget();
            $contact = Contact::getById($contactId);
            $opportunity->contacts->add($contact);
            $opportunity->save();
            //Still forgets ok, because we are forgetting both opportunity and contact. If we forget just contact,
            //it would break later when you try to look at $contact->oppportunities or $opportunity->contacts
            $contact->forget();
            $opportunity->forget();
            $contact     = Contact::getById($contactId);
            $opportunity = Opportunity::getById($opportunityId);
            //Finds many-to-many relationships ok on both sides
            $this->assertEquals(1,            $opportunity->contacts->count());
            $this->assertEquals(1,            $contact->opportunities->count());
            $this->assertEquals($opportunity, $contact->opportunities[0]);
            $this->assertEquals($contact,     $opportunity->contacts[0]);
        }
    }
?>
