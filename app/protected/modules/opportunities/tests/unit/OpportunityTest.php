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

    class OpportunityTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testCreateStageValues()
        {
            $stageValues = array(
                'Prospecting',
                'Negotiating',
                'Close Won',
            );
            $stageFieldData = CustomFieldData::getByName('SalesStages');
            $stageFieldData->serializedData = serialize($stageValues);
            $this->assertTrue($stageFieldData->save());
        }

        /**
         * @depends testCreateStageValues
         */
        public function testVariousCurrencyValues()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);
            $opportunity = new Opportunity();
            $opportunity->owner          = $super;
            $opportunity->name           = 'test';
            $opportunity->amount         = $currencyValue;
            $opportunity->closeDate      = '2011-01-01';
            $opportunity->stage->value   = 'Verbal';
            $saved                       = $opportunity->save();
            $this->assertTrue($saved);
            $opportunity1Id              = $opportunity->id;
            $opportunity->forget();

            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 800;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);
            $opportunity = new Opportunity();
            $opportunity->owner          = $super;
            $opportunity->name           = 'test';
            $opportunity->amount         = $currencyValue;
            $opportunity->closeDate      = '2011-01-01';
            $opportunity->stage->value   = 'Verbal';
            $saved                       = $opportunity->save();
            $this->assertTrue($saved);
            $opportunity2Id              = $opportunity->id;
            $opportunity->forget();
            $currencyValue->forget(); //need to forget this to pull the accurate value from the database

            $opportunity1 = Opportunity::getById($opportunity1Id);
            $this->assertEquals(100, $opportunity1->amount->value);

            $opportunity2 = Opportunity::getById($opportunity2Id);
            $this->assertEquals(800, $opportunity2->amount->value);

            $opportunity1->delete();
            $opportunity2->delete();
        }

        /**
         * @depends testVariousCurrencyValues
         */
        public function testCreateAndGetOpportunityById()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user = UserTestHelper::createBasicUser('Billy');
            $currencies    = Currency::getAll();
            $currencyValue = new CurrencyValue();
            $currencyValue->value = 500.54;
            $currencyValue->currency = $currencies[0];
            $opportunity = new Opportunity();
            $opportunity->owner        = $user;
            $opportunity->name         = 'Test Opportunity';
            $opportunity->amount       = $currencyValue;
            $opportunity->closeDate    = '2011-01-01'; //eventually fix to make correct format
            $opportunity->stage->value = 'Negotiating';
            $this->assertTrue($opportunity->save());
            $id = $opportunity->id;
            unset($opportunity);
            $opportunity = Opportunity::getById($id);
            $this->assertEquals('Test Opportunity', $opportunity->name);
            $this->assertEquals('500.54',      $opportunity->amount->value);
            $this->assertEquals('Negotiating', $opportunity->stage->value);
            $this->assertEquals('2011-01-01',    $opportunity->closeDate);
            $this->assertEquals(1, $currencies[0]->rateToBase);
        }

        /**
         * @depends testCreateAndGetOpportunityById
         */
        public function testGetOpportunitiesByName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $opportunities = Opportunity::getByName('Test Opportunity');
            $this->assertEquals(1, count($opportunities));
            $this->assertEquals('Test Opportunity', $opportunities[0]->name);
        }

        /**
         * @depends testCreateAndGetOpportunityById
         */
        public function testGetLabel()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $opportunities = Opportunity::getByName('Test Opportunity');
            $this->assertEquals(1, count($opportunities));
            $this->assertEquals('Opportunity',   $opportunities[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Opportunities', $opportunities[0]::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testGetOpportunitiesByName
         */
        public function testGetOpportunitiesByNameForNonExistentName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $opportunities = Opportunity::getByName('Test Opportunity 69');
            $this->assertEquals(0, count($opportunities));
        }

        /**
         * @depends testCreateAndGetOpportunityById
         */
        public function testGetAll()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user = User::getByUsername('billy');
            $currencies    = Currency::getAll();
            $currencyValue = new CurrencyValue();
            $currencyValue->value = 500.54;
            $currencyValue->currency = $currencies[0];
            $opportunity = new Opportunity();
            $opportunity->owner        = $user;
            $opportunity->name         = 'Test Opportunity 2';
            $opportunity->amount       = $currencyValue;
            $opportunity->closeDate    = '2011-01-01'; //eventually fix to make correct format
            $opportunity->stage->value = 'Negotiating';
            $this->assertTrue($opportunity->save());
            $opportunities = Opportunity::getAll();
            $this->assertEquals(2, count($opportunities));
            $this->assertTrue('Test Opportunity'   == $opportunities[0]->name &&
                              'Test Opportunity 2' == $opportunities[1]->name ||
                              'Test Opportunity 2' == $opportunities[0]->name &&
                              'Test Opportunity'   == $opportunities[1]->name);
            $this->assertEquals(1, $currencies[0]->rateToBase);
        }

        /**
         * @depends testCreateAndGetOpportunityById
         */
        public function testSetAndGetOwner()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user = UserTestHelper::createBasicUser('Dicky');

            $opportunities = Opportunity::getByName('Test Opportunity');
            $this->assertEquals(1, count($opportunities));
            $opportunity = $opportunities[0];
            $opportunity->owner = $user;
            $this->assertTrue($opportunity->save());
            unset($user);
            $this->assertTrue($opportunity->owner !== null);
            $opportunity->owner = null;
            $this->assertFalse($opportunity->validate());
            $opportunity->forget();
            unset($opportunity);
        }

        /**
         * @depends testSetAndGetOwner
         */
        public function testReplaceOwner()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $opportunities = Opportunity::getByName('Test Opportunity');
            $this->assertEquals(1, count($opportunities));
            $opportunity = $opportunities[0];
            $user = User::getByUsername('dicky');
            $this->assertEquals($user->id, $opportunity->owner->id);
            unset($user);
            $user2 = UserTestHelper::createBasicUser('Benny');
            $opportunity->owner = $user2;
            unset($user2);
            $this->assertTrue($opportunity->owner !== null);
            $user = $opportunity->owner;
            $this->assertEquals('benny', $user->username);
            unset($user);
        }

        /**
         * @depends testCreateAndGetOpportunityById
         */
        public function testUpdateOpportunityFromForm()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user = User::getByUsername('billy');
            $opportunities = Opportunity::getByName('Test Opportunity');
            $opportunity = $opportunities[0];
            $this->assertEquals($opportunity->name, 'Test Opportunity');
            $currencies    = Currency::getAll();
            $postData = array(
                'owner' => array(
                    'id' => $user->id,
                ),
                'name' => 'New Name',
                'amount' => array('value' => '500.54', 'currency' => array('id' => $currencies[0]->id)),
                'closeDate' => '2011-01-01',
                'stage' => array(
                    'value' => 'Negotiating'
                ),
            );
            $opportunity->setAttributes($postData);
            $this->assertTrue($opportunity->save());

            $id = $opportunity->id;
            unset($opportunity);
            $opportunity = Opportunity::getById($id);
            $this->assertEquals('New Name', $opportunity->name);
            $this->assertEquals(500.54,     $opportunity->amount->value);
            $this->assertEquals(1, $currencies[0]->rateToBase);
        }

        public function testDeleteOpportunity()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $opportunities = Opportunity::getAll();
            $this->assertEquals(2, count($opportunities));
            $opportunities[0]->delete();
            $opportunities = Opportunity::getAll();
            $this->assertEquals(1, count($opportunities));
            $opportunities[0]->delete();
            $opportunities = Opportunity::getAll();
            $this->assertEquals(0, count($opportunities));
            $currencies    = Currency::getAll();
            $this->assertEquals(1, $currencies[0]->rateToBase);
        }

        public function testGetAllWhenThereAreNone()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $opportunities = Opportunity::getAll();
            $this->assertEquals(0, count($opportunities));
        }

        /**
         * @depends testCreateAndGetOpportunityById
         */
        public function testSetStageAndSourceAndRetrieveDisplayName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user = User::getByUsername('billy');

            $stageValues = array(
                'Prospecting',
                'Negotiating',
                'Close Won',
            );
            $stageFieldData = CustomFieldData::getByName('SalesStages');
            $stageFieldData->serializedData = serialize($stageValues);
            $this->assertTrue($stageFieldData->save());

            $sourceValues = array(
                'Word of Mouth',
                'Outbound',
                'Trade Show',
            );
            $sourceFieldData = CustomFieldData::getByName('LeadSources');
            $sourceFieldData->serializedData = serialize($sourceValues);
            $this->assertTrue($sourceFieldData->save());

            $currencies    = Currency::getAll();
            $currencyValue = new CurrencyValue();
            $currencyValue->value = 500.54;
            $currencyValue->currency = $currencies[0];
            $opportunity = new Opportunity();
            $opportunity->owner        = $user;
            $opportunity->name         = '1000 Widgets';
            $opportunity->amount       = $currencyValue;
            $opportunity->closeDate    = '2011-01-01'; //eventually fix to make correct format
            $opportunity->stage->value = $stageValues[1];
            $opportunity->source->value = $sourceValues[1];
            $saved = $opportunity->save();
            $this->assertTrue($saved);
            $this->assertTrue($opportunity->id !== null);
            $id = $opportunity->id;
            unset($opportunity);
            $opportunity = Opportunity::getById($id);
            $this->assertEquals('Negotiating', $opportunity->stage->value);
            $this->assertEquals('Outbound', $opportunity->source->value);
            $this->assertEquals(1, $currencies[0]->rateToBase);
        }

        public function testGetModelClassNames()
        {
            $modelClassNames = OpportunitiesModule::getModelClassNames();
            $this->assertEquals(2, count($modelClassNames));
            $this->assertEquals('Opportunity', $modelClassNames[1]);
            $this->assertEquals('OpportunitiesFilteredList', $modelClassNames[0]);
        }
    }
?>
