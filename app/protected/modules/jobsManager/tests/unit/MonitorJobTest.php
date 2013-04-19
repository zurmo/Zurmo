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

    class MonitorJobTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::import('application.modules.jobsManager.tests.unit.jobs.*');
        }

        public function testRunAndProcessStuckJobs()
        {
            Yii::app()->user->userModel               = User::getByUsername('super');
            $emailAddress                             = new Email();
            $emailAddress->emailAddress               = 'sometest@zurmoalerts.com';
            Yii::app()->user->userModel->primaryEmail = $emailAddress;
            $saved                                    = Yii::app()->user->userModel->save();
            $this->assertTrue($saved);

            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            $monitorJob = new MonitorJob();
            $this->assertEquals(0, count(JobInProcess::getAll()));
            $this->assertEquals(0, count(Notification::getAll()));
            $jobInProcess = new JobInProcess();
            $jobInProcess->type = 'Test';
            $this->assertTrue($jobInProcess->save());
            //Should make createdDateTime long enough in past to trigger as stuck.
            $createdDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - 1000);
            $sql = "Update item set createddatetime = '" . $createdDateTime . "' where id = " .
                   $jobInProcess->getClassId('Item');
            R::exec($sql);
            $jobInProcess->forget();
            $monitorJob->run();
            $this->assertEquals(1, count(Notification::getAll()));
            //Confirm an email was sent
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(1, Yii::app()->emailHelper->getSentCount());
        }
    }
?>