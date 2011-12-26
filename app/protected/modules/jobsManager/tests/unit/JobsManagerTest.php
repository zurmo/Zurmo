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

    class JobsManagerTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testJobInProcess()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $jobInProcess          = new JobInProcess();
            $jobInProcess->type    = 'Monitor';
            $this->assertTrue($jobInProcess->save());

            $id = $jobInProcess->id;

            try
            {
                $jobInProcess = JobInProcess::getByType('SomethingElse');
                $this->fail();
            }
            catch(NotFoundException $e)
            {
                //nothing. passes.
            }
            $jobInProcess = JobInProcess::getByType('Monitor');
            $this->assertEquals(1, count($jobInProcess));
            $this->assertEquals($id, $jobInProcess->id);
            $jobInProcess->delete();
            $this->assertEquals(0, count(JobInProcess::getAll()));
        }

        public function testJobLog()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $jobLog                = new JobLog();
            $jobLog->type          = 'Monitor';
            $jobLog->startDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $jobLog->endDateTime   = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $jobLog->status        = JobLog::STATUS_COMPLETE_WITHOUT_ERROR;

            //Should fail to save because isProcessed is not specified.
            $this->assertFalse($jobLog->save());
            $jobLog->isProcessed = false;
            $this->assertTrue($jobLog->save());
            $id = $jobLog->id;
            $jobLog = JobLog::getById($id);
            $jobLog->delete();
            $this->assertEquals(0, count(JobInProcess::getAll()));
        }
    }
?>
