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

    class AuditingTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $user = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $user;
            $user = new User();
            $user->username  = 'jimmy';
            $user->firstName = 'James';
            $user->lastName  = 'Boondog';
            assert($user->save()); // Not Coding Standard
            assert(AuditEvent::getCount() == 4); // Not Coding Standard
        }

        public function testLogAuditEventsListForUser()
        {
            Yii::app()->user->userModel = User::getByUsername('jimmy');
            $beforeCount = AuditEvent::getCount();

            // To be called just after login and just before logout.
            $this->assertTrue(AuditEvent::logAuditEvent('UsersModule', UsersModule::AUDIT_EVENT_USER_LOGGED_IN));
            $this->assertEquals($beforeCount + 1, AuditEvent::getCount());

            $this->assertTrue(AuditEvent::logAuditEvent('UsersModule', UsersModule::AUDIT_EVENT_USER_LOGGED_OUT));
            $this->assertEquals($beforeCount + 2, AuditEvent::getCount());

            $AuditEventsList = AuditEvent::getTailEvents(2);
            $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                'James Boondog, User Logged In/',
                                UsersModule::stringifyAuditEvent($AuditEventsList[0]));
            $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                'James Boondog, User Logged Out/',
                                UsersModule::stringifyAuditEvent($AuditEventsList[1]));

            $user = new User();
            $user->username  = 'benedict';
            $user->firstName = 'Benedict';
            $user->lastName  = 'Niñero';
            $this->assertTrue($user->save());
            $this->assertEquals($beforeCount + 3, AuditEvent::getCount());

            $AuditEventsList = AuditEvent::getTailEvents(1);
            $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                'James Boondog, Item Created, '                 .
                                'User\([0-9]+\), Benedict Niñero/',               // Not Coding Standard
                                ZurmoModule::stringifyAuditEvent($AuditEventsList[0]));

            $user->delete();

            $AuditEventsList = AuditEvent::getTailEvents(2);
            $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                'James Boondog, Item Created, '                 .
                                'User\([0-9]+\), Benedict Niñero/',               // Not Coding Standard
                                ZurmoModule::stringifyAuditEvent($AuditEventsList[0]));
            $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                'James Boondog, Item Deleted, '                 .
                                'User\([0-9]+\), Benedict Niñero/',               // Not Coding Standard
                                ZurmoModule::stringifyAuditEvent($AuditEventsList[1]));
        }

        public function testLogAuditEventsListForCreatingAndDeletingItems()
        {
            Yii::app()->user->userModel = User::getByUsername('jimmy');
            $beforeCount = AuditEvent::getCount();

            $account = new Account();
            $account->name = 'Yoddle';
            $this->assertTrue($account->save());
            $this->assertEquals($beforeCount + 1, AuditEvent::getCount());

            $account->delete();
            $this->assertEquals($beforeCount + 2, AuditEvent::getCount());

            $AuditEventsList = AuditEvent::getTailEvents(2);
            $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                'James Boondog, Item Created, '                 .
                                'Account\([0-9]+\), Yoddle/',                     // Not Coding Standard
                                ZurmoModule::stringifyAuditEvent($AuditEventsList[0]));
            $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                'James Boondog, Item Deleted, '                 .
                                'Account\([0-9]+\), Yoddle/',                     // Not Coding Standard
                                ZurmoModule::stringifyAuditEvent($AuditEventsList[1]));
        }

        public function testLogAuditEventChangingItemMembers()
        {
            Yii::app()->user->userModel = User::getByUsername('jimmy');
            $beforeCount = AuditEvent::getCount();

            $account = new Account();
            $account->name = 'Dooble';
            $this->assertTrue($account->save());
            $this->assertEquals($beforeCount + 1, AuditEvent::getCount());

            $account->name = 'Dooble'; // Change to same thing, no audit Event.
            $this->assertTrue($account->save());
            $this->assertEquals($beforeCount + 1, AuditEvent::getCount());

            $account->name = 'Kookle';
            $this->assertTrue($account->save());
            $this->assertEquals($beforeCount + 2, AuditEvent::getCount());

            $account->officePhone = 'klm-noodles'; // No event until save.
            $this->assertEquals($beforeCount + 2, AuditEvent::getCount());

            $account->officeFax = '555-dontcall';
            $this->assertEquals($beforeCount + 2, AuditEvent::getCount());

            $account->website = 'http://example.com';
            $this->assertEquals($beforeCount + 2, AuditEvent::getCount());

            $account->annualRevenue = 4039311;
            $this->assertEquals($beforeCount + 2, AuditEvent::getCount());

            //Several attributes are not audited by default.
            $this->assertTrue($account->save());
            $this->assertEquals($beforeCount + 4, AuditEvent::getCount());

            $AuditEventsList = AuditEvent::getTailEvents(3);
            $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' .    // Not Coding Standard
                                'James Boondog, Item Modified, '       .
                                'Account\([0-9]+\), Kookle, ' .                      // Not Coding Standard
                                'Changed Name from Dooble to Kookle/',
                                ZurmoModule::stringifyAuditEvent($AuditEventsList[0]));
            $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' .    // Not Coding Standard
                                'James Boondog, Item Modified, '                .
                                'Account\([0-9]+\), Kookle, '                   .    // Not Coding Standard
                                'Changed Office Phone from \(None\) to klm-noodles/',
                                ZurmoModule::stringifyAuditEvent($AuditEventsList[1]));
            $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' .    // Not Coding Standard
                                'James Boondog, Item Modified, '                .
                                'Account\([0-9]+\), Kookle, '                   .    // Not Coding Standard
                                'Changed Office Fax from \(None\) to 555-dontcall/', // Not Coding Standard
                                ZurmoModule::stringifyAuditEvent($AuditEventsList[2]));
            $account->name = 'Bookle';
            $this->assertEquals($beforeCount + 4, AuditEvent::getCount());
            $this->assertTrue($account->save());
            $this->assertEquals($beforeCount + 5, AuditEvent::getCount());

            $AuditEventsList = AuditEvent::getTailEvents(1);
            $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' .    // Not Coding Standard
                                'James Boondog, Item Modified, '       .
                                'Account\([0-9]+\), Bookle, ' .                      // Not Coding Standard
                                'Changed Name from Kookle to Bookle/',
                                ZurmoModule::stringifyAuditEvent($AuditEventsList[0]));
        }

        public function testLogAuditEventChangingNonOwnedRelationsToOtherModels()
        {
            Yii::app()->user->userModel = User::getByUsername('jimmy');
            $beforeCount = AuditEvent::getCount();

            $account1 = new Account();
            $account1->name = 'Giggle';
            $this->assertTrue($account1->save());
            $this->assertEquals($beforeCount + 1, AuditEvent::getCount());

            $account2 = new Account();
            $account2->name = 'Gargle';
            $this->assertTrue($account2->save());
            $this->assertEquals($beforeCount + 2, AuditEvent::getCount());

            $contact = new Contact();
            $contact->lastName = 'Smith';
            $contact->account = $account1;
            $contact->state->name = 'Warped';
            $contact->state->order = 1;
            $this->assertTrue($contact->save());
            $this->assertEquals($beforeCount + 3, AuditEvent::getCount());

            $contact->account = $account1; // Change to same thing, no audit Event.
            $this->assertTrue($contact->save());
            $this->assertEquals($beforeCount + 3, AuditEvent::getCount());

            $contact->account = $account2;
            $this->assertTrue($contact->save());
            $this->assertEquals($beforeCount + 4, AuditEvent::getCount());

            $AuditEventsList = AuditEvent::getTailEvents(1);
            $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                'James Boondog, Item Modified, '                .
                                'Contact\([0-9]+\), Smith, '                    . // Not Coding Standard
                                'Changed Account from Account\([0-9]+\) Giggle' . // Not Coding Standard
                                ' to Account\([0-9]+\) Gargle/',                  // Not Coding Standard
                                ZurmoModule::stringifyAuditEvent($AuditEventsList[0]));
        }

        public function testLogAuditEventChangingOwnedRelationsAttributes()
        {
            Yii::app()->user->userModel = User::getByUsername('jimmy');
            $beforeCount = AuditEvent::getCount();

            $account = new Account();
            $account->name = 'Nubble';
            $account->billingAddress->street1 = '29 Wherever St';
            $this->assertTrue($account->save());
            $this->assertEquals($beforeCount + 1, AuditEvent::getCount());

            $account->billingAddress->street1 = '229 Wherever St';
            $account->industry->value         = 'Automotive';

            try // Test saving owned related model.
            {
                $billingAddress = $account->billingAddress;
                $billingAddress->save(); // Can't save directly.
                $this->fail();
            }
            catch (NotSupportedException $e)
            {
            }

            try
            {
                $account->billingAddress->save(); // Or this way.
                $this->fail();
            }
            catch (NotSupportedException $e)
            {
            }

            try // Test saving owned related CustomField.
            {
                $industry = $account->industry;
                $industry->save(); // Can't save directly.
                $this->fail();
            }
            catch (NotSupportedException $e)
            {
            }

            try
            {
                $account->industry->save(); // Or this way.
                $this->fail();
            }
            catch (NotSupportedException $e)
            {
            }

            $this->assertTrue($account->save()); // Must save through the Item.
            $this->assertEquals($beforeCount + 3, AuditEvent::getCount());

            $AuditEventsList = AuditEvent::getTailEvents(2);
            $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                'James Boondog, Item Modified, '                .
                                'Account\([0-9]+\), Nubble, '                   . // Not Coding Standard
                                'Changed Billing Address Street 1 from 29 Wherever St to 229 Wherever St/',
                                ZurmoModule::stringifyAuditEvent($AuditEventsList[0]));
            $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                'James Boondog, Item Modified, '                .
                                'Account\([0-9]+\), Nubble, '                   . // Not Coding Standard
                                'Changed Industry Value from \(None\) to '      . // Not Coding Standard
                                'Automotive/',
                                ZurmoModule::stringifyAuditEvent($AuditEventsList[1]));
        }

        public function testLogAuditEventChangingUsersPassword()
        {
            Yii::app()->user->userModel = User::getByUsername('jimmy');
            $beforeCount = AuditEvent::getCount();

            $user = new User();
            $user->username  = 'eddy';
            $user->firstName = 'Ed';
            $user->lastName  = 'Gein';
            $this->assertTrue($user->save());
            $this->assertEquals($beforeCount + 1, AuditEvent::getCount());

            $user->setPassword('waggles');
            $this->assertTrue($user->save());
            $this->assertEquals($beforeCount + 2, AuditEvent::getCount());

            $user->setPassword('bibbler');
            $this->assertTrue($user->save());
            $this->assertEquals($beforeCount + 3, AuditEvent::getCount());

            $AuditEventsList = AuditEvent::getTailEvents(2);
            $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                'James Boondog, User Password Changed/',
                                ZurmoModule::stringifyAuditEvent($AuditEventsList[0]));
            $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                'James Boondog, User Password Changed/',
                                ZurmoModule::stringifyAuditEvent($AuditEventsList[0]));
        }

        public function testLogAuditEventForEachType()
        {
            if (!RedBeanDatabase::isFrozen())
            {
                Yii::app()->user->userModel = User::getByUsername('jimmy');
                $beforeCount = AuditEvent::getCount();

                $item = new AuditTestItem();
                $this->assertTrue($item->save());
                $this->assertEquals($beforeCount + 1, AuditEvent::getCount());

                $auditEvents = AuditEvent::getTailEvents(1);
                $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                    'James Boondog, Item Created, '                 .
                                    'AuditTestItem\([0-9]+\), \(None\)/',             // Not Coding Standard
                                    ZurmoModule::stringifyAuditEvent($auditEvents[0]));

                $item->dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - 120);
                $item->date     = '2010-12-20';
                $item->float    = 3.14159;
                $item->integer  = 666;
                $item->time     = '11:59';
                $this->assertTrue($item->save());
                $this->assertEquals($beforeCount + 6, AuditEvent::getCount());
                $auditEvents = AuditEvent::getTailEvents(5);
                $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                    'James Boondog, Item Modified, '                .
                                    'AuditTestItem\([0-9]+\), \(None\), '           . // Not Coding Standard
                                    'Changed Date Time from \(None\) to [0-9]+/',      // Not Coding Standard
                                    ZurmoModule::stringifyAuditEvent($auditEvents[0]));
                $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                    'James Boondog, Item Modified, '                .
                                    'AuditTestItem\([0-9]+\), \(None\), '           . // Not Coding Standard
                                    'Changed Date from \(None\) to 2010-12-20/',      // Not Coding Standard
                                    ZurmoModule::stringifyAuditEvent($auditEvents[1]));
                $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                    'James Boondog, Item Modified, '                .
                                    'AuditTestItem\([0-9]+\), \(None\), '           . // Not Coding Standard
                                    'Changed Float from \(None\) to 3.14159/',
                                    ZurmoModule::stringifyAuditEvent($auditEvents[2]));
                $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                    'James Boondog, Item Modified, '                .
                                    'AuditTestItem\([0-9]+\), \(None\), '           . // Not Coding Standard
                                    'Changed Integer from \(None\) to 666/',          // Not Coding Standard
                                    ZurmoModule::stringifyAuditEvent($auditEvents[3]));
                $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                    'James Boondog, Item Modified, '                .
                                    'AuditTestItem\([0-9]+\), \(None\), '           . // Not Coding Standard
                                    'Changed Time from \(None\) to 11:59/',           // Not Coding Standard
                                    ZurmoModule::stringifyAuditEvent($auditEvents[4]));

                $item->dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                $item->date     = '2012-01-22';
                $item->float    = 6.626068E-34;
                $item->integer  = 69;
                $item->time     = '12:00';
                $this->assertTrue($item->save());
                $this->assertEquals($beforeCount + 11, AuditEvent::getCount());

                $auditEvents = AuditEvent::getTailEvents(5);
