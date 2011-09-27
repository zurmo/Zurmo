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

    /**
     * Unit test for AuditEventsListControllerUtil
     * To-Do: walkthrough test for AuditEventsListControllerUtil::renderList() action
     */
    class AuditEventsListControllerTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testMakeSearchAttributeDataByAuditedModel()
        {
            $account = new Account();
            $user = UserTestHelper::createBasicUser('Billy');
            $account->name  = 'aNewDawn Inc';
            $account->owner = $user;
            assert($account->save());

            $searchAttributeData = AuditEventsListControllerUtil::makeSearchAttributeDataByAuditedModel($account);

            $this->assertTrue    (is_array($searchAttributeData)                  );
            $this->assertTrue    (is_array($searchAttributeData['clauses'])       );
            $this->assertTrue    (is_string($searchAttributeData['structure'])    );
            $this->assertTrue    (is_array($searchAttributeData['clauses']['1'])  );
            $this->assertTrue    (is_array($searchAttributeData['clauses']['2'])  );
            $this->assertTrue    (is_array($searchAttributeData['clauses']['3'])  );
            $this->assertEquals  (3, count($searchAttributeData['clauses'])       );
            $this->assertEquals  (3, count($searchAttributeData['clauses']['1'])  );
            $this->assertEquals  (3, count($searchAttributeData['clauses']['2'])  );
            $this->assertEquals  (3, count($searchAttributeData['clauses']['3'])  );

            $this->assertEquals  ('modelClassName', $searchAttributeData['clauses']['1']['attributeName']);
            $this->assertEquals  ('equals', $searchAttributeData['clauses']['1']['operatorType']         );
            $this->assertEquals  (get_class($account), $searchAttributeData['clauses']['1']['value']     );

            $this->assertEquals  ('modelId', $searchAttributeData['clauses']['2']['attributeName']       );
            $this->assertEquals  ('equals', $searchAttributeData['clauses']['2']['operatorType']         );
            $this->assertEquals  ($account->id, $searchAttributeData['clauses']['2']['value']            );

            $this->assertEquals  ('eventName', $searchAttributeData['clauses']['3']['attributeName']     );
            $this->assertEquals  ('doesNotEqual', $searchAttributeData['clauses']['3']['operatorType']   );
            $this->assertEquals  (ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, $searchAttributeData['clauses']['3']['value']);

            $this->assertEquals  ('1 and 2 and 3', $searchAttributeData['structure']);
        }

        public function testMakeDataProviderBySearchAttributeData()
        {
            $account = new Account();
            $user = UserTestHelper::createBasicUser('Andy');
            $account->name  = 'aNewDawn Inc 2';
            $account->owner = $user;
            assert($account->save());

            $searchAttributeData = AuditEventsListControllerUtil::makeSearchAttributeDataByAuditedModel($account);

            $dataProvider = AuditEventsListControllerUtil::makeDataProviderBySearchAttributeData($searchAttributeData);

            $this->assertTrue($dataProvider instanceof RedBeanModelDataProvider);
            $data = $dataProvider->getData();
            $this->assertEquals(1, count($data));
            $firstAuditEvent = current($data);
            $accountName = unserialize($firstAuditEvent->serializedData);
            $this->assertEquals($account->name,     $accountName);
        }
    }
?>