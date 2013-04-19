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
     * Unit test for AuditEventsListControllerUtil
     * To-Do: walkthrough test for AuditEventsListControllerUtil::renderList() action
     */
    class AuditEventsListControllerTest extends ZurmoBaseTest
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
            AuditEvent::$isTableOptimized = false;
        }

        public function teardown()
        {
            AuditEvent::$isTableOptimized = false;
            parent::teardown();
        }

        public function testMakeSearchAttributeDataByAuditedModel()
        {
            $account = new Account();
            $user = UserTestHelper::createBasicUser('Billy');
            $account->name  = 'aNewDawn Inc';
            $account->owner = $user;
            $this->assertTrue($account->save());

            $searchAttributeData = AuditEventsListControllerUtil::makeModalSearchAttributeDataByAuditedModel($account);

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
            $this->assertTrue($account->save());

            $searchAttributeData = AuditEventsListControllerUtil::makeModalSearchAttributeDataByAuditedModel($account);

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