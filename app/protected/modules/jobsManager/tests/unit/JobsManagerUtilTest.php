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

    class JobsManagerUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::import('application.modules.jobsManager.tests.unit.jobs.*');
        }

        public function testRunNonMonitorJob()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            //Test running a TestJob that it creates a JobLog and does not leave a JobInProcess
            $this->assertEquals(0, count(JobInProcess::getAll()));
            $this->assertEquals(0, count(JobLog::getAll()));

            JobsManagerUtil::runNonMonitorJob('Test', new MessageLogger());
            $this->assertEquals(0, count(JobInProcess::getAll()));
            $jobLogs = JobLog::getAll();
            $this->assertEquals(1, count($jobLogs));
            $this->assertEquals('Test', $jobLogs[0]->type);
            $this->assertEquals(JobLog::STATUS_COMPLETE_WITHOUT_ERROR, $jobLogs[0]->status);
            $this->assertEquals(0, $jobLogs[0]->isProcessed);

            //Now test a job that always fails
            JobsManagerUtil::runNonMonitorJob('TestAlwaysFails', new MessageLogger());
            $this->assertEquals(0, count(JobInProcess::getAll()));
            $jobLogs = JobLog::getAll();
            $this->assertEquals(2, count($jobLogs));
            $this->assertEquals('TestAlwaysFails', $jobLogs[1]->type);
            $this->assertEquals(JobLog::STATUS_COMPLETE_WITH_ERROR, $jobLogs[1]->status);
            $this->assertEquals('The test job failed', $jobLogs[1]->message);
            $this->assertEquals(0, $jobLogs[1]->isProcessed);
        }

        public function testIsJobInProcessOverThreashold()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $jobInProcess          = new JobInProcess();
            $jobInProcess->type    = 'Test';
            $this->assertTrue($jobInProcess->save());
            //Set the createdDateTime as way in the past, so that it is over the threshold
            $sql  = "update " . Item::getTableName('Item'). " set createddatetime = '1980-06-03 18:33:03' where id = " .
                    $jobInProcess->getClassId('Item');
            R::exec($sql);
            $jobInProcessId        = $jobInProcess->id;
            $jobInProcess->forget();
            $jobInProcess = JobInProcess::getById($jobInProcessId);
            $this->assertTrue(JobsManagerUtil::isJobInProcessOverThreashold($jobInProcess, $jobInProcess->type));
            $jobInProcess->delete();

            //Test when a job is not over the threshold.
            $jobInProcess          = new JobInProcess();
            $jobInProcess->type    = 'Test';
            $this->assertTrue($jobInProcess->save());
            $this->assertFalse(JobsManagerUtil::isJobInProcessOverThreashold($jobInProcess, $jobInProcess->type));
            $jobInProcess->delete();
        }

        public function testRunMonitorJob()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            foreach (JobLog::getAll() as $jobLog)
            {
                $jobLog->delete();
            }
            JobsManagerUtil::runNonMonitorJob('Test', new MessageLogger());
            $jobLogs = JobLog::getAll();
            $this->assertEquals(1, count($jobLogs));
            $this->assertEquals(0, $jobLogs[0]->isProcessed);
            $jobLogId = $jobLogs[0]->id;
            JobsManagerUtil::runMonitorJob(new MessageLogger());
            $jobLogs = JobLog::getAll();
            $this->assertEquals(2, count($jobLogs));
            $this->assertEquals($jobLogId, $jobLogs[0]->id);
            $this->assertEquals(1, $jobLogs[0]->isProcessed);
        }
    }
?>