/*
                $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                    'James Boondog, Item Modified, '                .
                                    'AuditTestItem\([0-9]+\), \(None\), '           . // Not Coding Standard
                                    'Changed Date Time from [0-9]+ to [0-9]+/',        // Not Coding Standard
                                    ZurmoModule::stringifyAuditEvent($auditEvents[0]));
*/
                $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                    'James Boondog, Item Modified, '                .
                                    'AuditTestItem\([0-9]+\), \(None\), '           . // Not Coding Standard
                                    'Changed Date from 2010-12-20 to 2012-01-22/',
                                    ZurmoModule::stringifyAuditEvent($auditEvents[1]));
                $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                    'James Boondog, Item Modified, '                .
                                    'AuditTestItem\([0-9]+\), \(None\), '           . // Not Coding Standard
                                    'Changed Float from 3.14159 to 6.626068E-34/',
                                    ZurmoModule::stringifyAuditEvent($auditEvents[2]));
                $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                    'James Boondog, Item Modified, '                .
                                    'AuditTestItem\([0-9]+\), \(None\), '           . // Not Coding Standard
                                    'Changed Integer from 666 to 69/',
                                    ZurmoModule::stringifyAuditEvent($auditEvents[3]));
                $this->assertRegExp('/[0-9]+\/[0-9]+\/[0-9]+ [0-9]+:[0-9]+ [AP]M, ' . // Not Coding Standard
                                    'James Boondog, Item Modified, '                .
                                    'AuditTestItem\([0-9]+\), \(None\), '           . // Not Coding Standard
                                    'Changed Time from 11:59 to 12:00/',
                                    ZurmoModule::stringifyAuditEvent($auditEvents[4]));
            }
        }
    }
?>
