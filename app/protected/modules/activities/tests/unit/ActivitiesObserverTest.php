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

    class ActivitiesObserverTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
            $account  = AccountTestHelper::createAccountByNameForOwner('anAccount2', Yii::app()->user->userModel);
            $task     = TaskTestHelper::createTaskWithOwnerAndRelatedAccount('startTask', $super, $account);
            $task->delete();
            R::exec('delete from activity_item');
        }

        public function testProperlyDeletingActivityItems()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $count   = R::getRow('select count(*) count from activity_item');
            $this->assertEquals(0, $count['count']);
            $account = AccountTestHelper::createAccountByNameForOwner('anAccount', Yii::app()->user->userModel);
            $deleted = $account->delete();
            $this->assertTrue($deleted);
            $count   = R::getRow('select count(*) count from activity_item');
            $this->assertEquals(0, $count['count']);

            $account2 = AccountTestHelper::createAccountByNameForOwner('anAccount2', Yii::app()->user->userModel);
            $opportunity = OpportunityTestHelper::createOpportunityByNameForOwner('anOpp', Yii::app()->user->userModel);
            $task     = TaskTestHelper::createTaskWithOwnerAndRelatedAccount('aTask', Yii::app()->user->userModel, $account2);
            $task->activityItems->add($opportunity);
            $this->assertTrue($task->save());
            $taskId = $task->id;
            $task->forget();

            RedBeansCache::forgetAll();

            $count   = R::getRow('select count(*) count from activity_item');
            $this->assertEquals(2, $count['count']);

            $deleted = $account2->delete();
            $this->assertTrue($deleted);
            $account2->forget();

            $count   = R::getRow('select count(*) count from activity_item');
            $this->assertEquals(1, $count['count']);

            RedBeansCache::forgetAll();

            //Make sure things render ok even with the account deleted.
            $content = ActivitiesUtil::renderSummaryContent(Task::getById($taskId),
                                                            'someUrl',
                                                            LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL,
                                                            'HomeModule');
        }
    }
?>