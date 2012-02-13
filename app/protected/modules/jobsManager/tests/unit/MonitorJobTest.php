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

    class MonitorJobTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::import('application.modules.jobsManager.tests.unit.jobs.*');
        }

        public function testRunAndProcessStuckJobs()
        {
            $monitorJob = new MonitorJob();
            $this->assertEquals(0, count(JobInProcess::getAll()));
            $this->assertEquals(0, count(Notification::getAll()));
            Yii::app()->emailHelper->removeAllSent();
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
            /** When the Email is available, we can test that the notification also sends
             *  a critical email.
            echo "<pre>";
            print_r(Yii::app()->emailHelper->getSentEmailMessages());
            echo "</pre>";
            */
        }
    }
?>