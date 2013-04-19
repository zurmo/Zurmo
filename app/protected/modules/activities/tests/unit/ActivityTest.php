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

    class ActivityTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testDownCasts()
        {
            $possibleDerivationPaths = array(
                                           array('SecurableItem', 'OwnedSecurableItem', 'Account'),
                                           array('SecurableItem', 'OwnedSecurableItem', 'Person', 'Contact'),
                                           array('SecurableItem', 'OwnedSecurableItem', 'Opportunity'),
                                       );

            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $account     = AccountTestHelper    ::createAccountByNameForOwner('Waggle', $super);
            $contact     = ContactTestHelper    ::createContactByNameForOwner('Noddy',  $super);
            $opportunity = OpportunityTestHelper::createOpportunityByNameForOwner('Noddy',  $super);

            $accountItem     = Item::getById($account    ->getClassId('Item'));
            $contactItem     = Item::getById($contact    ->getClassId('Item'));
            $opportunityItem = Item::getById($opportunity->getClassId('Item'));

            $this->assertTrue ($accountItem    ->isSame($account));
            $this->assertTrue ($contactItem    ->isSame($contact));
            $this->assertTrue ($opportunityItem->isSame($opportunity));

            $this->assertFalse($accountItem     instanceof Account);
            $this->assertFalse($contactItem     instanceof Contact);
            $this->assertFalse($opportunityItem instanceof Opportunity);

            $account2     = $accountItem    ->castDown($possibleDerivationPaths);
            $this->assertEquals('Account', get_class($account2));
            //Demonstrate a single array, making sure it casts down properly.
            $accountItem2     = Item::getById($account    ->getClassId('Item'));
            $account3 = $accountItem2    ->castDown(array(array('SecurableItem', 'OwnedSecurableItem', 'Account')));
            $this->assertEquals('Account', get_class($account3));
            $contact2     = $contactItem    ->castDown($possibleDerivationPaths);
            $opportunity2 = $opportunityItem->castDown($possibleDerivationPaths);

            $this->assertTrue($account2    ->isSame($account));
            $this->assertTrue($contact2    ->isSame($contact));
            $this->assertTrue($opportunity2->isSame($opportunity));

            $this->assertTrue($account2     instanceof Account);
            $this->assertTrue($contact2     instanceof Contact);
            $this->assertTrue($opportunity2 instanceof Opportunity);

            $account2 = AccountTestHelper::createAccountByNameForOwner('Waggle2', $super);
            //By adding a second contact with a relation to the account2, we can demonstrate a bug with how castDown works.
            //Since contacts can in fact be attached to accounts via account_id, if a contact exists connected to the account
            //we are trying to cast down, then this will cast down even though it shouldn't.
            $contact2 = ContactTestHelper::createContactWithAccountByNameForOwner('MrWaggle2',  $super, $account2);
            try
            {
                $account2CastedDown = $account2->castDown(array(array('SecurableItem', 'OwnedSecurableItem', 'Person', 'Contact')));
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                //success
            }
            //Now try to forget the account and retrieve it.
            $account2Id = $account2->id;
            $account2->forget();
            unset($account2);
            $account2 = Account::getById($account2Id);
            try
            {
                $account2CastedDown = $account2->castDown(array(array('SecurableItem', 'OwnedSecurableItem', 'Person', 'Contact')));
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                //success
            }
        }
    }
?>
